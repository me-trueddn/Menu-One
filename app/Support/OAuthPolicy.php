<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class OAuthPolicy
{
    /** @return array<string, string> */
    public static function defaults(): array
    {
        return [
            'oauth_google_enabled' => '0',
            'oauth_google_client_id' => '',
            'oauth_google_client_secret' => '',
            'oauth_microsoft_enabled' => '0',
            'oauth_microsoft_client_id' => '',
            'oauth_microsoft_client_secret' => '',
            'oauth_allow_login' => '1',
            'oauth_allow_register' => '1',
        ];
    }

    public static function bool(string $key): bool
    {
        $defaults = static::defaults();

        return Setting::boolean($key, filter_var($defaults[$key] ?? '0', FILTER_VALIDATE_BOOLEAN));
    }

    public static function googleConfigured(): bool
    {
        return static::bool('oauth_google_enabled')
            && static::clientId('google') !== ''
            && static::clientSecret('google') !== '';
    }

    public static function microsoftConfigured(): bool
    {
        return static::bool('oauth_microsoft_enabled')
            && static::clientId('microsoft') !== ''
            && static::clientSecret('microsoft') !== '';
    }

    public static function clientId(string $provider): string
    {
        return trim((string) (Setting::mergedGroup('site', static::defaults())['oauth_'.$provider.'_client_id'] ?? ''));
    }

    public static function clientSecret(string $provider): string
    {
        $encrypted = Setting::get('oauth_'.$provider.'_client_secret');

        if (! $encrypted) {
            return '';
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return '';
        }
    }

    public static function hasStoredClientSecret(string $provider): bool
    {
        $encrypted = Setting::get('oauth_'.$provider.'_client_secret');

        return is_string($encrypted) && trim($encrypted) !== '';
    }

    public static function clientSecretDecryptFailed(string $provider): bool
    {
        return static::hasStoredClientSecret($provider) && static::clientSecret($provider) === '';
    }

    public static function redirectUrl(string $provider): string
    {
        $base = SiteConfig::firstUsablePanelUrl();

        if ($base === null) {
            if (request()->getHost() !== '') {
                return url("/auth/{$provider}/callback");
            }

            $base = rtrim((string) config('app.url'), '/');
        }

        return rtrim($base, '/')."/auth/{$provider}/callback";
    }

    public static function allowLogin(): bool
    {
        return static::bool('oauth_allow_login');
    }

    public static function allowRegister(): bool
    {
        return static::bool('oauth_allow_register');
    }

    /** @return list<string> */
    public static function enabledProviders(): array
    {
        $providers = [];

        if (static::googleConfigured()) {
            $providers[] = 'google';
        }

        if (static::microsoftConfigured()) {
            $providers[] = 'microsoft';
        }

        return $providers;
    }

    public static function socialiteDriver(string $provider): string
    {
        return $provider === 'microsoft' ? 'azure' : $provider;
    }
}
