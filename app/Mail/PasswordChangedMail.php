<?php

namespace App\Mail;

use App\Mail\Concerns\LocalizesMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordChangedMail extends Mailable
{
    use Queueable, SerializesModels, LocalizesMail;

    public function __construct(public readonly User $user)
    {
        $this->useUserLocale($user);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('emails.password_changed.subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.password-changed');
    }
}
