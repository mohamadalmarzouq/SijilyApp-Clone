<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User extends Model
{


    protected $fillable = ['name','email', 'password','role_id'];

    protected $hidden = [
        'password',
    ];

    protected $module_name = 'users';

    public function getModuleName(){
            return $this->module_name.".";
    }
    public function preventSearch(){
        return [
                'Action',
             ];
    }

    public function getColumnsForDataTable()
    {
            $data = [
                ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
                ['data' => 'name', 'name' => 'name', 'title' => 'Name'],
                ['data' => 'email', 'name' => 'email', 'title' => 'Email'],
                ['data' => 'action', 'name' => 'Action', 'searchable' => 'false'],
                ['data' => 'created_at', 'name' => 'created_at', 'visible' => false]
            ];

        return json_encode($data);
    }

    public function orderArray()
    {
       return [
                // ['data' => 'id', 'name' => 'id', 'order' => true],
                ['data' => 'name', 'name' => 'name', 'order' => true,"search"=>true],
                ['data' => 'email', 'name' => 'email', 'order' => true,"search"=>true],
                ['data' => 'action', 'name' => 'Action', 'order' => false],
                ['name' => 'created_at', 'order' => false]
         ];

    }

    public function orderingColumn()
    {
        return json_encode([['8', 'desc']]);
    }

}
