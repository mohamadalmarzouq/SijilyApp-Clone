<?php

namespace App\Http\Validation;


class RulesBank
{
    public static function store()
    {
        return [
            'date_from' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d',
            'opening_balance' => 'required',//|regex:/^\d+(\.\d{1,2})?$/
            'actual_balance' => 'required',//|regex:/^\d+(\.\d{1,2})?$/
            'cash_in' => 'required',//|regex:/^\d+(\.\d{1,2})?$/
            'cash_out' => 'required',//|regex:/^\d+(\.\d{1,2})?$/
            'ending_balance' => 'required',//|regex:/^\d+(\.\d{1,2})?$/
            'variance' => 'required' //|regex:/^\d+(\.\d{1,2})?$/
        ];
    }

    public static function update()
    {
        return [
            'id' => 'required|exists:bank_reconciles,id',
            // 'date_from' => 'required|date_format:Y-m-d',
            // 'date_to' => 'required|date_format:Y-m-d',
            // 'opening_balance' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            // 'actual_balance' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            // 'cash_in' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            // 'cash_out' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            // 'ending_balance' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            // 'variance' => 'required|regex:/^\d+(\.\d{1,2})?$/'
        ];
    }

    public static function delete()
    {
        return [
            'id' => 'required|exists:bank_reconciles,id',
        ];
    }
}
