<?php

namespace App\Http\Validation;

use Illuminate\Http\Request;

class RulesStatus
{
    public static function get()
    {
        return [
            'module' => 'required',
        ];
    }
}
