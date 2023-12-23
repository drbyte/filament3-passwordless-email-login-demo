<?php

namespace App\Filament\EmailAuth\Mail;

use App\Filament\EmailAuth\MagicLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MagicLinkEmail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $email, public MagicLink $magicLink, public string $tryAgainUrl)
    {
        $settings = app(GeneralSettings::class);
        $this->subject = 'Login access to Website';
    }

    public function envelope(): Envelope
    {
        return new Envelope();
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'auth.magic-link.email-template',
            with: [
                'email' => $this->email,
                'signedUrl' => $this->magicLink->getSignedUrl(),
                'expiry' => $this->magicLink->getExpiry(),
                'urlToTryAgain' => $this->tryAgainUrl,
            ],
        );
    }
}
