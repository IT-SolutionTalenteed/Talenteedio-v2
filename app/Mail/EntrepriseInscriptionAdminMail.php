<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EntrepriseInscriptionAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $entrepriseUser,
        public readonly string $nomEntreprise,
        public readonly string $adminUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[Talenteed Admin] Nouvelle inscription entreprise en attente');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.entreprise-inscription-admin');
    }
}
