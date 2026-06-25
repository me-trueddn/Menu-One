<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class LicenseGateSettings
{
    /** @return array<string, string> */
    public static function defaults(): array
    {
        return [
            'licensegate_enabled' => '0',
            'licensegate_user_id' => '',
            'licensegate_base_url' => (string) config('licensegate.default_base_url'),
            'licensegate_verify_on_access' => '1',
            'licensegate_strict_mode' => '0',
        ];
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        return Setting::mergedGroup('licensegate', static::defaults());
    }

    public static function enabled(): bool
    {
        return filter_var(static::all()['licensegate_enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN);
    }

    public static function verifyOnAccess(): bool
    {
        return filter_var(static::all()['licensegate_verify_on_access'] ?? '1', FILTER_VALIDATE_BOOLEAN);
    }

    public static function strictMode(): bool
    {
        return filter_var(static::all()['licensegate_strict_mode'] ?? '0', FILTER_VALIDATE_BOOLEAN);
    }

    public static function userId(): string
    {
        return trim((string) (static::all()['licensegate_user_id'] ?? ''));
    }

    public static function baseUrl(): string
    {
        $url = trim((string) (static::all()['licensegate_base_url'] ?? ''));

        return rtrim($url !== '' ? $url : (string) config('licensegate.default_base_url'), '/');
    }

    public static function adminToken(): string
    {
        $encrypted = Setting::get('licensegate_admin_token');

        if (! is_string($encrypted) || $encrypted === '') {
            return '';
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return '';
        }
    }

    public static function isConfigured(): bool
    {
        return static::userId() !== '' && static::adminToken() !== '';
    }
}
