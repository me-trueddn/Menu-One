<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\LicenseType;
use App\Models\Tenant;
use App\Services\LicenseGateService;
use App\Services\SupportSessionService;
use App\Services\TenantLicenseService;
use App\Support\CompanyDefaults;
use App\Support\ImageStorage;
use App\Support\MediaLimits;
use App\Support\TenantAccess;
use App\Support\TenantIdGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function __construct(
        private TenantLicenseService $licenses,
        private LicenseGateService $licenseGate,
    ) {}

    public function index(Request $request): View
    {
        $query = Tenant::query()->with(['owner', 'stoppedBy', 'currentLicense.licenseType']);

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('id', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhere('company_name', 'like', '%'.$search.'%')
                    ->orWhereHas('owner', fn ($ownerQuery) => $ownerQuery->where('email', 'like', '%'.$search.'%'));
            });
        }

        $tenants = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('theme::pages.platform.tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        return view('theme::pages.platform.tenants.create', [
            'companyDefaults' => CompanyDefaults::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->tenantRules());

        $tenant = Tenant::create(array_merge($validated, [
            'id' => TenantIdGenerator::generate(),
            'is_active' => true,
        ]));

        if ($request->hasFile('logo')) {
            $tenant->update(['logo_path' => ImageStorage::storeCustomerFile($request->file('logo'), $tenant->id)]);
        }

        $this->licenses->assignDefault($tenant);

        return redirect()
            ->route('platform.tenants.index')
            ->with('success', __('menu.messages.saved'));
    }

    public function edit(Tenant $tenant): View
    {
        $tenant->load([
            'owner',
            'stoppedBy',
            'currentLicense.licenseType',
            'staffUsers.roles',
            'staffInvitations' => fn ($query) => $query->recent()->with(['user', 'invitedBy', 'revokedBy'])->latest(),
        ]);

        return view('theme::pages.platform.tenants.edit', [
            'tenant' => $tenant,
            'licenseTypes' => LicenseType::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate($this->tenantRules($tenant));

        $licenseTypeId = $validated['license_type_id'] ?? null;
        unset($validated['license_type_id']);

        $tenant->update($validated);

        if ($request->hasFile('logo')) {
            ImageStorage::delete($tenant->logo_path);
            $tenant->update(['logo_path' => ImageStorage::storeCustomerFile($request->file('logo'), $tenant->id)]);
        }

        if ($licenseTypeId) {
            $type = LicenseType::query()->findOrFail($licenseTypeId);
            $this->licenses->assign($tenant, $type);
        }

        return redirect()
            ->route('platform.tenants.index')
            ->with('success', __('menu.messages.updated'));
    }

    public function connect(Tenant $tenant): RedirectResponse
    {
        abort_unless(
            $this->authUser()->isSuperAdmin()
            || $this->authUser()->canAccessPlatformPanel()
            || $this->authUser()->isPlatformStaffMember(),
            403
        );

        TenantAccess::setActiveTenant($this->authUser(), $tenant->id, support: true);
        app(SupportSessionService::class)->connect($tenant, $this->authUser());

        return redirect()
            ->route('admin.dashboard')
            ->with('success', __('menu.cafe_connected_support', ['name' => $tenant->name]));
    }

    public function disconnectSupport(): RedirectResponse
    {
        abort_unless(
            $this->authUser()->isSuperAdmin()
            || $this->authUser()->canAccessPlatformPanel()
            || $this->authUser()->isPlatformStaffMember(),
            403
        );

        $tenantId = session('active_tenant_id');
        app(SupportSessionService::class)->disconnect(is_string($tenantId) ? $tenantId : null);
        TenantAccess::clearSupportMode();

        return redirect()
            ->route('platform.tenants.index')
            ->with('success', __('menu.support_disconnected'));
    }

    public function stop(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'stop_note' => ['required', 'string', 'max:2000'],
        ]);

        $tenant->update([
            'is_active' => false,
            'stopped_at' => now(),
            'stop_note' => $validated['stop_note'],
            'stopped_by_user_id' => $this->authUser()->id,
        ]);

        $this->licenseGate->setLicenseStatus($tenant, false);

        return back()->with('success', __('menu.cafe_stopped'));
    }

    public function resume(Tenant $tenant): RedirectResponse
    {
        $tenant->update([
            'is_active' => true,
            'stopped_at' => null,
            'stop_note' => null,
            'stopped_by_user_id' => null,
        ]);

        $this->licenseGate->setLicenseStatus($tenant, true);

        return back()->with('success', __('menu.cafe_resumed'));
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->members()->detach();
        $tenant->delete();

        return redirect()
            ->route('platform.tenants.index')
            ->with('success', __('menu.messages.deleted'));
    }

    /** @return array<string, mixed> */
    private function tenantRules(?Tenant $tenant = null): array
    {
        $slugRule = $tenant
            ? 'unique:tenants,slug,'.$tenant->id
            : 'unique:tenants,slug';

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', $slugRule],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_tax_number' => ['nullable', 'string', 'max:50'],
            'company_phone' => ['nullable', 'string', 'max:30'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:1000'],
            'logo' => MediaLimits::imageRules(MediaLimits::CONTEXT_LOGO),
            'license_type_id' => ['nullable', 'exists:license_types,id'],
        ];
    }
}
