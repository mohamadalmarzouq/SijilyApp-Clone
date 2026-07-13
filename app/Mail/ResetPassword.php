<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $user;
    // public $token;
    public $code;

    public function __construct(User $user, $token)
    {
        // $this->token = Str::random(16);
        $this->user = $user['name'];
        // $id = Crypt::encryptString($this->user->id);
        $this->code = url("reset_password", $token);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(
            config('mail.from.address'),
            config('mail.from.name')
        )
            ->view('emails.forgot_password', ['code' => $this->code, 'user' => $this->user]);
    }
}
