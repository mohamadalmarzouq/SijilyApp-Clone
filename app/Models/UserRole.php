<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'name_ar','slug'];
    protected $hidden = ['updated_at', 'created_at', 'deleted_at'];
    protected $module_name = 'user_roles';

    public function getModuleName(){
            return $this->module_name.".";
    }
    public function getColumnsForDataTable()
    {
        $data = [
            ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
            ['data' => 'name', 'name' => 'name', 'title' => 'Name'],
            ['data' => 'name_ar', 'name' => 'name_ar', 'title' => 'Name (Arabic)'],
            ['data' => 'action', 'name' => 'Action', 'searchable' => false],
            // ['data' => 'created_at', 'name' => 'created_at', 'visible' => false]
        ];

        return json_encode($data);
    }

    public function preventSearch(){
        return [
                'Action',
             ];
    }

    public function orderArray()
    {
       return [
                ['data' => 'id', 'name' => 'id', 'order' => true,'search'=>false],
                ['data' => 'name', 'name' => 'name', 'order' => true,'search'=>true],
                ['data' => 'name_ar', 'name' => 'name_ar', 'order' => true,'search'=>true],
                ['data' => 'action', 'name' => 'Action', 'order' => false,'search'=>true],
                // ['name' => 'created_at', 'order' => false]
         ];
    }

    public function orderingColumn()
    {
        return json_encode([['2', 'desc']]);
    }

     public function apiListing()
    {
        $result =  $this->orderBy('id','asc')->get()->toArray();
        return makeClientHappy($result,'success');
    }

    public function Permission(){
        return $this->hasMany(UserPermission::class,'role_id','id')->with('Modules');
    }

}
