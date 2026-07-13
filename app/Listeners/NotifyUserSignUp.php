<?php

namespace App\Listeners;

use App\Repositories\Mail\Email;

class NotifyUserSignUp
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

        $email_data = ['temp' => 'user_registration', 'to' => $event->user->email,
            'subject' => 'Please Verify Your Email',
            'data' => ['user' => $event->user->full_name, 'code' => $code]];

        $this->email_repository->sendEmail($email_data);

        $event->user->reset_token = $code;

        $event->user->save();
    }
}
