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

    public static function getFilled(string $key, mixed $default = null): mixed
    {
        $value = static::get($key);

        if ($value === null || trim((string) $value) === '') {
            return $default;
        }

        return $value;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        static::cache()->forget('app.settings');
    }

    public static function setIfMissing(string $key, mixed $value, string $group = 'general'): void
    {
        if (static::query()->where('key', $key)->exists()) {
            return;
        }

        static::set($key, $value, $group);
    }

    public static function setIfEmpty(string $key, mixed $value, string $group = 'general'): bool
    {
        $current = static::query()->where('key', $key)->value('value');

        if ($current !== null && trim((string) $current) !== '') {
            return false;
        }

        static::set($key, $value, $group);

        return true;
    }

    public static function setMany(array $pairs, string $group = 'general'): void
    {
        foreach ($pairs as $key => $value) {
            static::set($key, $value, $group);
        }
    }

    /** @param  array<string, string>  $defaults */
    public static function mergedGroup(string $group, array $defaults): array
    {
        $stored = static::query()
            ->where('group', $group)
            ->pluck('value', 'key')
            ->all();

        return array_merge($defaults, $stored);
    }

    public static function boolean(string $key, bool $default = false): bool
    {
        $value = static::get($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
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
