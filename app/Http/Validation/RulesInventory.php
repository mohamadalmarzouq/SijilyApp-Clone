<?php

namespace App\Http\Validation;


class RulesInventory
{
    public static function store()
    {
        return [
            'item_name' => 'required',
            'emp_incharge' =>  'required',
            'status_id' => 'required'
        ];
    }

    public static function update()
    {
        return [
            'id' => 'required|exists:inventories,id',
        ];
    }

    public static function delete()
    {
        return [
            'id' => 'required|exists:inventories,id',
        ];
    }
    public static function get()
    {
        return [
            'id' => 'required|exists:inventories,id',
        ];
    }
    public static function listing($slugs)
    {
        return [
            'slug' => 'required|in:' . $slugs,
        ];
    }

    public static function storeUpdates()
    {
        return [
            'status_id' => 'required',
            'stock_id' => 'required',
            'emp_incharge' =>  'required',
            'date' => 'required|date|date_format:Y-m-d'
        ];
    }

    public static function Updates()
    {
        return [
            'id' => 'required',
            'status_id' => 'required',
            'emp_incharge' =>  'required',
            'date' => 'required|date|date_format:Y-m-d'
        ];
    }
    public static function StockTransDelete()
    {
        return [
            'id' => 'required',
        ];
    }
}
