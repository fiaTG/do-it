<?php

namespace App\Mail;

use App\Models\Invite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invite $invite)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Einladung zur Familie '.$this->invite->family->name,
        );
    }

    public function content(): Content
    {
        $base = rtrim((string) config('app.frontend_url'), '/');

        return new Content(
            view: 'mail.invitation',
            with: [
                'familyName' => $this->invite->family->name,
                'url' => $base.'/register?token='.$this->invite->token,
            ],
        );
    }
}
