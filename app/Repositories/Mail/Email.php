<?php

namespace App\Repositories\Mail;

use Illuminate\Support\Facades\Mail;

class Email
{
    public function sendEmail($email_data)
    {
        $template = 'emails.' . $email_data['temp'];

        Mail::send($template, $email_data['data'], function ($message) use ($email_data) {
            $message->to($email_data['to'])->subject($email_data['subject']);
            $message->from(env('MAIL_FROM_NAME','noreply@cubestagearea.xyz'),'Sijily');
        });
    }
}
