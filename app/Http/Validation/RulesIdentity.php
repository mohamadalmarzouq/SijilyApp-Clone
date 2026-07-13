<?php

namespace App\Http\Validation;

use Illuminate\Http\Request;

class RulesIdentity
{
    public static function get()
    {
        return [
            'id' => 'required',
        ];
    }
}
