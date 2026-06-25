<?php

namespace App\Support;

use Illuminate\Validation\Rules\File;

class MediaLimits
{
    public const CONTEXT_PRODUCT = 'product';

    public const CONTEXT_LOGO = 'logo';

    public const CONTEXT_SITE = 'site';

    /** @return array{max_kb: int, max_width: int, max_height: int, variant: string, mimes: list<string>} */
    public static function imageContext(string $context): array
    {
        return match ($context) {
            self::CONTEXT_LOGO => [
                'max_kb' => 512,
                'max_width' => 512,
                'max_height' => 512,
                'variant' => 'logo',
                'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
            ],
            self::CONTEXT_SITE => [
                'max_kb' => 1024,
                'max_width' => 1200,
                'max_height' => 400,
                'variant' => 'public',
                'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
            ],
            default => [
                'max_kb' => 1024,
                'max_width' => 1024,
                'max_height' => 1024,
                'variant' => 'product',
                'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
            ],
        };
    }

    public static function variantForContext(string $context): string
    {
        return static::imageContext($context)['variant'];
    }

    /** @return list<string|File> */
    public static function imageRules(string $context): array
    {
        $config = static::imageContext($context);

        return [
            'nullable',
            File::types($config['mimes'])->max($config['max_kb']),
        ];
    }

    /** @return list<string|File> */
    public static function videoRules(): array
    {
        return [
            'nullable',
            File::types(['mp4'])->max(15360),
        ];
    }

    public static function maxVideoSeconds(): int
    {
        return 30;
    }

    public static function shouldStayLocalMime(string $mime): bool
    {
        $mime = strtolower($mime);

        return str_contains($mime, 'gif')
            || str_contains($mime, 'svg')
            || str_contains($mime, 'ico')
            || str_contains($mime, 'x-icon');
    }
}
