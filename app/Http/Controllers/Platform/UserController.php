<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MailConfigService;
use App\Services\PasswordLifecycleService;
use App\Services\TwoFactorNotificationService;
use App\Services\TwoFactorService;
use App\Support\MailExceptionFormatter;
use App\Support\SecurityPolicy;
use App\Support\SuperAdminGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private PasswordLifecycleService $passwordLifecycle,
        private TwoFactorService $twoFactor,
        private TwoFactorNotificationService $twoFactorNotifications,
    ) {}
    public function index(): View
    {
        $users = User::query()
            ->platformStaff()
            ->with('roles')
            ->orderByDesc('id')
            ->paginate(10);

        $groups = Role::query()->orderBy('name')->get();

        return view('theme::pages.platform.users.index', compact('users', 'groups'));
    }

    public function create(): View
    {
        $groups = Role::query()
            ->where('is_system', false)
            ->orWhereIn('name', ['platform_admin'])
            ->orderBy('name')
            ->get();

        return view('theme::pages.platform.users.create', compact('groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $minLength = SecurityPolicy::passwordMinLength();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('tenant_id')],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', SecurityPolicy::passwordRules()],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'tenant_id' => null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'password_changed_at' => now(),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        return redirect()
            ->route('platform.users.index')
            ->with('success', __('menu.messages.saved'));
    }

    public function edit(User $user): View
    {
        abort_unless($user->isPlatformStaffMember(), 404);
        SuperAdminGuard::abortIfProtected($user);

        $groups = Role::query()->orderBy('name')->get();

        $user->load('assignedTenants');

        return view('theme::pages.platform.users.edit', compact('user', 'groups'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isPlatformStaffMember(), 404);
        SuperAdminGuard::abortIfProtected($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('tenant_id')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', SecurityPolicy::passwordRules()],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        if ($validated['password'] ?? null) {
            $this->passwordLifecycle->assertNotInHistory($user, $validated['password']);
        }

        $oldHash = $validated['password'] ? $user->getRawOriginal('password') : null;

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            ...($validated['password'] ? ['password' => $validated['password']] : []),
        ]);

        if ($oldHash) {
            $this->passwordLifecycle->recordChange($user->fresh(), $oldHash);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('platform.users.index')
            ->with('success', __('menu.messages.updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->isPlatformStaffMember(), 404);
        SuperAdminGuard::abortIfProtected($user);
        abort_if($user->id === $this->authUser()->id, 403);

        $user->assignedTenants()->detach();
        $user->loginToken()?->delete();
        $user->syncRoles([]);
        $user->delete();

        return redirect()
            ->route('platform.users.index')
            ->with('success', __('menu.messages.deleted'));
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isPlatformStaffMember(), 404);
        SuperAdminGuard::abortIfProtected($user);

        $validated = $request->validate([
            'password' => ['required', SecurityPolicy::passwordRules(), 'confirmed'],
        ]);

        $oldHash = $user->getRawOriginal('password');
        $this->passwordLifecycle->assertNotInHistory($user, $validated['password']);
        $user->update(['password' => $validated['password']]);
        $this->passwordLifecycle->recordChange($user->fresh(), $oldHash);

        return back()->with('success', __('menu.password_reset_done'));
    }

    public function toggleTwoFactor(User $user): RedirectResponse
    {
        abort_unless($user->isPlatformStaffMember(), 404);
        SuperAdminGuard::abortIfProtected($user);

        if (! SecurityPolicy::bool('security_2fa_enabled_globally')) {
            return back()->with('error', __('menu.two_factor_disabled_globally'));
        }

        $enabled = ! $user->hasTwoFactorConfigured();

        if ($enabled) {
            return back()->with('info', __('menu.two_factor_admin_enable_hint'));
        }

        $this->twoFactor->adminDisable($user);

        try {
            $this->twoFactorNotifications->notifyStatusChange($user->fresh(), false);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', __('menu.two_factor_admin_disabled'));
    }

    public function resetTwoFactor(User $user): RedirectResponse
    {
        abort_unless($user->isPlatformStaffMember(), 404);
        SuperAdminGuard::abortIfProtected($user);

        if (! SecurityPolicy::bool('security_2fa_enabled_globally')) {
            return back()->with('error', __('menu.two_factor_disabled_globally'));
        }

        $this->twoFactor->adminReset($user);

        try {
            $this->twoFactorNotifications->notifyStatusChange($user->fresh(), false);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', __('menu.two_factor_reset_success'));
    }

    public function changeEmail(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isPlatformStaffMember(), 404);
        SuperAdminGuard::abortIfProtected($user);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('tenant_id')->ignore($user->id)],
        ]);

        $user->update(['email' => $validated['email']]);

        return back()->with('success', __('menu.email_updated'));
    }

    public function sendResetLink(User $user): RedirectResponse
    {
        abort_unless($user->isPlatformStaffMember(), 404);
        SuperAdminGuard::abortIfProtected($user);

        try {
            MailConfigService::runWithTimeout(function () use ($user) {
                $status = Password::sendResetLink(['email' => $user->email]);

                if ($status !== Password::RESET_LINK_SENT) {
                    throw new \RuntimeException(__($status));
                }
            });
        } catch (\RuntimeException $exception) {
            report($exception);

            return back()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', MailExceptionFormatter::toUserMessage($exception));
        }

        return back()->with('success', __('menu.reset_link_sent'));
    }

    public function toggleActive(User $user): RedirectResponse
    {
        abort_unless($user->isPlatformStaffMember(), 404);
        SuperAdminGuard::abortIfProtected($user);
        abort_if($user->id === $this->authUser()->id, 403);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', __('menu.messages.updated'));
    }

    public function attachTenant(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isPlatformStaffMember(), 404);
        SuperAdminGuard::abortIfProtected($user);

        $validated = $request->validate([
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
        ]);

        if ($user->assignedTenants()->whereKey($validated['tenant_id'])->exists()) {
            return back()->with('error', __('menu.tenant_already_assigned'));
        }

        $user->assignedTenants()->attach($validated['tenant_id']);

        return back()->with('success', __('menu.tenant_assigned'));
    }

    public function detachTenant(User $user, Tenant $tenant): RedirectResponse
    {
        abort_unless($user->isPlatformStaffMember(), 404);
        SuperAdminGuard::abortIfProtected($user);

        abort_unless($user->isLinkedToTenant($tenant), 404);

        $user->unlinkTenant($tenant);

        return back()->with('success', __('menu.tenant_removed'));
    }
}
