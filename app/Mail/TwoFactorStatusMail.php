<?php

namespace App\Mail;

use App\Models\User;
use App\Support\EmailTemplatePolicy;
use App\Support\EmailTemplateRenderer;
use App\Support\EmailTemplateVariables;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public bool $enabled,
    ) {}

    public function envelope(): Envelope
    {
        $subject = trim(strip_tags(EmailTemplateRenderer::render(
            $this->enabled
                ? EmailTemplatePolicy::twoFactorEnabledSubject()
                : EmailTemplatePolicy::twoFactorDisabledSubject(),
            $this->variables(),
        )));

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $body = EmailTemplateRenderer::render(
            $this->enabled
                ? EmailTemplatePolicy::twoFactorEnabledBody()
                : EmailTemplatePolicy::twoFactorDisabledBody(),
            $this->variables(),
        );

        return new Content(htmlString: $body);
    }

    /** @return array<string, string> */
    protected function variables(): array
    {
        return array_merge(EmailTemplateVariables::base(), [
            'name' => $this->user->name,
            'email' => $this->user->email,
        ]);
    }
}
