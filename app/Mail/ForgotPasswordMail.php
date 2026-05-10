<?php

namespace App\Mail;

use App\Mail\Concerns\LocalizesMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels, LocalizesMail;

    public function __construct(
        public readonly User $user,
        public readonly string $resetUrl,
    ) {
        $this->useUserLocale($user);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('emails.forgot_password.subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.forgot-password');
    }
}
