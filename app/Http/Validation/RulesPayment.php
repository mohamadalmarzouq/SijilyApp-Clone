<?php

namespace App\Http\Validation;


class RulesPayment
{
    public static function store()
    {
        return [
            'token' => 'required',
            'subscription_id' => 'required|exists:subscriptions,id',
            'amount' => 'required',
            'currency' => 'required',
            'user_id' => 'required',
            'is_recur'=>'required',
            'country_code'=>'required',
            'phone'=>'required',
        ];
    }

    public static function listing()
    {
        return [
            'customer_id' => 'required',
        ];
    }

    public static function save()
    {
        return [
            'source' => 'required',
            'customer_id' => 'required',
        ];
    }
    public static function default()
    {
        return [
            'card_id' => 'required',
        ];
    }

    public static function delete()
    {
        return [
            'customer_id' => 'required',
            'card_id' => 'required',
        ];
    }
}
