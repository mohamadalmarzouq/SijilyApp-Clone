<?php

namespace App\Http\Validation;


class RulesSales
{
    public static function store()
    {
        return [
            'date' => 'required|date',
            'sys_gen_id' => 'required',
            'sub_category_id' => 'required',//|exists:sub_categories,id
            'desc' => 'max:255',
            'amount' => 'required|numeric',
            'status_id' => 'required|exists:statuses,id',
        ];
    }

    public static function update()
    {
        return [
            'id' => 'required|exists:sales,id',
            //'sub_category_id' => 'required|exists:sub_categories,id',
            'date' => 'date',
            'desc' => 'max:255',
            'amount' => 'numeric',
            'status_id' => 'exists:statuses,id',
        ];
    }

    public static function deleteSale(){
        return [
            'id' => 'required|exists:sales,id',
        ];
    }
}
