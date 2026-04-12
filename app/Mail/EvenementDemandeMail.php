<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EvenementDemandeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nomEntreprise,
        public string $nomEvenement,
        public ?string $messageEntreprise
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Demande de participation — ' . $this->nomEvenement,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.evenement-demande',
        );
    }
}
