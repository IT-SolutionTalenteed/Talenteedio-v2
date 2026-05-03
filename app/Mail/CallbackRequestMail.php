<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CallbackRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $message = ''
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[Talenteed] Demande de rappel — ' . $this->name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.callback-request');
    }
}
