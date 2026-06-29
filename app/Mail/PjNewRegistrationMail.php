<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PjNewRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $team;
    public $competition;

    public function __construct($team, $competition)
    {
        $this->team = $team;
        $this->competition = $competition;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftar Baru Lomba - ' . $this->competition->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pj_new_registration',
        );
    }
}
