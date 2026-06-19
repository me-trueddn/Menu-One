<?php

namespace App\Http\Controllers;

use App\Support\PlatformModules;
use App\Support\TenantAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasRole('platform_admin') || $user->canAccessPlatformPanel()) {
            if ($user->managesCafePanel() && (TenantAccess::selectableTenants($user)->isNotEmpty() || $user->isSuperAdmin())) {
                $redirect = $this->resolveCafePanelRedirect($user);

                if ($redirect) {
                    return $redirect;
                }
            }

            $route = PlatformModules::firstAccessibleRoute($user);

            return redirect()->route($route ?? 'profile.edit');
        }

        if ($user->hasRole('cafe_admin') || ($user->hasRole('user') && $user->linkedTenants()->isNotEmpty())) {
            $redirect = $this->resolveCafePanelRedirect($user);

            if ($redirect) {
                return $redirect;
            }
        }

        if ($user->hasRole('cafe_admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('waiter')) {
            return redirect()->route('waiter.tables.index');
        }

        if ($user->hasRole('kitchen')) {
            return redirect()->route('kitchen.index');
        }

        if ($user->hasRole('user')) {
            return redirect()->route('profile.edit');
        }

        return redirect()->route('login');
    }

    private function resolveCafePanelRedirect($user): ?RedirectResponse
    {
        $tenants = TenantAccess::selectableTenants($user);

        if ($tenants->isEmpty()) {
            return null;
        }

        if ($tenants->count() > 1 && ! session('active_tenant_id')) {
            return redirect()->route('tenant.select');
        }

        $activeId = TenantAccess::resolveActiveTenantId($user);

        if ($activeId) {
            TenantAccess::setActiveTenant($user, $activeId);

            return redirect()->route('admin.dashboard');
        }

        if ($tenants->count() > 1) {
            return redirect()->route('tenant.select');
        }

        return null;
    }
}
