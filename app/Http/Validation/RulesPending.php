<?php

namespace App\Http\Validation;

use Illuminate\Http\Request;

class RulesPending
{

    /**
     * Get the validation rules that apply to the requests For AppUserController.
     *
     * @return array
     */
    public static function Pending()
    {
        return [
            'date' => 'required',
            // 'desc' => 'required',
            'amount' => 'required',
        ];
    }
    public static function updatePending(){
        return [
            'id' => 'required|exists:pendings,id',
        ];
    }
    public static function getPending(){
        return [
            'id' => 'required|exists:pendings,id',
        ];
    }

    public static function deletePending(){
        return [
            'id' => 'required|exists:pendings,id',
        ];
    }
}
