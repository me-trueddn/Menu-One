<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\CaptchaPolicy;
use App\Support\OAuthPolicy;
use App\Support\SecurityPolicy;
use App\Support\SettingsDefaults;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SettingsDefaults::siteSeedValues() as $key => $value) {
            Setting::setIfMissing($key, $value, 'site');
        }

        foreach (CaptchaPolicy::defaults() as $key => $value) {
            Setting::setIfMissing($key, $value, 'site');
        }

        foreach (OAuthPolicy::defaults() as $key => $value) {
            Setting::setIfMissing($key, $value, 'site');
        }

        SettingsDefaults::restoreEmptySiteValues();

        $mail = [
            'mail_mailer' => env('MAIL_MAILER', 'log'),
            'mail_host' => env('MAIL_HOST', ''),
            'mail_port' => env('MAIL_PORT', '587'),
            'mail_username' => env('MAIL_USERNAME', ''),
            'mail_password' => '',
            'mail_encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'mail_from_name' => env('MAIL_FROM_NAME', config('app.name')),
            'mail_timeout_seconds' => '15',
        ];

        foreach ($mail as $key => $value) {
            Setting::setIfMissing($key, $value, 'mail');
        }

        foreach (SecurityPolicy::defaults() as $key => $value) {
            Setting::setIfMissing($key, $value, 'security');
        }
    }
}
