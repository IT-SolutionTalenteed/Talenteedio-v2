<?php

namespace App\Mail;

use App\Models\Entretien;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EntretienReserveMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Entretien $entretien,
        public string $destinataire // 'talent' | 'entreprise' | 'admin'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Nouvelle réservation d\'entretien — Talenteed');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.entretien-reserve');
    }
}
