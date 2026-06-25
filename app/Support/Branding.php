<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\Tenant;

class Branding
{
    public const DEFAULT_LOGO_PATH = 'images/logo-default.svg';

    public const DEFAULT_FAVICON_PATH = 'images/logo-default.svg';

    public static function defaultLogoUrl(): string
    {
        return '/'.ltrim(static::DEFAULT_LOGO_PATH, '/');
    }

    public static function defaultFaviconUrl(): string
    {
        return '/'.ltrim(static::DEFAULT_FAVICON_PATH, '/');
    }

    public static function logoUrl(): string
    {
        $path = Setting::getFilled('site_logo_path');

        if ($path !== null) {
            $url = ImageStorage::url($path, MediaLimits::variantForContext(MediaLimits::CONTEXT_SITE));
            if ($url !== null) {
                return $url;
            }
        }

        return static::defaultLogoUrl();
    }

    /** Logo height on the login page. */
    public static function logoHeight(): int
    {
        $height = (int) Setting::getFilled('site_logo_height', 40);

        return max(16, min($height, 160));
    }

    /** Logo height on the registration panel. */
    public static function registerLogoHeight(): int
    {
        $height = (int) Setting::getFilled('site_logo_height_register', 32);

        return max(16, min($height, 120));
    }

    /** Logo height inside the sidebar brand area. */
    public static function sidebarLogoHeight(): int
    {
        $height = (int) Setting::getFilled('site_sidebar_logo_height', 28);

        return max(16, min($height, 120));
    }

    /** Sidebar brand bar height (logo container). */
    public static function sidebarBrandHeight(): int
    {
        $height = (int) Setting::getFilled('site_sidebar_brand_height', 56);

        return max(40, min($height, 160));
    }

    public static function faviconUrl(): string
    {
        $path = Setting::getFilled('site_favicon_path');

        if ($path !== null) {
            $url = ImageStorage::url($path, MediaLimits::variantForContext(MediaLimits::CONTEXT_SITE));
            if ($url !== null) {
                return $url;
            }
        }

        return static::defaultFaviconUrl();
    }

    public static function tenantLogoUrl(?string $path): ?string
    {
        return ImageStorage::url($path, MediaLimits::variantForContext(MediaLimits::CONTEXT_LOGO));
    }

    /** Tenant logo when set; otherwise the site logo (never empty). */
    public static function tenantLogoUrlOrSite(?string $path): string
    {
        return static::tenantLogoUrl($path) ?? static::logoUrl();
    }

    /**
     * Logo for the active cafe context: tenant upload when present, else site logo.
     * Pass a tenant explicitly on platform screens; omit to use the initialized tenant().
     */
    public static function cafeLogoUrl(?Tenant $tenant = null): string
    {
        if ($tenant === null && function_exists('tenant')) {
            $resolved = tenant();
            $tenant = $resolved instanceof Tenant ? $resolved : null;
        }

        if ($tenant instanceof Tenant && filled($tenant->logo_path)) {
            return static::tenantLogoUrlOrSite($tenant->logo_path);
        }

        return static::logoUrl();
    }
}
