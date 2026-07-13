<?php

namespace App\Events;

use App\Models\AppUser;
use Illuminate\Foundation\Events\Dispatchable;

class UserSignUp
{
    use Dispatchable;

    public $user;

    public function __construct(AppUser $user)
    {
        $this->user = $user;
    }
}
