<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The one message the contact form produces. It goes to the site owner and
 * nothing is persisted — the inbox is the storage, exactly as the page says.
 */
class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{name: string, email: string, topic: string, timeline: string, message: string, locale: string}  $fields
     */
    public function __construct(public array $fields) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '['.$this->fields['topic'].'] '.$this->fields['name'],

            /* Sent from the app's own address so SPF/DKIM hold; the sender's
               address only ever lands in Reply-To, which is all a reply needs. */
            replyTo: [new Address($this->fields['email'], $this->fields['name'])],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-message',
        );
    }
}
