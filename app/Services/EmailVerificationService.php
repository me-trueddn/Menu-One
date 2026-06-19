<?php

namespace App\Services;

use App\Mail\VerifyEmailMail;
use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Support\EmailTemplatePolicy;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationService
{
    public function issueAndSend(User $user): EmailVerificationToken
    {
        EmailVerificationToken::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $token = EmailVerificationToken::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes(EmailTemplatePolicy::verificationExpiresMinutes()),
        ]);

        MailConfigService::runWithTimeout(function () use ($user, $token) {
            Mail::to($user->email)->send(new VerifyEmailMail($user, $token));
        });

        return $token;
    }

    public function verify(string $plainToken): ?User
    {
        $record = EmailVerificationToken::query()
            ->where('token', $plainToken)
            ->first();

        if (! $record || ! $record->isUsable()) {
            return null;
        }

        $record->update(['used_at' => now()]);

        $user = $record->user;
        $user->update(['email_verified_at' => now()]);

        return $user;
    }
}
