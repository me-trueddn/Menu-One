<?php

namespace App\Notifications;

use App\Support\EmailTemplatePolicy;
use App\Support\EmailTemplateRenderer;
use App\Support\EmailTemplateVariables;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $variables = array_merge(EmailTemplateVariables::base(), [
            'name' => $notifiable->name,
            'email' => $notifiable->getEmailForPasswordReset(),
            'reset_url' => $resetUrl,
            'expires_minutes' => (string) EmailTemplatePolicy::passwordResetExpiresMinutes(),
        ]);

        $subject = trim(strip_tags(EmailTemplateRenderer::render(
            EmailTemplatePolicy::passwordResetSubject(),
            $variables,
        )));

        $body = EmailTemplateRenderer::render(
            EmailTemplatePolicy::passwordResetBody(),
            $variables,
        );

        return (new MailMessage)
            ->subject($subject)
            ->view('mail.html-string', ['content' => $body]);
    }
}
