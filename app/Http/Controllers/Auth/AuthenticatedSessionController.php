<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\SupportSessionService;
use App\Services\UserLoginTokenService;
use App\Support\TenantAccess;
use App\Support\TenantLicenseGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private UserLoginTokenService $loginTokens) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        if ($user->hasTwoFactorConfigured()) {
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $request->boolean('remember'),
            ]);

            Auth::logout();
            $request->session()->regenerate();

            return redirect()->route('two-factor.login');
        }

        $request->session()->regenerate();

        $token = $this->loginTokens->issue($user, $request);
        $request->session()->put('user_access_token', $token);

        if (TenantLicenseGate::licenseExpiredForUser($user)) {
            return TenantLicenseGate::redirectToProfileForExpiredLicense();
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        if ($user = $request->user()) {
            $this->loginTokens->revoke($user);

            if (TenantAccess::isInSupportMode($user)) {
                $tenantId = session('active_tenant_id');
                app(SupportSessionService::class)->disconnect(is_string($tenantId) ? $tenantId : null);
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
