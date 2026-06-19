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

        if ($user->isSuperAdmin() || $user->hasRole('platform_admin') || $user->canAccessPlatformPanel()) {
            $route = PlatformModules::firstAccessibleRoute($user);

            return redirect()->route($route ?? 'profile.edit');
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

        if ($user->isCustomer()) {
            if (TenantAccess::shouldPromptTenantSelection($user)) {
                return redirect()->route('tenant.select');
            }

            if ($user->linkedTenants()->isNotEmpty()) {
                $activeId = TenantAccess::resolveActiveTenantId($user);

                if ($activeId) {
                    TenantAccess::setActiveTenant($user, $activeId);

                    return redirect()->route('admin.dashboard');
                }
            }

            return redirect()->route('profile.edit');
        }

        return redirect()->route('login');
    }
}
