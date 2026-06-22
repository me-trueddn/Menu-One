<?php

namespace App\Support;

class EmailTemplateVariables
{
    /** @return array<string, string> */
    public static function base(): array
    {
        return [
            'site_name' => SiteConfig::name(),
            'site_logo_url' => Branding::logoUrl(),
        ];
    }
}
