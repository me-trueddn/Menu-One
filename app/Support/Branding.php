<?php

namespace App\Support;

use App\Models\Setting;

class Branding
{
    public static function logoUrl(): string
    {
        $path = Setting::get('site_logo_path');

        if ($path && is_file(public_path($path))) {
            return asset($path);
        }

        return asset('images/logo-default.svg');
    }

    public static function faviconUrl(): string
    {
        $path = Setting::get('site_favicon_path');

        if ($path && is_file(public_path($path))) {
            return asset($path);
        }

        return asset('images/logo-default.svg');
    }

    public static function tenantLogoUrl(?string $path): ?string
    {
        if ($path && is_file(storage_path('app/public/'.$path))) {
            return asset('storage/'.$path);
        }

        return null;
    }
}
