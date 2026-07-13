<?php

namespace App\Http\Validation;

use Illuminate\Http\Request;
use App\Rules\CustomValidation;

class RulesUsers
{

    /**
     * Get the validation rules that apply to the requests For AppUserController.
     *
     * @return array
     */
    public static function store()
    {
        return [
            'name' => ['required','max:25','regex:/^[a-zA-Z\s]*$/'],
            'email' => 'required|email|unique:users,email|max:99',
            'password' => 'required|min:8',
            'role_id' => 'required'
        ];
    }


    public static function update($request)
    {
        return [
            'name' => ['required','max:25','regex:/^[a-zA-Z\s]*$/'],
            'email' => 'required|unique:users,email,' . $request->id,
            'password' => 'nullable|min:8',
            'role_id' => 'required'
        ];
    }

    public static function updatePassword()
    {
        return [
            'old_password' => 'required',
            'password' => 'required_with:cpassword|same:cpassword|min:8',
            'cpassword' => 'required'
        ];
    }


}
