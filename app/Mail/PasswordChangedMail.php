<?php

// app/Mail/PasswordChangedMail.php

// app/Mail/PasswordChangedMail.php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class PasswordChangedMail extends Mailable
{
    use SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Your Password has been Changed')
            ->view('emails.password_success');  // Update the view name here
    }
}
