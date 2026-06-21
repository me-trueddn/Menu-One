<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class SiteConfig
{
    public static function get(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable('settings')) {
            return config('site.'.str_replace('site_', '', $key), $default) ?? $default;
        }

        $map = [
            'site_name' => config('site.name'),
            'panel_url' => config('site.panel_url'),
            'main_site_url' => config('site.main_site_url'),
            'contact_phone' => config('site.contact_phone'),
            'support_email' => config('site.support_email'),
            'default_locale' => config('site.default_locale'),
        ];

        return Setting::get($key, $map[$key] ?? $default);
    }

    public static function name(): string
    {
        return (string) static::get('site_name', config('app.name'));
    }

    public static function panelUrl(): string
    {
        return (string) static::get('panel_url', config('app.url'));
    }

    public static function mainSiteUrl(): string
    {
        return (string) static::get('main_site_url', config('site.main_site_url'));
    }

    public static function contactPhone(): string
    {
        return (string) static::get('contact_phone', '');
    }

    public static function supportEmail(): string
    {
        return (string) static::get('support_email', '');
    }
}
