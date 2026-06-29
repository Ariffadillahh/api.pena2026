<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StaffAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $password;
    public $role;

    public function __construct($email, $password, $role)
    {
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function build()
    {
        return $this->subject('Kredensial Akun Kepanitiaan PENA 2026')
            ->view('emails.staff_account');
    }
}
