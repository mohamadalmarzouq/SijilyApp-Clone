<?php

namespace App\Listeners;

use App\Repositories\Mail\Email;

class NotifyForgotPassword
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->email_repository = new Email();
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        $code = rand(1000, 9999);

        $email_data = ['temp' => 'forgot_password', 'to' => $event->user->email,
            'subject' => 'Forgot Password',
            'data' => ['user' => $event->user->full_name, 'code' => $code]];

        $this->email_repository->sendEmail($email_data);

        $event->user->reset_token = $code;

        $event->user->save();
    }
}
