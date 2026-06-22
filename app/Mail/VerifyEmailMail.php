<?php

namespace App\Mail;

use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Support\EmailTemplatePolicy;
use App\Support\EmailTemplateRenderer;
use App\Support\EmailTemplateVariables;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public EmailVerificationToken $token,
    ) {}

    public function envelope(): Envelope
    {
        $subject = EmailTemplateRenderer::render(EmailTemplatePolicy::verificationSubject(), array_merge(EmailTemplateVariables::base(), [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'verify_url' => route('verification.custom', ['token' => $this->token->token]),
            'expires_minutes' => (string) EmailTemplatePolicy::verificationExpiresMinutes(),
        ]));

        // Subject must be plain text — strip any HTML/BBCode artifacts.
        $subject = trim(strip_tags($subject));

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $verifyUrl = route('verification.custom', ['token' => $this->token->token]);

        $body = EmailTemplateRenderer::render(EmailTemplatePolicy::verificationBody(), array_merge(EmailTemplateVariables::base(), [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'verify_url' => $verifyUrl,
            'expires_minutes' => (string) EmailTemplatePolicy::verificationExpiresMinutes(),
        ]));

        return new Content(
            htmlString: $body,
        );
    }
}
