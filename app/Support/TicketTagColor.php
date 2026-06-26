<?php

namespace App\Support;

class TicketTagColor
{
  /** @var list<string> */
    private const PALETTE = [
        '#dbeafe',
        '#fce7f3',
        '#d1fae5',
        '#ffedd5',
        '#ede9fe',
        '#cffafe',
        '#fee2e2',
        '#ecfccb',
        '#e0e7ff',
        '#fef3c7',
    ];

    public static function next(): string
    {
        $count = \App\Models\TicketTag::query()->count();

        return self::PALETTE[$count % count(self::PALETTE)];
    }

    public static function forName(string $name): string
    {
        $index = crc32(mb_strtolower(trim($name))) % count(self::PALETTE);

        return self::PALETTE[$index];
    }
}
