<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Tenant;
use App\Services\PasswordLifecycleService;
use App\Support\SecurityPolicy;
use App\Support\TenantAccess;
use App\Support\TenantIdGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private PasswordLifecycleService $passwordLifecycle) {}

    public function edit(Request $request): View
    {
        $user = $request->user()->load(['assignedTenants', 'ownedTenants']);

        $tab = in_array($request->query('tab'), ['profile', 'security', 'cafe'], true)
            ? $request->query('tab')
            : 'profile';

        $hasCafeLinks = $user->tenant_id !== null
            || $user->assignedTenants->isNotEmpty()
            || $user->ownedTenants->isNotEmpty();

        $canCreateCafe = $user->tenant_id === null && $user->ownedTenants->isEmpty();

        return view('theme::pages.profile.edit', [
            'user' => $user,
            'tab' => $tab,
            'hasCafeLinks' => $hasCafeLinks,
            'canCreateCafe' => $canCreateCafe,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return Redirect::route('profile.edit', ['tab' => 'profile'])
            ->with('success', __('menu.messages.updated'));
    }

    public function changeEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        $emailUnique = Rule::unique('users', 'email')->ignore($user->id);

        if ($user->tenant_id === null) {
            $emailUnique = $emailUnique->whereNull('tenant_id');
        } else {
            $emailUnique = $emailUnique->where('tenant_id', $user->tenant_id);
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                $emailUnique,
            ],
            'password' => ['required', 'current_password'],
        ]);

        $user->update([
            'email' => $validated['email'],
            'email_verified_at' => null,
        ]);

        return Redirect::route('profile.edit', ['tab' => 'security'])
            ->with('success', __('menu.email_updated'));
    }

    public function toggleTwoFactor(Request $request): RedirectResponse
    {
        if (! SecurityPolicy::bool('security_2fa_enabled_globally')) {
            return Redirect::route('profile.edit', ['tab' => 'security'])
                ->with('error', __('menu.two_factor_disabled_globally'));
        }

        $user = $request->user();
        $user->update(['two_factor_enabled' => ! $user->two_factor_enabled]);

        return Redirect::route('profile.edit', ['tab' => 'security'])
            ->with('success', __('menu.messages.updated'));
    }

    public function storeCafe(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user->tenant_id !== null || $user->ownedTenants()->exists(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:tenants,slug'],
        ]);

        $tenant = Tenant::create([
            'id' => TenantIdGenerator::generate(),
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'is_active' => true,
            'owner_user_id' => $user->id,
        ]);

        $user->update(['tenant_id' => $tenant->id]);
        $user->assignedTenants()->syncWithoutDetaching([$tenant->id]);

        if (! $user->hasRole('cafe_admin')) {
            $user->assignRole('cafe_admin');
        }

        TenantAccess::setActiveTenant($user, $tenant->id);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', __('menu.cafe_created'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $hasCafeLinks = $user->tenant_id !== null
            || $user->assignedTenants()->exists()
            || $user->ownedTenants()->exists();

        Auth::logout();

        if ($hasCafeLinks) {
            Tenant::query()->where('owner_user_id', $user->id)->update(['owner_user_id' => null]);
            $user->assignedTenants()->detach();
        }

        $user->loginToken()?->delete();
        $user->syncRoles([]);
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')
            ->with('success', $hasCafeLinks ? __('menu.account_removed_cafe_kept') : __('menu.account_deleted'));
    }
}
