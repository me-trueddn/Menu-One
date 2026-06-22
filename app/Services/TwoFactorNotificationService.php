<?php

namespace App\Services;

use App\Mail\TwoFactorStatusMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class TwoFactorNotificationService
{
    public function notifyStatusChange(User $user, bool $enabled): void
    {
        MailConfigService::runWithTimeout(function () use ($user, $enabled) {
            Mail::to($user->email)->send(new TwoFactorStatusMail($user, $enabled));
        });
    }
}
