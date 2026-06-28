<?php

namespace App\Mail;

use App\Models\Lease;
use App\Models\Message;
use App\Models\Property;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PropertyBroadcastMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Property $property,
        public Message $message,
        public User $sender,
        public ?Lease $lease = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Renpresso: building notice — '.$this->property->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.property-broadcast',
            text: 'emails.property-broadcast-text',
        );
    }
}
