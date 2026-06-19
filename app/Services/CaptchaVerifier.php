<?php

namespace App\Services;

use App\Support\CaptchaPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CaptchaVerifier
{
    public static function tokenFromRequest(Request $request): ?string
    {
        $token = $request->input('cf-turnstile-response')
            ?? $request->input('g-recaptcha-response')
            ?? $request->input('captcha_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    public static function verify(?string $token, ?string $ip = null): bool
    {
        if (! CaptchaPolicy::configured()) {
            return true;
        }

        if ($token === null || $token === '') {
            return false;
        }

        return match (CaptchaPolicy::provider()) {
            'google' => static::verifyGoogle($token, $ip),
            'turnstile' => static::verifyTurnstile($token, $ip),
            default => true,
        };
    }

    protected static function verifyGoogle(string $token, ?string $ip): bool
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', array_filter([
            'secret' => CaptchaPolicy::secretKey(),
            'response' => $token,
            'remoteip' => $ip,
        ]));

        if (! $response->successful()) {
            return false;
        }

        return (bool) $response->json('success');
    }

    protected static function verifyTurnstile(string $token, ?string $ip): bool
    {
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array_filter([
            'secret' => CaptchaPolicy::secretKey(),
            'response' => $token,
            'remoteip' => $ip,
        ]));

        if (! $response->successful()) {
            return false;
        }

        return (bool) $response->json('success');
    }
}
