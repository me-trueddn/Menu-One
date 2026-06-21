<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class CaptchaPolicy
{
    public const CONTEXT_LOGIN = 'login';

    public const CONTEXT_REGISTER = 'register';

    public const CONTEXT_PASSWORD_RESET = 'password_reset';

    /** @return array<string, string> */
    public static function defaults(): array
    {
        return [
            'captcha_provider' => 'none',
            'captcha_site_key' => '',
            'captcha_secret_key' => '',
            'captcha_login_enabled' => '0',
            'captcha_register_enabled' => '0',
            'captcha_password_reset_enabled' => '0',
            'registration_enabled' => '1',
        ];
    }

    public static function bool(string $key): bool
    {
        return filter_var(Setting::getFilled($key, static::defaults()[$key] ?? '0'), FILTER_VALIDATE_BOOLEAN);
    }

    public static function provider(): string
    {
        return (string) Setting::getFilled('captcha_provider', 'none');
    }

    public static function siteKey(): string
    {
        return (string) Setting::getFilled('captcha_site_key', '');
    }

    public static function secretKey(): string
    {
        $encrypted = Setting::get('captcha_secret_key');

        if (! $encrypted) {
            return '';
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return '';
        }
    }

    public static function configured(): bool
    {
        return static::provider() !== 'none'
            && static::siteKey() !== ''
            && static::secretKey() !== '';
    }

    public static function requiredFor(string $context): bool
    {
        if (! static::configured()) {
            return false;
        }

        return match ($context) {
            self::CONTEXT_LOGIN => static::bool('captcha_login_enabled'),
            self::CONTEXT_REGISTER => static::bool('captcha_register_enabled'),
            self::CONTEXT_PASSWORD_RESET => static::bool('captcha_password_reset_enabled'),
            default => false,
        };
    }

    public static function registrationEnabled(): bool
    {
        return static::bool('registration_enabled');
    }
}
