<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserPermission extends Model
{
    protected $fillable = ['name', 'slug'];

    public function getColumnsForDataTable()
    {

            $data = [
                ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
                ['data' => 'name', 'name' => 'name', 'title' => 'Name'],
                ['data' => 'action', 'name' => 'Action', 'searchable' => 'false'],
                ['data' => 'created_at', 'name' => 'created_at', 'visible' => false]
            ];

        return json_encode($data);
    }

    public function orderArray()
    {
       return [
                ['data' => 'id', 'name' => 'id', 'order' => true],
                ['data' => 'name', 'name' => 'name', 'order' => true],
                ['data' => 'action', 'name' => 'Action', 'order' => false],
                ['name' => 'created_at', 'order' => false]
         ];

    }

    public function orderingColumn()
    {
        return json_encode([['8', 'desc']]);
    }

    public function Modules(){
        return $this->belongsTo(UserModule::class,'module_id','id');
    }

}
