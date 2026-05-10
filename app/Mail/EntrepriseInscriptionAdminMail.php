<?php

namespace App\Mail;

use App\Mail\Concerns\LocalizesMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EntrepriseInscriptionAdminMail extends Mailable
{
    use Queueable, SerializesModels, LocalizesMail;

    public function __construct(
        public readonly User $entrepriseUser,
        public readonly string $nomEntreprise,
        public readonly string $adminUrl,
        ?string $locale = null,
    ) {
        $this->useLocale($locale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('emails.entreprise_inscription_admin.subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.entreprise-inscription-admin');
    }
}
