<?php

namespace App\Services;

use App\Models\User;
use App\Support\SiteConfig;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorService
{
    private const SESSION_USER_KEY = 'two_factor.setup_user_id';

    private const SESSION_SECRET_KEY = 'two_factor.setup_secret';

    public function engine(): Google2FA
    {
        return new Google2FA;
    }

    public function hasPendingSetup(User $user): bool
    {
        return $this->pendingSecretFor($user) !== null;
    }

    public function pendingSecretFor(User $user): ?string
    {
        if ((int) Session::get(self::SESSION_USER_KEY) !== (int) $user->id) {
            return null;
        }

        $encrypted = Session::get(self::SESSION_SECRET_KEY);

        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array{secret: string, qr_inline: string} */
    public function beginSetup(User $user): array
    {
        $secret = $this->engine()->generateSecretKey();

        Session::put([
            self::SESSION_USER_KEY => $user->id,
            self::SESSION_SECRET_KEY => Crypt::encryptString($secret),
        ]);

        return [
            'secret' => $secret,
            'qr_inline' => $this->qrCodeInline($user, $secret),
        ];
    }

    public function confirmSetup(User $user, string $code): bool
    {
        $secret = $this->pendingSecretFor($user);

        if ($secret === null || ! $this->verifyCode($secret, $code)) {
            return false;
        }

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_enabled' => true,
        ])->save();

        $this->clearPendingSetup();

        return true;
    }

    public function disable(User $user, ?string $code = null): bool
    {
        if ($user->hasTwoFactorConfigured()) {
            if ($code === null || ! $this->verifyUserCode($user, $code)) {
                return false;
            }
        }

        $this->resetCredentials($user);

        return true;
    }

    public function adminDisable(User $user): void
    {
        $this->resetCredentials($user);
    }

    public function adminReset(User $user): void
    {
        $this->resetCredentials($user);
        $this->clearPendingSetupForUser($user);
    }

    public function verifyUserCode(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        return $this->verifyCode($user->two_factor_secret, $code);
    }

    public function verifyCode(string $secret, string $code): bool
    {
        $normalized = preg_replace('/\s+/', '', $code) ?? '';

        if (! preg_match('/^\d{6}$/', $normalized)) {
            return false;
        }

        return $this->engine()->verifyKey($secret, $normalized);
    }

    public function clearPendingSetup(): void
    {
        Session::forget([self::SESSION_USER_KEY, self::SESSION_SECRET_KEY]);
    }

    public function qrCodeInline(User $user, string $secret): string
    {
        return $this->engine()->getQRCodeInline(
            SiteConfig::name(),
            $user->email,
            $secret,
        );
    }

    private function resetCredentials(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_enabled' => false,
        ])->save();

        $this->clearPendingSetup();
    }

    private function clearPendingSetupForUser(User $user): void
    {
        if ((int) Session::get(self::SESSION_USER_KEY) === (int) $user->id) {
            $this->clearPendingSetup();
        }
    }
}
