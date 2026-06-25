<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationToken;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CafeStaffService;
use App\Services\MailConfigService;
use App\Services\SocialAuthService;
use App\Services\PasswordLifecycleService;
use App\Services\TwoFactorNotificationService;
use App\Services\TwoFactorService;
use App\Services\UserCafeService;
use App\Support\MailExceptionFormatter;
use App\Support\SecurityPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        private PasswordLifecycleService $passwordLifecycle,
        private TwoFactorService $twoFactor,
        private TwoFactorNotificationService $twoFactorNotifications,
    ) {}

    public function index(Request $request): View
    {
        CafeStaffService::healOrphanCustomerAccounts();

        User::query()
            ->customers()
            ->whereIn('oauth_provider', ['google', 'microsoft'])
            ->whereNull('email_verified_at')
            ->each(fn (User $customer) => SocialAuthService::ensureOAuthEmailVerified($customer));

        $perPage = in_array((int) $request->query('per_page'), [20, 50, 100, 200], true)
            ? (int) $request->query('per_page')
            : 20;

        $searchField = in_array($request->query('search_field'), ['email', 'phone', 'tenant'], true)
            ? $request->query('search_field')
            : 'email';

        $query = User::query()
            ->customers()
            ->with([
                'assignedTenants.owner',
                'assignedTenants.currentLicense.licenseType',
                'tenant.owner',
                'tenant.currentLicense.licenseType',
                'ownedTenants.owner',
                'ownedTenants.currentLicense.licenseType',
            ]);

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($searchField, $search) {
                if ($searchField === 'email') {
                    $builder->where('email', 'like', '%'.$search.'%');
                } elseif ($searchField === 'phone') {
                    $builder->where('phone', 'like', '%'.$search.'%');
                } else {
                    $builder->where(function ($tenantBuilder) use ($search) {
                        $tenantBuilder
                            ->whereHas('assignedTenants', fn ($tenantQuery) => $this->applyTenantSearch($tenantQuery, $search))
                            ->orWhereHas('ownedTenants', fn ($tenantQuery) => $this->applyTenantSearch($tenantQuery, $search))
                            ->orWhereHas('tenant', fn ($tenantQuery) => $this->applyTenantSearch($tenantQuery, $search));
                    });
                }
            });
        }

        $customers = $query
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('theme::pages.platform.customers.index', compact('customers', 'perPage', 'searchField'));
    }

    public function toggleActive(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $customer->update(['is_active' => ! $customer->is_active]);

        return back()->with('success', __('menu.messages.updated'));
    }

    public function sendResetLink(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        try {
            MailConfigService::runWithTimeout(function () use ($customer) {
                $status = Password::sendResetLink(['email' => $customer->email]);

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

    public function resetPassword(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $validated = $request->validate([
            'password' => ['required', SecurityPolicy::passwordRules(), 'confirmed'],
        ]);

        $oldHash = $customer->getRawOriginal('password');
        $this->passwordLifecycle->assertNotInHistory($customer, $validated['password']);
        $customer->update(['password' => $validated['password']]);
        $this->passwordLifecycle->recordChange($customer->fresh(), $oldHash);

        return back()->with('success', __('menu.password_reset_done'));
    }

    public function toggleTwoFactor(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        if (! SecurityPolicy::bool('security_2fa_enabled_globally')) {
            return back()->with('error', __('menu.two_factor_disabled_globally'));
        }

        if (! $customer->hasTwoFactorConfigured()) {
            return back()->with('info', __('menu.two_factor_admin_enable_hint'));
        }

        $this->twoFactor->adminDisable($customer);

        try {
            $this->twoFactorNotifications->notifyStatusChange($customer->fresh(), false);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', __('menu.two_factor_admin_disabled'));
    }

    public function resetTwoFactor(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        if (! SecurityPolicy::bool('security_2fa_enabled_globally')) {
            return back()->with('error', __('menu.two_factor_disabled_globally'));
        }

        $this->twoFactor->adminReset($customer);

        try {
            $this->twoFactorNotifications->notifyStatusChange($customer->fresh(), false);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', __('menu.two_factor_reset_success'));
    }

    public function toggleEmailVerification(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        if ($customer->hasVerifiedEmail()) {
            $customer->update(['email_verified_at' => null]);

            return back()->with('success', __('menu.email_unverified_manual'));
        }

        $customer->update(['email_verified_at' => now()]);

        EmailVerificationToken::query()
            ->where('user_id', $customer->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        return back()->with('success', __('menu.email_verified_manual'));
    }

    public function changeEmail(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('tenant_id')->ignore($customer->id),
            ],
        ]);

        $customer->update(['email' => $validated['email']]);

        return back()->with('success', __('menu.email_updated'));
    }

    public function attachTenant(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $validated = $request->validate([
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
        ]);

        if ($customer->isLinkedToTenant($validated['tenant_id'])) {
            return back()->with('error', __('menu.tenant_already_assigned'));
        }

        $tenant = Tenant::query()->findOrFail($validated['tenant_id']);

        app(UserCafeService::class)->linkAsCafeOwner($customer, $tenant);

        return back()->with('success', __('menu.tenant_assigned'));
    }

    public function detachTenant(User $customer, Tenant $tenant): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);
        abort_unless($customer->isLinkedToTenant($tenant), 404);

        $customer->unlinkTenant($tenant);

        return back()->with('success', __('menu.tenant_removed'));
    }

    public function transferTenantOwnership(Request $request, User $customer, Tenant $tenant): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);
        abort_unless($customer->ownsTenant($tenant), 403);

        $validated = $request->validate([
            'new_owner' => ['required', 'string', 'max:255'],
        ]);

        $newOwner = User::query()
            ->customers()
            ->where(function ($query) use ($validated) {
                $query
                    ->where('email', $validated['new_owner'])
                    ->orWhere('public_id', $validated['new_owner']);
            })
            ->first();

        if ($newOwner === null) {
            return back()->with('error', __('menu.tenant_owner_not_found'));
        }

        if ($newOwner->id === $customer->id) {
            return back()->with('error', __('menu.tenant_owner_same_customer'));
        }

        app(UserCafeService::class)->linkAsCafeOwner($newOwner, $tenant);

        return back()->with('success', __('menu.tenant_ownership_transferred'));
    }

    public function makeTenantOwner(User $customer, Tenant $tenant): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);
        abort_unless($customer->isLinkedToTenant($tenant), 404);

        if ($customer->ownsTenant($tenant)) {
            return back()->with('info', __('menu.tenant_already_owner'));
        }

        app(UserCafeService::class)->linkAsCafeOwner($customer, $tenant);

        return back()->with('success', __('menu.tenant_owner_updated'));
    }

    public function destroy(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);
        abort_if($customer->id === $this->authUser()->id, 403);

        $customer->assignedTenants()->detach();
        $customer->loginToken()?->delete();
        $customer->syncRoles([]);
        $customer->delete();

        return back()->with('success', __('menu.messages.deleted'));
    }

    private function applyTenantSearch($query, string $search): void
    {
        $query->where(function ($builder) use ($search) {
            $builder
                ->where('tenants.id', 'like', '%'.$search.'%')
                ->orWhere('tenants.name', 'like', '%'.$search.'%');
        });
    }
}
