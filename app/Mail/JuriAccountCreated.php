<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JuriAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $rawPassword;
    public $loginUrl;

    
    public function __construct($user, $rawPassword, $loginUrl)
    {
        $this->user = $user;
        $this->rawPassword = $rawPassword;
        $this->loginUrl = $loginUrl;
    }

   
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akses Panel Juri PENA 2026',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.juri_account',
            with: [
                'user' => $this->user,
                'rawPassword' => $this->rawPassword,
                'loginUrl' => $this->loginUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
