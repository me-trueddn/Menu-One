<?php

namespace App\Support;

use App\Services\CloudflareImagesService;
use App\Services\CloudflareStreamService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

class MediaStorage
{
    public const SITE_DIR = 'images/Siteimage';

    public const CUSTOMER_DIR = 'images/Customerimage';

    public static function storeSiteFile(UploadedFile $file): string
    {
        return static::storeImageFile($file, MediaLimits::CONTEXT_SITE, static::SITE_DIR);
    }

    public static function storeCustomerFile(UploadedFile $file, string $tenantId): string
    {
        return static::storeImageFile(
            $file,
            MediaLimits::CONTEXT_LOGO,
            static::CUSTOMER_DIR.'/'.static::safeTenantId($tenantId),
        );
    }

    public static function storeProductFile(UploadedFile $file, string $tenantId): string
    {
        return static::storeImageFile(
            $file,
            MediaLimits::CONTEXT_PRODUCT,
            static::CUSTOMER_DIR.'/'.static::safeTenantId($tenantId).'/products',
            ['tenant_id' => $tenantId],
        );
    }

    public static function storeCategoryFile(UploadedFile $file, string $tenantId): string
    {
        return static::storeImageFile(
            $file,
            MediaLimits::CONTEXT_PRODUCT,
            static::CUSTOMER_DIR.'/'.static::safeTenantId($tenantId).'/categories',
            ['tenant_id' => $tenantId],
        );
    }

    /**
     * @param  array<string, string>  $metadata
     */
    public static function storeImageFile(
        UploadedFile $file,
        string $context,
        string $localDirectory,
        array $metadata = [],
    ): string {
        $mime = strtolower((string) $file->getMimeType());

        if (MediaLimits::shouldStayLocalMime($mime) || ! CloudflarePolicy::configured()) {
            return static::storePublicFile($file, $localDirectory);
        }

        $processed = MediaPreprocessor::process($file, $context);

        try {
            $images = app(CloudflareImagesService::class);
            $imageId = $images->uploadPath(
                $processed['path'],
                'upload.'.($processed['mime'] === 'image/webp' ? 'webp' : 'jpg'),
                $processed['mime'],
                array_merge($metadata, ['context' => $context]),
            );

            return CloudflarePolicy::imageReference($imageId);
        } finally {
            @unlink($processed['path']);
        }
    }

    public static function storeProductVideo(UploadedFile $file, string $tenantId): string
    {
        if (! CloudflarePolicy::streamEnabled()) {
            throw new RuntimeException(__('menu.cloudflare_stream_disabled'));
        }

        static::assertVideoDuration($file);

        $stream = app(CloudflareStreamService::class);
        $uid = $stream->upload($file, [
            'tenant_id' => $tenantId,
            'context' => 'product_video',
        ]);

        return CloudflarePolicy::streamReference($uid);
    }

    public static function storePublicFile(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    public static function url(?string $path, ?string $variant = null): ?string
    {
        if (! $path || trim($path) === '') {
            return null;
        }

        if (CloudflarePolicy::isImageReference($path)) {
            $imageId = CloudflarePolicy::imageIdFromReference($path);
            $variant ??= 'public';

            return app(CloudflareImagesService::class)->deliveryUrl($imageId, $variant);
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

    public static function streamPlaybackUrl(?string $reference): ?string
    {
        if (! $reference || ! CloudflarePolicy::isStreamReference($reference)) {
            return null;
        }

        $uid = CloudflarePolicy::streamUidFromReference($reference);

        return app(CloudflareStreamService::class)->playbackUrl($uid);
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (CloudflarePolicy::isImageReference($path)) {
            app(CloudflareImagesService::class)->delete(
                CloudflarePolicy::imageIdFromReference($path),
            );

            return;
        }

        if (CloudflarePolicy::isStreamReference($path)) {
            app(CloudflareStreamService::class)->delete(
                CloudflarePolicy::streamUidFromReference($path),
            );

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

    protected static function assertVideoDuration(UploadedFile $file): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $ffprobe = trim((string) shell_exec('where ffprobe 2>nul'));
        } else {
            $ffprobe = trim((string) shell_exec('command -v ffprobe 2>/dev/null'));
        }

        if ($ffprobe === '') {
            return;
        }

        $path = escapeshellarg($file->getRealPath());
        $output = shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 {$path}");
        $seconds = (float) trim((string) $output);

        if ($seconds > MediaLimits::maxVideoSeconds()) {
            throw new InvalidArgumentException(__('menu.media_video_too_long', [
                'seconds' => MediaLimits::maxVideoSeconds(),
            ]));
        }
    }

    protected static function safeTenantId(string $tenantId): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', $tenantId) ?: 'unknown';
    }
}
