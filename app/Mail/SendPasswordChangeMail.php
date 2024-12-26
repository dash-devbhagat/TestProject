<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPasswordChangeMail extends Mailable
{
    use SerializesModels;

    public $user;
    public $token;

    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function build()
    {
        // Generate the password reset URL
        $url = url('change-password/' . $this->token);

        // Pass the variables to the view
        return $this->subject('Password Change Request')
            ->view('emails.password_change')
            ->with([
                'name' => $this->user->name,
                'url' => $url
            ]);
    }
}
