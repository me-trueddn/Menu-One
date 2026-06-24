<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\CaptchaPolicy;
use App\Support\ImageStorage;
use App\Support\OAuthPolicy;
use App\Support\SecretMask;
use App\Support\SettingPersistence;
use App\Support\SettingsDefaults;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class SiteSettingsController extends Controller
{
    /** @var list<string> */
    private const NOTIFICATION_TEMPLATE_KEYS = [
        'verification_link_expires_minutes',
        'email_verification_subject',
        'email_verification_body',
        'password_reset_expires_minutes',
        'password_reset_subject',
        'password_reset_body',
        'staff_invitation_expires_minutes',
        'staff_invitation_subject',
        'staff_invitation_body',
        'two_factor_enabled_subject',
        'two_factor_enabled_body',
        'two_factor_disabled_subject',
        'two_factor_disabled_body',
    ];

    public function edit(): View
    {
        SettingsDefaults::ensureSiteScaffoldDefaults();
        SettingsDefaults::ensureNotificationTemplatesIfUnset();

        $templates = SettingsDefaults::notificationTemplateDefaults();

        $site = Setting::mergedGroup('site', array_merge(
            CaptchaPolicy::defaults(),
            OAuthPolicy::defaults(),
            SettingsDefaults::siteSeedValues(),
        ));

        $settings = [
            'site_name' => $site['site_name'] ?? config('site.name'),
            'panel_url' => $site['panel_url'] ?? config('site.panel_url'),
            'main_site_url' => $site['main_site_url'] ?? config('site.main_site_url'),
            'contact_phone' => $site['contact_phone'] ?? '',
            'support_email' => $site['support_email'] ?? '',
            'default_locale' => $site['default_locale'] ?? config('site.default_locale'),
            'captcha_provider' => CaptchaPolicy::provider(),
            'captcha_site_key' => CaptchaPolicy::siteKey(),
            'captcha_site_key_masked' => SecretMask::mask(CaptchaPolicy::siteKey()),
            'captcha_secret_key_masked' => SecretMask::mask(CaptchaPolicy::secretKey()),
            'has_captcha_site_key' => CaptchaPolicy::siteKey() !== '',
            'captcha_login_enabled' => CaptchaPolicy::bool('captcha_login_enabled'),
            'captcha_register_enabled' => CaptchaPolicy::bool('captcha_register_enabled'),
            'captcha_password_reset_enabled' => CaptchaPolicy::bool('captcha_password_reset_enabled'),
            'registration_enabled' => CaptchaPolicy::registrationEnabled(),
            'has_captcha_secret' => CaptchaPolicy::secretKey() !== '',
            'oauth_google_enabled' => OAuthPolicy::bool('oauth_google_enabled'),
            'oauth_google_client_id' => OAuthPolicy::clientId('google'),
            'oauth_microsoft_enabled' => OAuthPolicy::bool('oauth_microsoft_enabled'),
            'oauth_microsoft_client_id' => OAuthPolicy::clientId('microsoft'),
            'oauth_allow_login' => OAuthPolicy::allowLogin(),
            'oauth_allow_register' => OAuthPolicy::allowRegister(),
            'has_oauth_google_secret' => OAuthPolicy::clientSecret('google') !== '',
            'has_oauth_microsoft_secret' => OAuthPolicy::clientSecret('microsoft') !== '',
            'oauth_google_secret_decrypt_failed' => OAuthPolicy::clientSecretDecryptFailed('google'),
            'oauth_microsoft_secret_decrypt_failed' => OAuthPolicy::clientSecretDecryptFailed('microsoft'),
            'oauth_google_redirect' => OAuthPolicy::redirectUrl('google'),
            'oauth_microsoft_redirect' => OAuthPolicy::redirectUrl('microsoft'),
            'verification_link_expires_minutes' => Setting::getFilled('verification_link_expires_minutes', $templates['verification_link_expires_minutes']),
            'email_verification_subject' => Setting::getFilled('email_verification_subject', $templates['email_verification_subject']),
            'email_verification_body' => Setting::getFilled('email_verification_body', $templates['email_verification_body']),
            'password_reset_expires_minutes' => Setting::getFilled('password_reset_expires_minutes', $templates['password_reset_expires_minutes']),
            'password_reset_subject' => Setting::getFilled('password_reset_subject', $templates['password_reset_subject']),
            'password_reset_body' => Setting::getFilled('password_reset_body', $templates['password_reset_body']),
            'staff_invitation_expires_minutes' => Setting::getFilled('staff_invitation_expires_minutes', $templates['staff_invitation_expires_minutes']),
            'staff_invitation_subject' => Setting::getFilled('staff_invitation_subject', $templates['staff_invitation_subject']),
            'staff_invitation_body' => Setting::getFilled('staff_invitation_body', $templates['staff_invitation_body']),
            'two_factor_enabled_subject' => Setting::getFilled('two_factor_enabled_subject', $templates['two_factor_enabled_subject']),
            'two_factor_enabled_body' => Setting::getFilled('two_factor_enabled_body', $templates['two_factor_enabled_body']),
            'two_factor_disabled_subject' => Setting::getFilled('two_factor_disabled_subject', $templates['two_factor_disabled_subject']),
            'two_factor_disabled_body' => Setting::getFilled('two_factor_disabled_body', $templates['two_factor_disabled_body']),
            'default_company_name' => Setting::getFilled('default_company_name', ''),
            'default_company_tax_number' => Setting::getFilled('default_company_tax_number', ''),
            'default_company_phone' => Setting::getFilled('default_company_phone', ''),
            'default_company_email' => Setting::getFilled('default_company_email', ''),
            'default_company_address' => Setting::getFilled('default_company_address', ''),
            'site_logo_path' => Setting::get('site_logo_path', ''),
            'site_logo_height' => (int) Setting::getFilled('site_logo_height', $site['site_logo_height'] ?? '40'),
            'site_logo_height_register' => (int) Setting::getFilled('site_logo_height_register', $site['site_logo_height_register'] ?? '32'),
            'site_sidebar_logo_height' => (int) Setting::getFilled('site_sidebar_logo_height', $site['site_sidebar_logo_height'] ?? '28'),
            'site_sidebar_brand_height' => (int) Setting::getFilled('site_sidebar_brand_height', $site['site_sidebar_brand_height'] ?? '56'),
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
            'email_verification_body' => ['nullable', 'string', 'max:65535'],
            'password_reset_expires_minutes' => ['nullable', 'integer', 'min:5', 'max:10080'],
            'password_reset_subject' => ['nullable', 'string', 'max:255'],
            'password_reset_body' => ['nullable', 'string', 'max:65535'],
            'staff_invitation_expires_minutes' => ['nullable', 'integer', 'min:5', 'max:10080'],
            'staff_invitation_subject' => ['nullable', 'string', 'max:255'],
            'staff_invitation_body' => ['nullable', 'string', 'max:65535'],
            'two_factor_enabled_subject' => ['nullable', 'string', 'max:255'],
            'two_factor_enabled_body' => ['nullable', 'string', 'max:65535'],
            'two_factor_disabled_subject' => ['nullable', 'string', 'max:255'],
            'two_factor_disabled_body' => ['nullable', 'string', 'max:65535'],
            'default_company_name' => ['nullable', 'string', 'max:255'],
            'default_company_tax_number' => ['nullable', 'string', 'max:50'],
            'default_company_phone' => ['nullable', 'string', 'max:30'],
            'default_company_email' => ['nullable', 'email', 'max:255'],
            'default_company_address' => ['nullable', 'string', 'max:1000'],
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'site_logo_height' => ['required', 'integer', 'min:16', 'max:160'],
            'site_logo_height_register' => ['required', 'integer', 'min:16', 'max:120'],
            'site_sidebar_logo_height' => ['required', 'integer', 'min:16', 'max:120'],
            'site_sidebar_brand_height' => ['required', 'integer', 'min:40', 'max:160'],
            'site_favicon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg,ico', 'max:1024'],
        ]);

        $pairs = [
            'site_name' => $validated['site_name'],
            'panel_url' => $validated['panel_url'],
            'main_site_url' => $validated['main_site_url'],
            'contact_phone' => $validated['contact_phone'] ?? '',
            'support_email' => $validated['support_email'] ?? '',
            'default_locale' => $validated['default_locale'],
            'captcha_provider' => $validated['captcha_provider'],
            'captcha_login_enabled' => $request->input('captcha_login_enabled', '0') === '1' ? '1' : '0',
            'captcha_register_enabled' => $request->input('captcha_register_enabled', '0') === '1' ? '1' : '0',
            'captcha_password_reset_enabled' => $request->input('captcha_password_reset_enabled', '0') === '1' ? '1' : '0',
            'registration_enabled' => $request->input('registration_enabled', '0') === '1' ? '1' : '0',
            'oauth_google_enabled' => $request->input('oauth_google_enabled', '0') === '1' ? '1' : '0',
            'oauth_google_client_id' => SettingPersistence::keepOrApply('oauth_google_client_id', $request->input('oauth_google_client_id')),
            'oauth_microsoft_enabled' => $request->input('oauth_microsoft_enabled', '0') === '1' ? '1' : '0',
            'oauth_microsoft_client_id' => SettingPersistence::keepOrApply('oauth_microsoft_client_id', $request->input('oauth_microsoft_client_id')),
            'oauth_allow_login' => $request->input('oauth_allow_login', '0') === '1' ? '1' : '0',
            'oauth_allow_register' => $request->input('oauth_allow_register', '0') === '1' ? '1' : '0',
            'default_company_name' => $validated['default_company_name'] ?? '',
            'default_company_tax_number' => $validated['default_company_tax_number'] ?? '',
            'default_company_phone' => $validated['default_company_phone'] ?? '',
            'default_company_email' => $validated['default_company_email'] ?? '',
            'default_company_address' => $validated['default_company_address'] ?? '',
            'site_logo_height' => (string) $validated['site_logo_height'],
            'site_logo_height_register' => (string) $validated['site_logo_height_register'],
            'site_sidebar_logo_height' => (string) $validated['site_sidebar_logo_height'],
            'site_sidebar_brand_height' => (string) $validated['site_sidebar_brand_height'],
        ];

        $pairs = array_merge($pairs, $this->notificationTemplatePairs($validated));

        if (! empty($validated['captcha_site_key'])) {
            $pairs['captcha_site_key'] = $validated['captcha_site_key'];
        }

        if (! empty($validated['captcha_secret_key'])) {
            $pairs['captcha_secret_key'] = Crypt::encryptString($validated['captcha_secret_key']);
        }

        if (! empty($validated['oauth_google_client_secret'])) {
            $pairs['oauth_google_client_secret'] = Crypt::encryptString(trim($validated['oauth_google_client_secret']));
        }

        if (! empty($validated['oauth_microsoft_client_secret'])) {
            $pairs['oauth_microsoft_client_secret'] = Crypt::encryptString($validated['oauth_microsoft_client_secret']);
        }

        Setting::setMany($pairs, 'site');

        if ($request->hasFile('site_logo')) {
            ImageStorage::delete(Setting::get('site_logo_path'));
            Setting::set('site_logo_path', ImageStorage::storeSiteFile($request->file('site_logo')), 'site');
        }

        if ($request->hasFile('site_favicon')) {
            ImageStorage::delete(Setting::get('site_favicon_path'));
            Setting::set('site_favicon_path', ImageStorage::storeSiteFile($request->file('site_favicon')), 'site');
        }

        return redirect()
            ->route('platform.settings.site')
            ->with('success', __('menu.messages.updated'));
    }

    /** @param  array<string, mixed>  $validated */
    private function notificationTemplatePairs(array $validated): array
    {
        $pairs = [];

        foreach (self::NOTIFICATION_TEMPLATE_KEYS as $key) {
            $incoming = SettingPersistence::incomingOrSkip($key, $validated[$key] ?? null);

            if ($incoming === null) {
                continue;
            }

            $pairs[$key] = is_int($incoming) ? (string) $incoming : $incoming;
        }

        return $pairs;
    }
}
