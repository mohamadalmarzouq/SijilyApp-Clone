<?php

namespace App\Http\Validation;


class RulesOwnerAccount
{
    public static function store()
    {
        return [
            'amount' => 'required',
            'date' => 'required|date',
            'owner_name' => 'required',
            'status_id' => 'required',
        ];
    }

    public static function import()
    {
        return [
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ];
    }

    public static function update()
    {
        return [
            'id' => 'required|exists:owner_accounts,id',
        ];
    }
    public static function delete()
    {
        return [
            'id' => 'required|exists:owner_accounts,id',
        ];
    }
}
