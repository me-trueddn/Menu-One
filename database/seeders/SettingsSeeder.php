<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\CaptchaPolicy;
use App\Support\CloudflarePolicy;
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

        foreach (CloudflarePolicy::defaults() as $key => $value) {
            Setting::setIfMissing($key, $value, 'site');
        }

        SettingsDefaults::restoreEmptySiteValues();

        foreach (SettingsDefaults::mailDefaults() as $key => $value) {
            Setting::setIfMissing($key, $value, 'mail');
        }

        foreach (SecurityPolicy::defaults() as $key => $value) {
            Setting::setIfMissing($key, $value, 'security');
        }
    }
}
