<?php

namespace App\Support;

use App\Models\Tenant;

class TenantIdMatcher
{
    public static function matches(?string $stored, string $tenantId): bool
    {
        if ($stored === null || $stored === '') {
            return false;
        }

        $stored = (string) $stored;
        $tenantId = (string) $tenantId;

        if ($stored === $tenantId) {
            return true;
        }

        return self::isTruncatedPrefix($stored, $tenantId);
    }

    public static function isTruncatedPrefix(string $stored, string $fullId): bool
    {
        return preg_match('/^\d+$/', $stored) === 1
            && str_starts_with($fullId, $stored.'-');
    }

    public static function resolveFullId(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }

        $stored = (string) $stored;

        if (Tenant::query()->whereKey($stored)->exists()) {
            return $stored;
        }

        if (! preg_match('/^\d+$/', $stored)) {
            return null;
        }

        $candidates = Tenant::query()
            ->where('id', 'like', $stored.'-%')
            ->pluck('id');

        return $candidates->count() === 1 ? (string) $candidates->first() : null;
    }
}
