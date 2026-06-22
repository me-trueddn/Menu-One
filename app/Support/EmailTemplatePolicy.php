<?php

namespace App\Support;

use App\Models\Setting;

class EmailTemplatePolicy
{
    public static function verificationExpiresMinutes(): int
    {
        return static::boundedMinutes('verification_link_expires_minutes', 1440);
    }

    public static function verificationSubject(): string
    {
        return static::localized(
            'email_verification_subject',
            EmailVerificationTemplate::subject(),
            EmailVerificationTemplate::subjectEn(),
        );
    }

    public static function verificationBody(): string
    {
        return static::localized(
            'email_verification_body',
            EmailVerificationTemplate::body(),
            EmailVerificationTemplate::bodyEn(),
        );
    }

    public static function passwordResetExpiresMinutes(): int
    {
        return static::boundedMinutes('password_reset_expires_minutes', 60);
    }

    public static function passwordResetSubject(): string
    {
        return static::localized(
            'password_reset_subject',
            PasswordResetTemplate::subject(),
            PasswordResetTemplate::subjectEn(),
        );
    }

    public static function passwordResetBody(): string
    {
        return static::localized(
            'password_reset_body',
            PasswordResetTemplate::body(),
            PasswordResetTemplate::bodyEn(),
        );
    }

    public static function staffInvitationExpiresMinutes(): int
    {
        return static::boundedMinutes('staff_invitation_expires_minutes', 10080);
    }

    public static function staffInvitationSubject(): string
    {
        return static::localized(
            'staff_invitation_subject',
            StaffInvitationTemplate::subject(),
            StaffInvitationTemplate::subjectEn(),
        );
    }

    public static function staffInvitationBody(): string
    {
        return static::localized(
            'staff_invitation_body',
            StaffInvitationTemplate::body(),
            StaffInvitationTemplate::bodyEn(),
        );
    }

    public static function twoFactorEnabledSubject(): string
    {
        return static::localized(
            'two_factor_enabled_subject',
            TwoFactorTemplate::enabledSubject(),
            TwoFactorTemplate::enabledSubjectEn(),
        );
    }

    public static function twoFactorEnabledBody(): string
    {
        return static::localized(
            'two_factor_enabled_body',
            TwoFactorTemplate::enabledBody(),
            TwoFactorTemplate::enabledBodyEn(),
        );
    }

    public static function twoFactorDisabledSubject(): string
    {
        return static::localized(
            'two_factor_disabled_subject',
            TwoFactorTemplate::disabledSubject(),
            TwoFactorTemplate::disabledSubjectEn(),
        );
    }

    public static function twoFactorDisabledBody(): string
    {
        return static::localized(
            'two_factor_disabled_body',
            TwoFactorTemplate::disabledBody(),
            TwoFactorTemplate::disabledBodyEn(),
        );
    }

    protected static function boundedMinutes(string $key, int $default): int
    {
        $minutes = (int) Setting::getFilled($key, (string) $default);

        return max(5, min($minutes, 10080));
    }

    protected static function localized(string $key, string $trDefault, string $enDefault): string
    {
        $default = app()->getLocale() === 'en' ? $enDefault : $trDefault;

        return (string) Setting::getFilled($key, $default);
    }
}
