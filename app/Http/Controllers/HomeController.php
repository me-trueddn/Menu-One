<?php

namespace App\Http\Controllers;

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

        if ($user->isCustomer() && TenantAccess::shouldPromptTenantSelection($user)) {
            return redirect()->route('tenant.select');
        }

        if ($user->isCustomer() && $user->linkedTenants()->isNotEmpty()) {
            $activeId = TenantAccess::resolveActiveTenantId($user);

            if ($activeId) {
                TenantAccess::setActiveTenant($user, $activeId);
            }
        }

        return redirect()->route($user->defaultRoute());
    }
}
