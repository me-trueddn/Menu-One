<?php

namespace App\Support;

use App\Models\Setting;

class SettingsDefaults
{
    /** @return list<string> */
    public static function restoreEmptySiteValues(): array
    {
        $restored = [];

        foreach (static::emailVerificationDefaults() as $key => $value) {
            if (Setting::setIfEmpty($key, $value, 'site')) {
                $restored[] = $key;
            }
        }

        return $restored;
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
    public static function siteSeedValues(): array
    {
        return array_merge([
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
        ], static::emailVerificationDefaults());
    }
}
