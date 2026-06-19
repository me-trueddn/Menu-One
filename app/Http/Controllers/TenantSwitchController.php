<?php

namespace App\Http\Controllers;

use App\Support\TenantAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantSwitchController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = $this->authUser();
        $tenants = TenantAccess::selectableTenants($user);

        if ($tenants->count() === 1) {
            TenantAccess::setActiveTenant($user, (string) $tenants->first()->id);

            return redirect()->route('admin.dashboard');
        }

        if ($tenants->isEmpty()) {
            return redirect()->route('profile.edit');
        }

        return view('theme::pages.tenant.select', compact('tenants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'redirect' => ['nullable', 'string'],
        ]);

        TenantAccess::setActiveTenant($this->authUser(), $validated['tenant_id']);

        $redirect = $validated['redirect'] ?? null;

        if (is_string($redirect) && str_starts_with($redirect, url('/'))) {
            return redirect()->to($redirect)->with('success', __('menu.tenant_switched'));
        }

        if ($this->authUser()->managesCafePanel()) {
            return redirect()->route('admin.dashboard')->with('success', __('menu.tenant_switched'));
        }

        return redirect()->route('profile.edit')->with('success', __('menu.tenant_switched'));
    }
}
