<?php

namespace App\Http\Validation;

use Illuminate\Http\Request;
use App\Rules\CustomValidation;

class RulesCustomer
{

    /**
     * Get the validation rules that apply to the requests For AppUserController.
     *
     * @return array
     */
    public static function store()
    {
        return [
            'type' => 'required',
            'name' => 'required',
        ];
    }

    public static function update($request)
    {
        return [
            'id' => 'required|exists:customers,id'
        ];
    }

    public static function delete(){
        return [
            'id' => 'required|exists:customers,id'
        ];
    }

}

