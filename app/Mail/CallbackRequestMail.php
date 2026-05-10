<?php

namespace App\Mail;

use App\Mail\Concerns\LocalizesMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CallbackRequestMail extends Mailable
{
    use Queueable, SerializesModels, LocalizesMail;

    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $userMessage = '',
        ?string $locale = null
    ) {
        $this->useLocale($locale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('emails.callback_request.subject') . ' — ' . $this->name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.callback-request');
    }
}
