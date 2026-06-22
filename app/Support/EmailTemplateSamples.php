<?php

namespace App\Support;

use App\Support\Branding;

class EmailTemplateSamples
{
    /** @return array<string, array<string, string>> */
    public static function all(): array
    {
        $siteName = SiteConfig::name();
        $base = [
            'name' => __('menu.email_sample_name'),
            'email' => __('menu.email_sample_email'),
            'site_name' => $siteName,
            'site_logo_url' => Branding::logoUrl(),
        ];

        return [
            'verification' => array_merge($base, [
                'name' => __('menu.email_sample_name'),
                'email' => __('menu.email_sample_email'),
                'verify_url' => url('/email/verify/'.str_repeat('a', 32)),
                'expires_minutes' => '1440',
            ]),
            'password_reset' => array_merge($base, [
                'name' => __('menu.email_sample_name'),
                'email' => __('menu.email_sample_email'),
                'reset_url' => url('/reset-password/'.str_repeat('b', 32).'?email=ornek@firma.com'),
                'expires_minutes' => '60',
            ]),
            'staff_invitation' => array_merge($base, [
                'name' => __('menu.email_sample_name'),
                'email' => __('menu.email_sample_email'),
                'invite_url' => url('/staff/invitation/'.str_repeat('c', 32)),
                'cafe_name' => __('menu.email_sample_cafe'),
                'role' => __('menu.role_waiter'),
                'invited_by' => __('menu.email_sample_inviter'),
                'expires_minutes' => '10080',
            ]),
            'two_factor_enabled' => $base,
            'two_factor_disabled' => $base,
        ];
    }
}
