<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

class MediaPreprocessor
{
    /**
     * @return array{path: string, mime: string}
     */
    public static function process(UploadedFile $file, string $context): array
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException(__('menu.media_gd_required'));
        }

        $config = MediaLimits::imageContext($context);
        $mime = strtolower((string) $file->getMimeType());

        if (MediaLimits::shouldStayLocalMime($mime)) {
            throw new InvalidArgumentException(__('menu.media_format_not_allowed'));
        }

        $source = static::loadImage($file->getRealPath(), $mime);
        [$width, $height] = [imagesx($source), imagesy($source)];

        [$targetWidth, $targetHeight] = static::fitWithin(
            $width,
            $height,
            $config['max_width'],
            $config['max_height'],
        );

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height,
        );

        imagedestroy($source);

        $outputMime = function_exists('imagewebp') ? 'image/webp' : 'image/jpeg';
        $extension = $outputMime === 'image/webp' ? 'webp' : 'jpg';
        $tempPath = tempnam(sys_get_temp_dir(), 'mo_media_').'.'.$extension;

        $saved = $outputMime === 'image/webp'
            ? imagewebp($canvas, $tempPath, 82)
            : imagejpeg($canvas, $tempPath, 85);

        imagedestroy($canvas);

        if (! $saved) {
            @unlink($tempPath);

            throw new RuntimeException(__('menu.media_preprocess_failed'));
        }

        $maxBytes = $config['max_kb'] * 1024;

        if (filesize($tempPath) > $maxBytes) {
            @unlink($tempPath);

            throw new InvalidArgumentException(__('menu.media_file_too_large'));
        }

        return [
            'path' => $tempPath,
            'mime' => $outputMime,
        ];
    }

    /**
     * @return \GdImage|resource
     */
    protected static function loadImage(string $path, string $mime)
    {
        $image = match (true) {
            str_contains($mime, 'png') => imagecreatefrompng($path),
            str_contains($mime, 'webp') && function_exists('imagecreatefromwebp') => imagecreatefromwebp($path),
            default => imagecreatefromjpeg($path),
        };

        if ($image === false) {
            throw new RuntimeException(__('menu.media_preprocess_failed'));
        }

        return $image;
    }

    /** @return array{0: int, 1: int} */
    protected static function fitWithin(int $width, int $height, int $maxWidth, int $maxHeight): array
    {
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return [$width, $height];
        }

        $ratio = min($maxWidth / max($width, 1), $maxHeight / max($height, 1));

        return [
            max(1, (int) round($width * $ratio)),
            max(1, (int) round($height * $ratio)),
        ];
    }
}
