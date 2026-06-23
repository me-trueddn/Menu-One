<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use App\Services\UserLoginTokenService;
use App\Support\TenantLicenseGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function __construct(
        private TwoFactorService $twoFactor,
        private UserLoginTokenService $loginTokens,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = $request->session()->get('login.id');

        if (! is_int($userId) && ! is_string($userId)) {
            return redirect()->route('login');
        }

        $user = User::query()->find($userId);

        if (! $user || ! $user->hasTwoFactorConfigured()) {
            $request->session()->forget(['login.id', 'login.remember']);

            return redirect()->route('login')->with('error', __('menu.two_factor_invalid_session'));
        }

        if (! $this->twoFactor->verifyUserCode($user, $request->string('code')->toString())) {
            return back()->withErrors(['code' => __('menu.two_factor_code_invalid')]);
        }

        $remember = (bool) $request->session()->get('login.remember', false);
        $request->session()->forget(['login.id', 'login.remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $token = $this->loginTokens->issue($user, $request);
        $request->session()->put('user_access_token', $token);

        if (TenantLicenseGate::licenseExpiredForUser($user)) {
            return TenantLicenseGate::redirectToProfileForExpiredLicense();
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
