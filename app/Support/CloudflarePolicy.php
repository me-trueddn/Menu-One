<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class CloudflarePolicy
{
    public const IMAGE_REF_PREFIX = 'cfi:';

    public const STREAM_REF_PREFIX = 'cfs:';

    /** @var list<string> */
    public const IMAGE_VARIANTS = ['public', 'product', 'logo'];

    /** @return array<string, string> */
    public static function defaults(): array
    {
        return [
            'cloudflare_images_enabled' => '0',
            'cloudflare_account_id' => '',
            'cloudflare_account_hash' => '',
            'cloudflare_api_token' => '',
            'cloudflare_stream_enabled' => '0',
            'cloudflare_stream_customer_subdomain' => '',
        ];
    }

    public static function bool(string $key): bool
    {
        $defaults = static::defaults();

        return Setting::boolean($key, filter_var($defaults[$key] ?? '0', FILTER_VALIDATE_BOOLEAN));
    }

    public static function imagesEnabled(): bool
    {
        return static::bool('cloudflare_images_enabled');
    }

    public static function streamEnabled(): bool
    {
        return static::imagesEnabled() && static::bool('cloudflare_stream_enabled');
    }

    public static function configured(): bool
    {
        return static::imagesEnabled()
          && static::accountId() !== ''
          && static::accountHash() !== ''
          && static::apiToken() !== '';
    }

    public static function accountId(): string
    {
        return trim((string) (Setting::mergedGroup('site', static::defaults())['cloudflare_account_id'] ?? ''));
    }

    public static function accountHash(): string
    {
        return trim((string) (Setting::mergedGroup('site', static::defaults())['cloudflare_account_hash'] ?? ''));
    }

    public static function apiToken(): string
    {
        $encrypted = Setting::get('cloudflare_api_token');

        if (! $encrypted) {
            return '';
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return '';
        }
    }

    public static function hasStoredApiToken(): bool
    {
        $encrypted = Setting::get('cloudflare_api_token');

        return is_string($encrypted) && trim($encrypted) !== '';
    }

    public static function apiTokenDecryptFailed(): bool
    {
        return static::hasStoredApiToken() && static::apiToken() === '';
    }

    public static function streamCustomerSubdomain(): string
    {
        return trim((string) (Setting::mergedGroup('site', static::defaults())['cloudflare_stream_customer_subdomain'] ?? ''));
    }

    public static function isImageReference(?string $path): bool
    {
        return is_string($path) && str_starts_with($path, static::IMAGE_REF_PREFIX);
    }

    public static function isStreamReference(?string $path): bool
    {
        return is_string($path) && str_starts_with($path, static::STREAM_REF_PREFIX);
    }

    public static function imageIdFromReference(string $reference): string
    {
        return substr($reference, strlen(static::IMAGE_REF_PREFIX));
    }

    public static function streamUidFromReference(string $reference): string
    {
        return substr($reference, strlen(static::STREAM_REF_PREFIX));
    }

    public static function imageReference(string $imageId): string
    {
        return static::IMAGE_REF_PREFIX.$imageId;
    }

    public static function streamReference(string $uid): string
    {
        return static::STREAM_REF_PREFIX.$uid;
    }

    public static function sampleDeliveryUrl(): string
    {
        $hash = static::accountHash() !== '' ? static::accountHash() : 'YOUR_ACCOUNT_HASH';

        return "https://imagedelivery.net/{$hash}/IMAGE_ID/public";
    }

    public static function samplePlaybackUrl(): string
    {
        $subdomain = static::streamCustomerSubdomain();

        if ($subdomain !== '') {
            return "https://{$subdomain}/STREAM_UID/watch";
        }

        return 'https://customer-videodelivery.net/STREAM_UID/watch';
    }
}
