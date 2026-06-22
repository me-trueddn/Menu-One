<?php

namespace App\Support;

use App\Models\Setting;

class SettingPersistence
{
    public static function isPresent(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }

    /**
     * Use incoming value when provided; otherwise keep the stored value.
     * Falls back only when neither incoming nor stored values exist.
     */
    public static function keepOrApply(string $key, mixed $incoming, mixed $fallback = null): mixed
    {
        if (static::isPresent($incoming)) {
            return $incoming;
        }

        $existing = Setting::get($key);

        if (static::isPresent($existing)) {
            return $existing;
        }

        return $fallback ?? '';
    }

    /**
     * Apply incoming value when provided; otherwise leave the stored value untouched.
     * Returns null when the key should not be written at all.
     */
    public static function incomingOrSkip(string $key, mixed $incoming): mixed
    {
        if (static::isPresent($incoming)) {
            return $incoming;
        }

        return null;
    }
}
