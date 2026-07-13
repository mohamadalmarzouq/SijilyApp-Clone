<?php

namespace App\Http\Validation;
use App\Rules\expenseSubCategories;
use App\Rules\fixedExpenses;


class RulesType
{
    public static function store()
    {
        return [
            'title' => 'required',
            'type_id' => 'required'
        ];
    }

    public static function import()
    {
        return [
            'title' => 'required',
            'amount' => 'required|numeric',
        ];
    }

    public static function update()
    {
        return [
            'id' => 'required|exists:expenses,id',
            // 'date' => 'required',
            // 'sub_cat_name' => 'required',
            // 'sub_cat_fixed_name' =>'required',
            // 'sub_cat_id' => 'required|exists:types,id',
            // 'sub_cat_fixed_expense' =>'required|exists:types,id',
            // 'amount' => 'required|numeric',
            // 'status_id' => 'required|exists:statuses,id',
        ];
    }

    public static function delete()
    {
        return [
            'id' => 'required|exists:types,id'
        ];
    }

    public static function get()
    {
        return [
            'id' => 'required|exists:types,id'
        ];
    }
}
