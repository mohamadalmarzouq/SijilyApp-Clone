<?php

namespace App\Http\Validation;


class RulesPurchase
{
    public static function store()
    {
        return [
            'asset_name' => 'required',
            //'desc' => 'required',
            'amount' => 'required',
            'date' => 'required',
            'status_id' => 'required',
        ];
    }

    public static function update()
    {
        return [
            'id' => 'required|exists:purchases,id',
        ];
    }
    public static function delete()
    {
        return [
            'id' => 'required|exists:purchases,id',
        ];
    }
    public static function get()
    {
        return [
            'id' => 'required|exists:purchases,id',
        ];
    }
}
