<?php   namespace App\Http\Validation;

use Illuminate\Http\Request;

class RulesIdentityChild
{
    public static function get()
    {
        return [
            'id' => 'required',
            'type_id' => 'required',
            'parent_sys_id' => 'required'
        ];
    }
}
