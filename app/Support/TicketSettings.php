<?php

namespace App\Support;

use App\Models\Setting;

class TicketSettings
{
    public const EXTENSIONS_KEY = 'ticket_file_extensions';

    public const MAX_SIZE_MB_KEY = 'ticket_max_file_size_mb';

    /** @return list<string> */
    public static function allowedExtensions(): array
    {
        $raw = (string) Setting::getFilled(self::EXTENSIONS_KEY, 'jpg,jpeg,png,doc,docx,xls,xlsx');

        return array_values(array_filter(array_map(
            fn (string $ext) => strtolower(trim($ext)),
            explode(',', $raw),
        )));
    }

    public static function maxSizeKb(): int
    {
        $mb = max(1, (int) Setting::getFilled(self::MAX_SIZE_MB_KEY, 10));

        return $mb * 1024;
    }

    /** @return list<string> */
    public static function allowedMimes(): array
    {
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pdf' => 'application/pdf',
        ];

        $mimes = [];

        foreach (self::allowedExtensions() as $ext) {
            if (isset($map[$ext])) {
                $mimes[] = $map[$ext];
            }
        }

        return array_values(array_unique($mimes));
    }

    public static function extensionRule(): string
    {
        return 'mimes:'.implode(',', self::allowedExtensions());
    }
}
