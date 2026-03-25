<?php

namespace App\Mail;

use App\Models\Entretien;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EntretienReponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Entretien $entretien) {}

    public function envelope(): Envelope
    {
        $statut = $this->entretien->statut === 'confirme' ? 'Confirmé' : 'Refusé';
        return new Envelope(subject: "Votre entretien a été {$statut} — Talenteed");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.entretien-reponse');
    }
}
