<?php

namespace App\Mail;

use App\Models\WaitlistEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WaitlistEmail $entry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' — you\'re on the waitlist',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.waitlist-confirmation',
            text: 'emails.waitlist-confirmation-text',
        );
    }
}
