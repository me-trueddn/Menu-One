<?php

namespace App\Support;

use App\Models\Setting;

class EmailTemplatePolicy
{
    public static function verificationExpiresMinutes(): int
    {
        $minutes = (int) Setting::getFilled('verification_link_expires_minutes', 1440);

        return max(5, min($minutes, 10080));
    }

    public static function verificationSubject(): string
    {
        $default = app()->getLocale() === 'en'
            ? EmailVerificationTemplate::subjectEn()
            : EmailVerificationTemplate::subject();

        return (string) Setting::getFilled('email_verification_subject', $default);
    }

    public static function verificationBody(): string
    {
        $default = app()->getLocale() === 'en'
            ? EmailVerificationTemplate::bodyEn()
            : EmailVerificationTemplate::body();

        return (string) Setting::getFilled('email_verification_body', $default);
    }
}
