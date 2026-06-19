<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\CaptchaPolicy;
use App\Support\OAuthPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteSettingsController extends Controller
{
    public function edit(): View
    {
        $defaults = CaptchaPolicy::defaults();

        $settings = [
            'site_name' => Setting::get('site_name', config('site.name')),
            'panel_url' => Setting::get('panel_url', config('site.panel_url')),
            'main_site_url' => Setting::get('main_site_url', config('site.main_site_url')),
            'contact_phone' => Setting::get('contact_phone', config('site.contact_phone')),
            'support_email' => Setting::get('support_email', config('site.support_email')),
            'default_locale' => Setting::get('default_locale', config('site.default_locale')),
            'captcha_provider' => Setting::get('captcha_provider', $defaults['captcha_provider']),
            'captcha_site_key' => Setting::get('captcha_site_key', ''),
            'captcha_login_enabled' => CaptchaPolicy::bool('captcha_login_enabled'),
            'captcha_register_enabled' => CaptchaPolicy::bool('captcha_register_enabled'),
            'captcha_password_reset_enabled' => CaptchaPolicy::bool('captcha_password_reset_enabled'),
            'registration_enabled' => CaptchaPolicy::registrationEnabled(),
            'has_captcha_secret' => (bool) Setting::get('captcha_secret_key'),
            'oauth_google_enabled' => OAuthPolicy::bool('oauth_google_enabled'),
            'oauth_google_client_id' => OAuthPolicy::clientId('google'),
            'oauth_microsoft_enabled' => OAuthPolicy::bool('oauth_microsoft_enabled'),
            'oauth_microsoft_client_id' => OAuthPolicy::clientId('microsoft'),
            'oauth_allow_login' => OAuthPolicy::allowLogin(),
            'oauth_allow_register' => OAuthPolicy::allowRegister(),
            'has_oauth_google_secret' => (bool) Setting::get('oauth_google_client_secret'),
            'has_oauth_microsoft_secret' => (bool) Setting::get('oauth_microsoft_client_secret'),
            'oauth_google_redirect' => url('/auth/google/callback'),
            'oauth_microsoft_redirect' => url('/auth/microsoft/callback'),
            'verification_link_expires_minutes' => Setting::get('verification_link_expires_minutes', '1440'),
            'email_verification_subject' => Setting::get('email_verification_subject', ''),
            'email_verification_body' => Setting::get('email_verification_body', ''),
            'default_company_name' => Setting::get('default_company_name', ''),
            'default_company_tax_number' => Setting::get('default_company_tax_number', ''),
            'default_company_phone' => Setting::get('default_company_phone', ''),
            'default_company_email' => Setting::get('default_company_email', ''),
            'default_company_address' => Setting::get('default_company_address', ''),
            'site_logo_path' => Setting::get('site_logo_path', ''),
            'site_favicon_path' => Setting::get('site_favicon_path', ''),
        ];

        return view('theme::pages.platform.settings.site', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'panel_url' => ['required', 'url', 'max:255'],
            'main_site_url' => ['required', 'url', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'default_locale' => ['required', 'in:tr,en'],
            'captcha_provider' => ['required', 'in:none,google,turnstile'],
            'captcha_site_key' => ['nullable', 'string', 'max:255'],
            'captcha_secret_key' => ['nullable', 'string', 'max:255'],
            'oauth_google_client_id' => ['nullable', 'string', 'max:255'],
            'oauth_google_client_secret' => ['nullable', 'string', 'max:255'],
            'oauth_microsoft_client_id' => ['nullable', 'string', 'max:255'],
            'oauth_microsoft_client_secret' => ['nullable', 'string', 'max:255'],
            'verification_link_expires_minutes' => ['nullable', 'integer', 'min:5', 'max:10080'],
            'email_verification_subject' => ['nullable', 'string', 'max:255'],
            'email_verification_body' => ['nullable', 'string', 'max:10000'],
            'default_company_name' => ['nullable', 'string', 'max:255'],
            'default_company_tax_number' => ['nullable', 'string', 'max:50'],
            'default_company_phone' => ['nullable', 'string', 'max:30'],
            'default_company_email' => ['nullable', 'email', 'max:255'],
            'default_company_address' => ['nullable', 'string', 'max:1000'],
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'site_favicon' => ['nullable', 'image', 'max:1024'],
        ]);

        $pairs = [
            'site_name' => $validated['site_name'],
            'panel_url' => $validated['panel_url'],
            'main_site_url' => $validated['main_site_url'],
            'contact_phone' => $validated['contact_phone'] ?? '',
            'support_email' => $validated['support_email'] ?? '',
            'default_locale' => $validated['default_locale'],
            'captcha_provider' => $validated['captcha_provider'],
            'captcha_site_key' => $validated['captcha_site_key'] ?? '',
            'captcha_login_enabled' => $request->boolean('captcha_login_enabled') ? '1' : '0',
            'captcha_register_enabled' => $request->boolean('captcha_register_enabled') ? '1' : '0',
            'captcha_password_reset_enabled' => $request->boolean('captcha_password_reset_enabled') ? '1' : '0',
            'registration_enabled' => $request->boolean('registration_enabled') ? '1' : '0',
            'oauth_google_enabled' => $request->boolean('oauth_google_enabled') ? '1' : '0',
            'oauth_google_client_id' => $request->input('oauth_google_client_id', ''),
            'oauth_microsoft_enabled' => $request->boolean('oauth_microsoft_enabled') ? '1' : '0',
            'oauth_microsoft_client_id' => $request->input('oauth_microsoft_client_id', ''),
            'oauth_allow_login' => $request->boolean('oauth_allow_login') ? '1' : '0',
            'oauth_allow_register' => $request->boolean('oauth_allow_register') ? '1' : '0',
            'verification_link_expires_minutes' => (string) ($validated['verification_link_expires_minutes'] ?? 1440),
            'email_verification_subject' => $validated['email_verification_subject'] ?? '',
            'email_verification_body' => $validated['email_verification_body'] ?? '',
            'default_company_name' => $validated['default_company_name'] ?? '',
            'default_company_tax_number' => $validated['default_company_tax_number'] ?? '',
            'default_company_phone' => $validated['default_company_phone'] ?? '',
            'default_company_email' => $validated['default_company_email'] ?? '',
            'default_company_address' => $validated['default_company_address'] ?? '',
        ];

        if (! empty($validated['captcha_secret_key'])) {
            $pairs['captcha_secret_key'] = Crypt::encryptString($validated['captcha_secret_key']);
        }

        if (! empty($validated['oauth_google_client_secret'])) {
            $pairs['oauth_google_client_secret'] = Crypt::encryptString($validated['oauth_google_client_secret']);
        }

        if (! empty($validated['oauth_microsoft_client_secret'])) {
            $pairs['oauth_microsoft_client_secret'] = Crypt::encryptString($validated['oauth_microsoft_client_secret']);
        }

        Setting::setMany($pairs, 'site');

        if ($request->hasFile('site_logo')) {
            Setting::set('site_logo_path', 'storage/'.$request->file('site_logo')->store('branding', 'public'), 'site');
        }

        if ($request->hasFile('site_favicon')) {
            Setting::set('site_favicon_path', 'storage/'.$request->file('site_favicon')->store('branding', 'public'), 'site');
        }

        return redirect()
            ->route('platform.settings.site')
            ->with('success', __('menu.messages.updated'));
    }
}
