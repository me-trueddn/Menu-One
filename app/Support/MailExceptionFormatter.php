<?php

namespace App\Support;

use Throwable;

class MailExceptionFormatter
{
    public static function toUserMessage(Throwable $exception): string
    {
        $message = $exception->getMessage();

        if (static::isAuthenticationFailure($message)) {
            return __('menu.mail_auth_failed');
        }

        if (static::isTimeout($message)) {
            return __('menu.mail_timeout_failed');
        }

        if (static::isConnectionFailure($message)) {
            return __('menu.mail_connection_failed');
        }

        return __('menu.mail_send_failed', [
            'message' => static::shorten($message),
        ]);
    }

    protected static function isAuthenticationFailure(string $message): bool
    {
        return str_contains($message, '535')
            || str_contains($message, 'authentication failed')
            || str_contains($message, 'Invalid user or password');
    }

    protected static function isTimeout(string $message): bool
    {
        return str_contains(strtolower($message), 'timeout')
            || str_contains(strtolower($message), 'timed out')
            || str_contains($message, 'Maximum execution time');
    }

    protected static function isConnectionFailure(string $message): bool
    {
        return str_contains($message, 'Connection could not be established')
            || str_contains($message, 'Connection refused')
            || str_contains($message, 'getaddrinfo');
    }

    protected static function shorten(string $message): string
    {
        $message = preg_replace('/\s+/', ' ', trim($message)) ?? $message;

        return mb_strlen($message) > 180 ? mb_substr($message, 0, 177).'...' : $message;
    }
}
