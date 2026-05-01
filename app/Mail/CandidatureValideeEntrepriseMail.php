<?php

namespace App\Mail;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidatureValideeEntrepriseMail extends Mailable
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
        
        // Définir la locale pour cet email (français par défaut)
        app()->setLocale('fr');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = __('emails.validated_company.badge') . ' - ' . $this->candidature->offre->titre;
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.candidature-validee-entreprise',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        // Attacher le CV si disponible
        if ($this->candidature->cv) {
            $cvPath = storage_path('app/public/' . $this->candidature->cv);
            if (file_exists($cvPath)) {
                $attachments[] = Attachment::fromPath($cvPath)
                    ->as('CV_' . $this->candidature->talent->name . '.pdf')
                    ->withMime('application/pdf');
            }
        }
        
        return $attachments;
    }
}
