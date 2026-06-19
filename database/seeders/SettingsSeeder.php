<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\EmailVerificationTemplate;
use App\Support\SecurityPolicy;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $site = [
            'site_name' => config('site.name'),
            'panel_url' => config('site.panel_url'),
            'main_site_url' => config('site.main_site_url'),
            'contact_phone' => config('site.contact_phone'),
            'support_email' => config('site.support_email'),
            'default_locale' => config('site.default_locale'),
        ];

        foreach ($site as $key => $value) {
            Setting::set($key, $value, 'site');
        }

        foreach (\App\Support\CaptchaPolicy::defaults() as $key => $value) {
            Setting::set($key, $value, 'site');
        }

        foreach (\App\Support\OAuthPolicy::defaults() as $key => $value) {
            Setting::set($key, $value, 'site');
        }

        $mail = [
            'mail_mailer' => env('MAIL_MAILER', 'log'),
            'mail_host' => env('MAIL_HOST', ''),
            'mail_port' => env('MAIL_PORT', '587'),
            'mail_username' => env('MAIL_USERNAME', ''),
            'mail_password' => '',
            'mail_encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS', 'noreply@trueddn.com.tr'),
            'mail_from_name' => env('MAIL_FROM_NAME', 'TrueDDN Panel'),
            'mail_timeout_seconds' => '15',
        ];

        foreach ($mail as $key => $value) {
            Setting::set($key, $value, 'mail');
        }

        $security = SecurityPolicy::defaults();

        foreach ($security as $key => $value) {
            Setting::set($key, $value, 'security');
        }

        foreach ([
            'verification_link_expires_minutes' => '1440',
            'email_verification_subject' => EmailVerificationTemplate::subject(),
            'email_verification_body' => EmailVerificationTemplate::body(),
            'default_company_name' => '',
            'default_company_tax_number' => '',
            'default_company_phone' => '',
            'default_company_email' => '',
            'default_company_address' => '',
        ] as $key => $value) {
            Setting::set($key, $value, 'site');
        }
    }
}
