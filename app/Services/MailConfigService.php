<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\MailExceptionFormatter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class MailConfigService
{
    public static function timeoutSeconds(): int
    {
        return max(5, (int) Setting::get('mail_timeout_seconds', 15));
    }

    /** @param array<string, mixed> $data */
    public static function apply(array $data): void
    {
        $mailer = $data['mail_mailer'] ?? Setting::get('mail_mailer', config('mail.default'));

        Config::set('mail.default', $mailer);

        if ($mailer === 'smtp') {
            $host = (string) ($data['mail_host'] ?? Setting::get('mail_host', ''));
            $port = (int) ($data['mail_port'] ?? Setting::get('mail_port', 587));
            $fromAddress = (string) ($data['mail_from_address'] ?? Setting::get('mail_from_address', config('mail.from.address')));
            $username = (string) ($data['mail_username'] ?? Setting::get('mail_username', ''));

            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', $port);
            Config::set('mail.mailers.smtp.username', static::resolveSmtpUsername($username, $fromAddress, $host));
            Config::set('mail.mailers.smtp.timeout', static::timeoutSeconds());

            $password = $data['mail_password'] ?? null;

            if (! empty($password)) {
                Config::set('mail.mailers.smtp.password', $password);
            } else {
                $encrypted = Setting::get('mail_password');
                if ($encrypted) {
                    try {
                        Config::set('mail.mailers.smtp.password', Crypt::decryptString($encrypted));
                    } catch (\Throwable) {
                        //
                    }
                }
            }

            $encryption = $data['mail_encryption'] ?? Setting::get('mail_encryption', 'tls');
            if ($encryption === 'none') {
                $encryption = '';
            }
            Config::set('mail.mailers.smtp.encryption', $encryption ?: null);
            Config::set('mail.mailers.smtp.scheme', static::resolveSmtpScheme($port, (string) $encryption));
        }

        $fromAddress = $data['mail_from_address'] ?? Setting::get('mail_from_address', config('mail.from.address'));
        $fromName = $data['mail_from_name'] ?? Setting::get('mail_from_name', config('mail.from.name'));

        if ($fromAddress) {
            Config::set('mail.from.address', $fromAddress);
        }

        if ($fromName) {
            Config::set('mail.from.name', $fromName);
        }
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @param  array<string, mixed>  $data
     * @return TReturn
     */
    public static function runWithTimeout(callable $callback, array $data = [])
    {
        static::apply($data);

        $timeout = static::timeoutSeconds();
        $previousLimit = ini_get('max_execution_time');
        set_time_limit($timeout + 10);

        try {
            return $callback();
        } catch (\Throwable $exception) {
            throw new \RuntimeException(
                MailExceptionFormatter::toUserMessage($exception),
                (int) $exception->getCode(),
                $exception
            );
        } finally {
            if ($previousLimit !== false) {
                set_time_limit((int) $previousLimit);
            }
        }
    }

    protected static function resolveSmtpUsername(string $username, string $fromAddress, string $host): string
    {
        $username = trim($username);

        if ($username === '' || str_contains($username, '@')) {
            return $username;
        }

        if ($fromAddress !== '' && str_starts_with($fromAddress, $username.'@')) {
            return $fromAddress;
        }

        if (str_contains(strtolower($host), 'yandex') && $fromAddress !== '' && str_contains($fromAddress, '@')) {
            return $fromAddress;
        }

        return $username;
    }

    protected static function resolveSmtpScheme(int $port, string $encryption): string
    {
        if ($encryption === 'ssl' || $port === 465) {
            return 'smtps';
        }

        return 'smtp';
    }
}
