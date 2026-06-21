<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Tenant;
use App\Services\PasswordLifecycleService;
use App\Services\TenantLicenseService;
use App\Services\UserCafeService;
use App\Support\CompanyDefaults;
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
    public function __construct(
        private PasswordLifecycleService $passwordLifecycle,
        private TenantLicenseService $licenses,
        private UserCafeService $cafes,
    ) {}

    public function edit(Request $request): View
    {
        $user = $request->user()->load([
            'assignedTenants.currentLicense.licenseType',
            'ownedTenants.currentLicense.licenseType',
            'tenant.currentLicense.licenseType',
        ]);

        $tab = in_array($request->query('tab'), ['profile', 'security', 'cafe', 'licensing'], true)
            ? $request->query('tab')
            : 'profile';

        $hasCafeLinks = $user->tenant_id !== null
            || $user->assignedTenants->isNotEmpty()
            || $user->ownedTenants->isNotEmpty();

        $canCreateCafe = $this->cafes->canCreateCafe($user);
        $cafeCooldownEndsAt = $this->cafes->cooldownEndsAt($user);
        $daysUntilCanCreateCafe = $this->cafes->daysUntilCanCreateCafe($user);

        return view('theme::pages.profile.edit', [
            'user' => $user,
            'tab' => $tab,
            'hasCafeLinks' => $hasCafeLinks,
            'canCreateCafe' => $canCreateCafe,
            'cafeCooldownEndsAt' => $cafeCooldownEndsAt,
            'daysUntilCanCreateCafe' => $daysUntilCanCreateCafe,
            'ownedCafes' => $user->ownedTenants,
            'companyDefaults' => CompanyDefaults::all(),
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

        abort_unless($this->cafes->canCreateCafe($user), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:tenants,slug'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_tax_number' => ['nullable', 'string', 'max:50'],
            'company_phone' => ['nullable', 'string', 'max:30'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $tenant = Tenant::create([
            'id' => TenantIdGenerator::generate(),
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'company_name' => $validated['company_name'] ?? null,
            'company_tax_number' => $validated['company_tax_number'] ?? null,
            'company_phone' => $validated['company_phone'] ?? null,
            'company_email' => $validated['company_email'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'is_active' => true,
            'owner_user_id' => $user->id,
        ]);

        if ($request->hasFile('logo')) {
            $tenant->update(['logo_path' => $request->file('logo')->store('tenant-logos', 'public')]);
        }

        $this->licenses->assignDefault($tenant);

        $user->update(['tenant_id' => $tenant->id, 'unlicensed_cafe_deleted_at' => null]);
        $user->assignedTenants()->syncWithoutDetaching([$tenant->id]);

        if (! $user->hasRole('cafe_admin')) {
            $user->assignRole('cafe_admin');
        }

        TenantAccess::setActiveTenant($user, $tenant->id);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', __('menu.cafe_created'));
    }

    public function destroyCafe(Tenant $tenant): RedirectResponse
    {
        $user = $this->authUser();

        try {
            $this->cafes->deleteUnlicensedCafe($user, $tenant);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (session('active_tenant_id') === $tenant->id) {
            session()->forget('active_tenant_id');
        }

        return redirect()
            ->route('profile.edit', ['tab' => 'cafe'])
            ->with('success', __('menu.cafe_deleted'));
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
