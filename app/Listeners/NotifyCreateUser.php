<?php

namespace App\Listeners;

use App\Repositories\Mail\Email;

class NotifyCreateUser
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
        $password = $event->user->email_password;

        $email_data = ['temp' => 'create_user', 'to' => $event->user->email,
            'subject' => 'Welcome to Sijily',
            'data' => ['name' => $event->user->full_name,'email' => $event->user->email, 'password' => $password]];

        $this->email_repository->sendEmail($email_data);
        unset($event->user->email_password);
        $event->user->save();
    }
}
