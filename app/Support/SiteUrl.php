<?php

namespace App\Support;

class SiteUrl
{
    /** @var list<string> */
    private const LOCAL_URLS = [
        'http://127.0.0.1:8000',
        'http://127.0.0.1',
        'http://localhost:8000',
        'http://localhost',
    ];

    public static function normalize(mixed $url): ?string
    {
        $url = rtrim(trim((string) $url), '/');

        if ($url === '') {
            return null;
        }

        if (in_array($url, self::LOCAL_URLS, true)) {
            return null;
        }

        if (app()->environment('production') && str_starts_with($url, 'http://')) {
            $host = parse_url($url, PHP_URL_HOST);

            if (is_string($host) && $host !== '' && $host !== 'localhost' && $host !== '127.0.0.1') {
                $port = parse_url($url, PHP_URL_PORT);

                return 'https://'.$host.($port ? ':'.$port : '');
            }
        }

        return $url;
    }

    public static function firstUsable(string ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $normalized = static::normalize($candidate);

            if ($normalized !== null) {
                return $normalized;
            }
        }

        if (! app()->runningInConsole() && request()->hasHeader('Host')) {
            return static::normalize(request()->root());
        }

        return null;
    }
}
