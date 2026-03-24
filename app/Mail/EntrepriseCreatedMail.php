<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EntrepriseCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nomEntreprise,
        public string $email,
        public string $password
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vos identifiants Talenteed — ' . $this->nomEntreprise,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.entreprise-created',
        );
    }
}
