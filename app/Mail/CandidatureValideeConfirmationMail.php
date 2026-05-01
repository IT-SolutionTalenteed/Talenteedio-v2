<?php

namespace App\Mail;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidatureValideeConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $candidature;
    public $matchingScore;

    /**
     * Create a new message instance.
     */
    public function __construct(Candidature $candidature, int $matchingScore)
    {
        $this->candidature = $candidature;
        $this->matchingScore = $matchingScore;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Candidature envoyée avec succès - ' . $this->candidature->offre->titre,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.candidature-validee-confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
