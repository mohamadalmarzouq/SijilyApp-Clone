<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'slug'];

    protected $appends = ['date_time_format'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($role) {
            foreach ($role->permissions as $permission) {
                $permission->delete();
            }
            foreach ($role->users as $user) {
                $user->delete();
            }
        });
    }

     public function preventSearch(){
        return [
                'Action',
             ];
    }


    public function getColumnsForDataTable()
    {
        $data = [

            ['data' => 'name', 'name' => 'name'],
            ['data' => 'date_time_format', 'name' => 'date_time_format', 'title' => 'Created At', 'searchable' => 'false'],
            ['data' => 'action', 'name' => 'Action', 'searchable' => 'false'],
            ['data' => 'created_at', 'name' => 'created_at', 'visible' => false]
        ];

        return json_encode($data);
    }

    public function orderArray()
    {
        return [
            ['data' => 'name', 'name' => 'name', 'order' => true],
            ['data' => 'created_at', 'name' => 'created_at', 'order' => true],
            ['data' => 'action', 'name' => 'Action', 'order' => false],
            ['name' => 'created_at', 'order' => false]
        ];
    }

    public function orderingColumn()
    {
        return json_encode([['3', 'desc']]);
    }

    public function getDateTimeFormatAttribute()
    {
        return date('F jS, Y g:i a', strtotime($this->created_at));
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'role_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function getRoleID($slug)
    {
        return $this->where('slug', $slug)->first()->id;
    }

    public function getWidgetRoles()
    {
        return $this->get();
    }



    public function allRoles()
    {
        return $this->query();

        $current_user = Auth()->user();

        if (!in_array($current_user->role_id, $current_user->getSuperAdminRoleIds())) {

            $query = $query->where('creator_id', $current_user->id);
        } else {
            $query = $query->query();
        }
    }
}
