<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\LogSettings;
use Illuminate\Database\Seeder;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        Setting::setIfMissing(LogSettings::PLATFORM_RETENTION_KEY, (string) LogSettings::DEFAULT_RETENTION_DAYS, 'logs');
        Setting::setIfMissing(LogSettings::CAFE_RETENTION_KEY, (string) LogSettings::DEFAULT_RETENTION_DAYS, 'logs');
    }
}
