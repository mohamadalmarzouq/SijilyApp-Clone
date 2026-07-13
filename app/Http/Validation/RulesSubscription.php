<?php

namespace App\Http\Validation;

use Illuminate\Http\Request;
use App\Rules\CustomValidation;

class RulesSubscription
{
    /**
     * Get the validation rules that apply to the requests For AppUserController.
     *
     * @return array
     */
    public static function addSubscription()
    {
        return [
            'subscription' => 'required|max:50|unique:subscriptions,subscription,NULL,id,deleted_at,NULL',
            'subscription_ar' => 'required|max:300',
            'per_user_amount' => 'required|numeric',
            'amount' => 'required|numeric',
            'title' => 'required|max:60|regex:/^[a-zA-Z ]*$/',
            'title_ar' => 'required|max:300',
            'content' => 'max:200',
            'image' => 'required|mimes:png,jpeg,jpg'
        ];
    }

    public static function update($request)
    {
        return [
            'subscription' => 'required|max:50|unique:subscriptions,subscription,' . $request->id.',id,deleted_at,NULL',
            'subscription_ar' => 'required|max:300',
            'per_user_amount' => 'required|numeric',
            'amount' => 'required|numeric',
            'title' => 'required|max:60|regex:/^[a-zA-Z ]*$/',
            'title_ar' => 'required|max:300',
            'content' => 'max:200',
            'image' => 'mimes:png,jpeg,jpg'
        ];
    }
}

