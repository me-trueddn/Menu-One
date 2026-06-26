<?php

namespace App\Support;

use App\Models\Setting;

class LogSettings
{
    public const PLATFORM_RETENTION_KEY = 'log_platform_retention_days';

    public const CAFE_RETENTION_KEY = 'log_cafe_retention_days';

    public const DEFAULT_RETENTION_DAYS = 30;

    /** @return list<int> */
    public static function perPageOptions(): array
    {
        return [20, 50, 100, 150];
    }

    public static function defaultPerPage(): int
    {
        return 20;
    }

    public static function platformRetentionDays(): int
    {
        return max(1, min(365, (int) Setting::getFilled(self::PLATFORM_RETENTION_KEY, self::DEFAULT_RETENTION_DAYS)));
    }

    public static function cafeRetentionDays(): int
    {
        return max(1, min(365, (int) Setting::getFilled(self::CAFE_RETENTION_KEY, self::DEFAULT_RETENTION_DAYS)));
    }
}
