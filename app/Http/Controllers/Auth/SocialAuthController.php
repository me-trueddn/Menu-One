<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\PlatformStaffRegistrationBlockedException;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SocialAuthService;
use App\Services\UserLoginTokenService;
use App\Support\CaptchaPolicy;
use App\Support\OAuthPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialAuthController extends Controller
{
    public function __construct(private UserLoginTokenService $loginTokens) {}

    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'microsoft'], true), 404);
        abort_unless($this->providerConfigured($provider), 404);

        SocialAuthService::applyConfig();
        Setting::flushCache();
        Session::put('oauth_intent', request('intent', OAuthPolicy::allowLogin() ? 'login' : 'register'));

        return Socialite::driver(OAuthPolicy::socialiteDriver($provider))
            ->redirectUrl(OAuthPolicy::redirectUrl($provider))
            ->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'microsoft'], true), 404);
        abort_unless($this->providerConfigured($provider), 404);

        $intent = Session::pull('oauth_intent', 'login');

        if ($intent === 'register' && ! CaptchaPolicy::registrationEnabled()) {
            return redirect()->route('login')->with('error', __('menu.registration_disabled'));
        }

        if ($intent === 'register' && ! OAuthPolicy::allowRegister()) {
            return redirect()->route('login')->with('error', __('menu.registration_disabled'));
        }

        if ($intent === 'login' && ! OAuthPolicy::allowLogin()) {
            return redirect()->route('login')->with('error', __('menu.oauth_login_disabled'));
        }

        try {
            SocialAuthService::applyConfig();
            $socialUser = Socialite::driver(OAuthPolicy::socialiteDriver($provider))
                ->redirectUrl(OAuthPolicy::redirectUrl($provider))
                ->user();
        } catch (InvalidStateException $exception) {
            report($exception);

            return redirect()->route('login')->with('error', __('menu.oauth_state_invalid'));
        } catch (\Throwable $exception) {
            report($exception);

            if (OAuthPolicy::clientSecretDecryptFailed($provider)) {
                return redirect()->route('login')->with('error', __('menu.oauth_secret_decrypt_failed'));
            }

            return redirect()->route('login')->with('error', __('menu.oauth_failed'));
        }

        try {
            $user = SocialAuthService::findOrCreateCustomer($provider, $socialUser);
        } catch (PlatformStaffRegistrationBlockedException) {
            return redirect()->route('login')->with('error', __('menu.platform_staff_registration_blocked'));
        }

        if (! $user->is_active) {
            return redirect()->route('login')->with('error', __('menu.account_inactive'));
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $token = $this->loginTokens->issue($user, $request);
        $request->session()->put('user_access_token', $token);

        return redirect()->route('dashboard');
    }

    protected function providerConfigured(string $provider): bool
    {
        return match ($provider) {
            'google' => OAuthPolicy::googleConfigured(),
            'microsoft' => OAuthPolicy::microsoftConfigured(),
            default => false,
        };
    }
}
