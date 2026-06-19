<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\SupportSessionService;
use App\Services\UserLoginTokenService;
use App\Support\TenantAccess;
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

        $request->session()->regenerate();

        $user = $request->user();
        $token = $this->loginTokens->issue($user, $request);
        $request->session()->put('user_access_token', $token);

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

        return redirect('/');
    }
}
