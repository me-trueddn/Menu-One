<?php

namespace App\Support;

class SecretMask
{
    public static function mask(?string $value, int $visible = 4): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $length = strlen($value);

        if ($length <= $visible * 2) {
            return str_repeat('•', $length);
        }

        $hidden = min(8, $length - ($visible * 2));

        return substr($value, 0, $visible)
            .str_repeat('•', $hidden)
            .substr($value, -$visible);
    }
}
