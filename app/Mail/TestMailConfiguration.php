<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMailConfiguration extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $sender) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('menu.mail_test_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.test-configuration',
        );
    }
}
