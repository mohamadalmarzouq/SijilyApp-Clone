<?php

namespace App\Http\Validation;

use Illuminate\Http\Request;
use App\Rules\CustomValidation;

class RulesUserRole
{

    /**
     * Get the validation rules that apply to the requests For AppUserController.
     *
     * @return array
     */
    public static function store()
    {
        return [
            'name' => 'required|unique:user_roles,name,NULL,id,deleted_at,NULL|max:25',
        ];

    }

    public static function update($request)
    {
        return [
             'name' => 'required|max:25|unique:user_roles,name,' . $request->id,
        ];

    }

}

