<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::cache()->rememberForever('app.settings', function () {
            return static::query()->pluck('value', 'key')->all();
        });

        return $settings[$key] ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        static::cache()->forget('app.settings');
    }

    public static function setMany(array $pairs, string $group = 'general'): void
    {
        foreach ($pairs as $key => $value) {
            static::set($key, $value, $group);
        }
    }

    public static function flushCache(): void
    {
        static::cache()->forget('app.settings');
    }

    protected static function cache(): \Illuminate\Contracts\Cache\Repository
    {
        return Cache::store('central');
    }
}
