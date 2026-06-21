<?php

namespace App\Http\Controllers;

use App\Services\UserImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function __construct(private UserImpersonationService $impersonation) {}

    public function leave(): RedirectResponse
    {
        abort_unless($this->impersonation->isImpersonating(), 403);

        $admin = $this->impersonation->stop();

        if ($admin->canAccessPlatformPanel() || $admin->isSuperAdmin()) {
            return redirect()
                ->route('platform.tenants.index')
                ->with('success', __('menu.impersonation_ended'));
        }

        return redirect()->route('dashboard')->with('success', __('menu.impersonation_ended'));
    }
}
