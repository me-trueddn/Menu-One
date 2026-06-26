<?php

namespace App\Support;

use Illuminate\Http\Request;

class ClientIp
{
    /** @var list<string> */
    private const CLIENT_IP_HEADERS = [
        'CF-Connecting-IP',
        'True-Client-IP',
        'X-Real-IP',
        'X-Forwarded-For',
    ];

    public static function resolve(?Request $request = null): ?string
    {
        $request ??= request();

        if ($request === null) {
            return null;
        }

        $resolved = $request->ip();

        if ($resolved !== null && ! self::isLoopback($resolved)) {
            return $resolved;
        }

        if (! self::isBehindTrustedProxy($request)) {
            return $resolved;
        }

        foreach (self::CLIENT_IP_HEADERS as $header) {
            $candidate = self::parseHeaderValue($request->header($header));

            if ($candidate !== null && ! self::isLoopback($candidate)) {
                return $candidate;
            }
        }

        return $resolved;
    }

    private static function isBehindTrustedProxy(Request $request): bool
    {
        $remote = (string) $request->server->get('REMOTE_ADDR', '');

        if ($remote === '') {
            return false;
        }

        if (self::isLoopback($remote)) {
            return true;
        }

        return filter_var($remote, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    private static function parseHeaderValue(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        foreach (array_map('trim', explode(',', $value)) as $part) {
            if (filter_var($part, FILTER_VALIDATE_IP)) {
                return $part;
            }
        }

        return null;
    }

    private static function isLoopback(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1', '0:0:0:0:0:0:0:1'], true);
    }
}
