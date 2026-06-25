<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class ImageStorage
{
    public const SITE_DIR = MediaStorage::SITE_DIR;

    public const CUSTOMER_DIR = MediaStorage::CUSTOMER_DIR;

    public static function storeSiteFile(UploadedFile $file): string
    {
        return MediaStorage::storeSiteFile($file);
    }

    public static function storeCustomerFile(UploadedFile $file, string $tenantId): string
    {
        return MediaStorage::storeCustomerFile($file, $tenantId);
    }

    public static function storeProductFile(UploadedFile $file, string $tenantId): string
    {
        return MediaStorage::storeProductFile($file, $tenantId);
    }

    public static function storePublicFile(UploadedFile $file, string $directory): string
    {
        return MediaStorage::storePublicFile($file, $directory);
    }

    public static function url(?string $path, ?string $variant = null): ?string
    {
        return MediaStorage::url($path, $variant);
    }

    public static function delete(?string $path): void
    {
        MediaStorage::delete($path);
    }
}
