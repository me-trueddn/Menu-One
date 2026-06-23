<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Validation\Rules\Password;

class SecurityPolicy
{
    public static function int(string $key, int $default = 0): int
    {
        return (int) Setting::get($key, (string) $default);
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return filter_var(Setting::get($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }

    /** Parola geçerlilik süresi (gün). 0 = süresiz. ISO 27001 · 8.5 */
    public static function passwordExpiryDays(): int
    {
        return static::int('security_password_expiry_days', 90);
    }

    /** Sıfırlama e-postasındaki link geçerliliği (dakika). */
    public static function resetLinkMinutes(): int
    {
        return static::int('security_reset_link_minutes', 60);
    }

    public static function passwordMinLength(): int
    {
        return static::int('security_password_min_length', 8);
    }

    public static function passwordMinAgeDays(): int
    {
        return static::int('security_password_min_age_days', 1);
    }

    public static function passwordHistoryCount(): int
    {
        return static::int('security_password_history_count', 5);
    }

    public static function lockoutAttempts(): int
    {
        return static::int('security_lockout_attempts', 5);
    }

    public static function lockoutMinutes(): int
    {
        return static::int('security_lockout_minutes', 15);
    }

    public static function sessionIdleMinutes(): int
    {
        return static::int('security_session_idle_minutes', 30);
    }

    public static function passwordRules(): Password
    {
        if (app()->environment('testing')) {
            return Password::min(8);
        }

        $rule = Password::min(static::passwordMinLength());

        if (static::bool('security_password_require_uppercase')) {
            $rule->letters()->mixedCase();
        } elseif (static::bool('security_password_require_lowercase', true)) {
            $rule->letters();
        }

        if (static::bool('security_password_require_number', true)) {
            $rule->numbers();
        }

        if (static::bool('security_password_require_symbol')) {
            $rule->symbols();
        }

        return $rule;
    }

    public static function defaults(): array
    {
        return [
            'security_password_min_length' => '8',
            'security_password_expiry_days' => '90',
            'security_password_min_age_days' => '1',
            'security_password_history_count' => '5',
            'security_password_require_uppercase' => '1',
            'security_password_require_lowercase' => '1',
            'security_password_require_number' => '1',
            'security_password_require_symbol' => '0',
            'security_lockout_attempts' => '5',
            'security_lockout_minutes' => '15',
            'security_session_idle_minutes' => '30',
            'security_reset_link_minutes' => '60',
            'security_inactive_account_days' => '0',
            'security_2fa_required' => '0',
            'security_2fa_enabled_globally' => '1',
        ];
    }
}
