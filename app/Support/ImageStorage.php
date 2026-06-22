<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageStorage
{
    public const SITE_DIR = 'images/Siteimage';

    public const CUSTOMER_DIR = 'images/Customerimage';

    public static function storeSiteFile(UploadedFile $file): string
    {
        return static::storePublicFile($file, static::SITE_DIR);
    }

    public static function storeCustomerFile(UploadedFile $file, string $tenantId): string
    {
        return static::storePublicFile($file, static::CUSTOMER_DIR.'/'.static::safeTenantId($tenantId));
    }

    public static function storeProductFile(UploadedFile $file, string $tenantId): string
    {
        return static::storePublicFile(
            $file,
            static::CUSTOMER_DIR.'/'.static::safeTenantId($tenantId).'/products'
        );
    }

    public static function storePublicFile(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');

        return $path;
    }

    public static function url(?string $path): ?string
    {
        if (! $path || trim($path) === '') {
            return null;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            return is_file(public_path($path)) ? '/'.$path : null;
        }

        if (Storage::disk('public')->exists($path)) {
            return '/storage/'.$path;
        }

        if (is_file(public_path($path))) {
            return '/'.$path;
        }

        return null;
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (str_starts_with($path, 'storage/')) {
            @unlink(public_path($path));

            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected static function safeTenantId(string $tenantId): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', $tenantId) ?: 'unknown';
    }
}
