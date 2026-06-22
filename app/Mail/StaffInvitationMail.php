<?php

namespace App\Mail;

use App\Models\TenantStaffInvitation;
use App\Support\EmailTemplatePolicy;
use App\Support\EmailTemplateRenderer;
use App\Support\EmailTemplateVariables;
use App\Support\SiteConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TenantStaffInvitation $invitation) {}

    public function envelope(): Envelope
    {
        $invitation = $this->invitation->loadMissing(['tenant', 'user', 'invitedBy']);
        $variables = $this->variables($invitation);

        $subject = trim(strip_tags(EmailTemplateRenderer::render(
            EmailTemplatePolicy::staffInvitationSubject(),
            $variables,
        )));

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $invitation = $this->invitation->loadMissing(['tenant', 'user', 'invitedBy']);

        $body = EmailTemplateRenderer::render(
            EmailTemplatePolicy::staffInvitationBody(),
            $this->variables($invitation),
        );

        return new Content(htmlString: $body);
    }

    /** @return array<string, string> */
    protected function variables(TenantStaffInvitation $invitation): array
    {
        return array_merge(EmailTemplateVariables::base(), [
            'name' => $invitation->user->name,
            'email' => $invitation->user->email,
            'invite_url' => route('staff.invitation.show', ['token' => $invitation->token]),
            'cafe_name' => $invitation->tenant->name,
            'role' => __('menu.role_'.$invitation->role),
            'invited_by' => $invitation->invitedBy?->name ?? SiteConfig::name(),
            'expires_minutes' => (string) EmailTemplatePolicy::staffInvitationExpiresMinutes(),
        ]);
    }
}
