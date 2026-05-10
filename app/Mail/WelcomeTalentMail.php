<?php

namespace App\Mail;

use App\Mail\Concerns\LocalizesMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeTalentMail extends Mailable
{
    use Queueable, SerializesModels, LocalizesMail;

    public function __construct(public User $user)
    {
        $this->useUserLocale($user);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('emails.welcome_talent.subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.welcome-talent');
    }
}
