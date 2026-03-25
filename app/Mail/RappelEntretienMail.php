<?php

namespace App\Mail;

use App\Models\Entretien;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RappelEntretienMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Entretien $entretien) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Rappel : votre entretien commence dans 1 heure — Talenteed');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.rappel-entretien');
    }
}
