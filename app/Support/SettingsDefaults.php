<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class SettingsDefaults
{
    /** @return list<string> */
    public static function ensureSiteGroupPersisted(): array
    {
        $restored = static::ensureSiteScaffoldDefaults();

        foreach (static::notificationTemplateDefaults() as $key => $value) {
            if (Setting::setIfEmpty($key, $value, 'site')) {
                $restored[] = $key;
            }
        }

        return $restored;
    }

    /** @return list<string> */
    public static function ensureSiteScaffoldDefaults(): array
    {
        $restored = [];

        foreach (static::siteScaffoldValues() as $key => $value) {
            if (Setting::setIfEmpty($key, $value, 'site')) {
                $restored[] = $key;
            }
        }

        return $restored;
    }

    /** Seed default HTML templates only when no notification template has been saved yet. */
    public static function ensureNotificationTemplatesIfUnset(): void
    {
        if (static::hasPersistedNotificationTemplates()) {
            return;
        }

        foreach (static::notificationTemplateDefaults() as $key => $value) {
            Setting::setIfEmpty($key, $value, 'site');
        }
    }

    public static function hasPersistedNotificationTemplates(): bool
    {
        return Setting::query()
            ->whereIn('key', array_keys(static::notificationTemplateDefaults()))
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->exists();
    }

    /** @return list<string> */
    public static function restoreEmptySiteValues(): array
    {
        return static::ensureSiteGroupPersisted();
    }

    /** @return array<string, string> */
    public static function emailVerificationDefaults(): array
    {
        return [
            'verification_link_expires_minutes' => '1440',
            'email_verification_subject' => EmailVerificationTemplate::subject(),
            'email_verification_body' => EmailVerificationTemplate::body(),
        ];
    }

    /** @return array<string, string> */
    public static function notificationTemplateDefaults(): array
    {
        return array_merge(static::emailVerificationDefaults(), [
            'password_reset_expires_minutes' => '60',
            'password_reset_subject' => PasswordResetTemplate::subject(),
            'password_reset_body' => PasswordResetTemplate::body(),
            'staff_invitation_expires_minutes' => '10080',
            'staff_invitation_subject' => StaffInvitationTemplate::subject(),
            'staff_invitation_body' => StaffInvitationTemplate::body(),
            'two_factor_enabled_subject' => TwoFactorTemplate::enabledSubject(),
            'two_factor_enabled_body' => TwoFactorTemplate::enabledBody(),
            'two_factor_disabled_subject' => TwoFactorTemplate::disabledSubject(),
            'two_factor_disabled_body' => TwoFactorTemplate::disabledBody(),
        ]);
    }

    /** @return array<string, string> */
    public static function mailDefaults(): array
    {
        return [
            'mail_mailer' => (string) env('MAIL_MAILER', 'log'),
            'mail_host' => (string) env('MAIL_HOST', ''),
            'mail_port' => (string) env('MAIL_PORT', '587'),
            'mail_username' => (string) env('MAIL_USERNAME', ''),
            'mail_encryption' => (string) env('MAIL_ENCRYPTION', 'tls'),
            'mail_from_address' => (string) env('MAIL_FROM_ADDRESS', ''),
            'mail_from_name' => (string) env('MAIL_FROM_NAME', config('app.name')),
            'mail_timeout_seconds' => '15',
        ];
    }

    public static function ensureMailSettingsIfUnset(): void
    {
        foreach (static::mailDefaults() as $key => $value) {
            Setting::setIfEmpty($key, $value, 'mail');
        }
    }

    public static function ensureBrandingDefaults(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Setting::setIfEmpty('site_logo_path', Branding::DEFAULT_LOGO_PATH, 'site');
        Setting::setIfEmpty('site_favicon_path', Branding::DEFAULT_FAVICON_PATH, 'site');
    }

    /** @return array<string, string> */
    public static function siteScaffoldValues(): array
    {
        return [
            'site_name' => config('site.name'),
            'panel_url' => config('site.panel_url'),
            'main_site_url' => config('site.main_site_url'),
            'contact_phone' => config('site.contact_phone'),
            'support_email' => config('site.support_email'),
            'default_locale' => config('site.default_locale'),
            'default_company_name' => '',
            'default_company_tax_number' => '',
            'default_company_phone' => '',
            'default_company_email' => '',
            'default_company_address' => '',
            'site_logo_height' => '40',
            'site_logo_height_register' => '32',
            'site_sidebar_logo_height' => '28',
            'site_sidebar_brand_height' => '56',
            'site_logo_path' => Branding::DEFAULT_LOGO_PATH,
            'site_favicon_path' => Branding::DEFAULT_FAVICON_PATH,
        ];
    }

    /** @return array<string, string> */
    public static function siteSeedValues(): array
    {
        return array_merge(static::siteScaffoldValues(), static::notificationTemplateDefaults());
    }
}
