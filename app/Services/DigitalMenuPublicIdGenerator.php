<?php

namespace App\Services;

use App\Models\DigitalMenu;

class DigitalMenuPublicIdGenerator
{
    public static function generate(): string
    {
        do {
            $id = (string) random_int(100000, 999999);
        } while (DigitalMenu::query()->where('public_id', $id)->exists());

        return $id;
    }
}
