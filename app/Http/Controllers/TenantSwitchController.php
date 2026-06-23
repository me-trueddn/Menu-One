<?php

namespace App\Http\Controllers;

use App\Support\TenantAccess;
use App\Support\TenantLicenseGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantSwitchController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = $this->authUser();

        if (! TenantAccess::isCustomerTenantSelection($user)) {
            return redirect()->route('home');
        }

        $tenants = TenantAccess::selectableTenants($user);

        if ($tenants->isEmpty()) {
            return redirect()->route('profile.edit');
        }

        if ($tenants->count() === 1) {
            TenantAccess::setActiveTenant($user, (string) $tenants->first()->id);

            if (TenantLicenseGate::licenseExpiredForUser($user)) {
                return TenantLicenseGate::redirectToProfileForExpiredLicense();
            }

            return redirect()->route($user->defaultRoute());
        }

        return view('theme::pages.tenant.select', compact('tenants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->authUser();

        abort_unless(TenantAccess::isCustomerTenantSelection($user), 403);

        $validated = $request->validate([
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'redirect' => ['nullable', 'string'],
        ]);

        TenantAccess::setActiveTenant($user, $validated['tenant_id']);

        if (TenantLicenseGate::licenseExpiredForUser($user)) {
            return TenantLicenseGate::redirectToProfileForExpiredLicense();
        }

        $redirect = $validated['redirect'] ?? null;

        if (is_string($redirect) && str_starts_with($redirect, url('/'))) {
            return redirect()->to($redirect)->with('success', __('menu.tenant_switched'));
        }

        return redirect()->route($user->defaultRoute())->with('success', __('menu.tenant_switched'));
    }
}
