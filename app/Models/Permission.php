<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['slug', 'role_id', 'add', 'edit', 'show', 'delete', 'is_visible','bypass_visibility'];

    public function attachRoles($request, $id)
    {

        $this->where('role_id', $id)->delete();
        
        //add permissions
        foreach ($request->add as $key => $item) {
            $this->create([
                'slug' => $key,
                'role_id' => $id,
                'add' => $item,
                'edit' => $request->edit[$key],
                'show' => $request->view[$key],
                'delete' => $request->delete[$key],
                'is_visible' => $request->is_visible[$key],
                'bypass_visibility' => $request->bypass_visibility[$key]
            ]);
        }
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_permissions');
    }
}
