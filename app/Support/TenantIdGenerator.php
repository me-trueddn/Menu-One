<?php

namespace App\Support;

use App\Models\Tenant;

class TenantIdGenerator
{
    public const PATTERN = '/^\d{3}-\d{3}$/';

    public static function generate(): string
    {
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $id = sprintf('%03d-%03d', random_int(0, 999), random_int(0, 999));

            if (! Tenant::query()->whereKey($id)->exists()) {
                return $id;
            }
        }

        throw new \RuntimeException('Unable to generate a unique tenant id.');
    }

    public static function isValid(?string $id): bool
    {
        return is_string($id) && preg_match(self::PATTERN, $id) === 1;
    }
}
