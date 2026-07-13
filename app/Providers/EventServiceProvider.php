<?php

namespace App\Providers;

use App\Events\UserSignUp;
use App\Events\ForgotPassword;
use App\Events\CreateUser;
use App\Listeners\NotifyCreateUser;
use App\Listeners\NotifyUserSignUp;
use App\Listeners\NotifyForgotPassword;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */

    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserSignUp::class => [
            NotifyUserSignUp::class,
        ],
        ForgotPassword::class => [
            NotifyForgotPassword::class,
        ],
        CreateUser::class => [
            NotifyCreateUser::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
