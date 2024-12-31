<?php

namespace App\Mail;

use App\Models\MobileUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(MobileUser $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->view('emails.verify')
            ->with([
                'verificationUrl' => url("/verify-email/{$this->user->email_verification_token}")
            ]);
    }
}
