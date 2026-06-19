<?php

namespace App\Services;

use App\Models\User;

class UserPublicIdGenerator
{
    public static function generate(): string
    {
        do {
            $id = (string) random_int(100000, 999999);
        } while (User::query()->where('public_id', $id)->exists());

        return $id;
    }
}
