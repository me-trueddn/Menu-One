<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class SeoPolicy
{
    /** @return array<string, string> */
    public static function defaults(): array
    {
        return [
            'seo_enabled' => '0',
            'seo_index_public' => '1',
            'seo_track_authenticated' => '0',
            'google_tag_manager_id' => '',
            'google_analytics_id' => '',
            'google_search_console_verification' => '',
            'google_ads_id' => '',
            'google_ads_conversion_label' => '',
            'yandex_webmaster_verification' => '',
            'yandex_metrika_id' => '',
            'seo_meta_title' => '',
            'seo_meta_description' => '',
            'seo_meta_keywords' => '',
            'seo_organization_name' => '',
            'seo_organization_url' => '',
            'seo_og_image_path' => '',
        ];
    }

    public static function ensureDefaults(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        foreach (static::defaults() as $key => $value) {
            Setting::setIfEmpty($key, $value, 'seo');
        }
    }

    public static function enabled(): bool
    {
        return Setting::boolean('seo_enabled');
    }

    public static function indexPublic(): bool
    {
        return Setting::boolean('seo_index_public', true);
    }

    public static function trackAuthenticated(): bool
    {
        return Setting::boolean('seo_track_authenticated');
    }

    public static function shouldInject(?bool $isPublicPage = null): bool
    {
        if (! static::enabled()) {
            return false;
        }

        if ($isPublicPage === null) {
            return true;
        }

        if ($isPublicPage) {
            return true;
        }

        return static::trackAuthenticated();
    }

    public static function tagManagerId(): string
    {
        return trim((string) Setting::getFilled('google_tag_manager_id', ''));
    }

    public static function analyticsId(): string
    {
        return trim((string) Setting::getFilled('google_analytics_id', ''));
    }

    public static function searchConsoleVerification(): string
    {
        return trim((string) Setting::getFilled('google_search_console_verification', ''));
    }

    public static function adsId(): string
    {
        return trim((string) Setting::getFilled('google_ads_id', ''));
    }

    public static function adsConversionLabel(): string
    {
        return trim((string) Setting::getFilled('google_ads_conversion_label', ''));
    }

    public static function yandexWebmasterVerification(): string
    {
        return trim((string) Setting::getFilled('yandex_webmaster_verification', ''));
    }

    public static function yandexMetrikaId(): string
    {
        return trim((string) Setting::getFilled('yandex_metrika_id', ''));
    }

    public static function metaTitle(?string $fallback = null): string
    {
        $title = trim((string) Setting::getFilled('seo_meta_title', ''));

        if ($title !== '') {
            return $title;
        }

        return $fallback ?? SiteConfig::name();
    }

    public static function metaDescription(): string
    {
        return trim((string) Setting::getFilled('seo_meta_description', ''));
    }

    public static function metaKeywords(): string
    {
        return trim((string) Setting::getFilled('seo_meta_keywords', ''));
    }

    public static function canonicalUrl(): string
    {
        return url()->current();
    }

    public static function ogImageUrl(): ?string
    {
        $path = Setting::get('seo_og_image_path');

        if (! $path) {
            $logo = Setting::get('site_logo_path');

            return $logo ? ImageStorage::url($logo, MediaLimits::variantForContext(MediaLimits::CONTEXT_SITE)) : Branding::defaultLogoUrl();
        }

        return ImageStorage::url($path, MediaLimits::variantForContext(MediaLimits::CONTEXT_SITE));
    }

    public static function organizationName(): string
    {
        $name = trim((string) Setting::getFilled('seo_organization_name', ''));

        return $name !== '' ? $name : SiteConfig::name();
    }

    public static function organizationUrl(): string
    {
        $url = trim((string) Setting::getFilled('seo_organization_url', ''));

        if ($url !== '') {
            return $url;
        }

        $main = SiteConfig::mainSiteUrl();

        return $main !== '' ? $main : config('app.url');
    }

    /** @return array<string, mixed>|null */
    public static function organizationSchema(): ?array
    {
        if (! static::enabled()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => static::organizationName(),
            'url' => static::organizationUrl(),
            'logo' => static::ogImageUrl(),
        ];
    }

    /** @return list<string> */
    public static function sitemapPaths(): array
    {
        $paths = [
            route('login'),
        ];

        if (CaptchaPolicy::registrationEnabled()) {
            $paths[] = route('register');
        }

        return array_values(array_unique($paths));
    }
}
