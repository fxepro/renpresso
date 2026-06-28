<?php

namespace App\Mail;

use App\Models\Lease;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaseThreadMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Lease $lease,
        public Message $message,
        public User $sender,
    ) {}

    public function envelope(): Envelope
    {
        $property = $this->lease->property;

        return new Envelope(
            subject: 'Renpresso: new message — '.$property->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.lease-thread-message',
            text: 'emails.lease-thread-message-text',
        );
    }
}
