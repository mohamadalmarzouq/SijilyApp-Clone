<?php

namespace App\Permissions;

use App\Models\Role;

trait HasPermissionsTrait
{

    public function role()
    {
        return $this->belongsTo(Role::class);

    }


    public function hasPermission($slug, $role, $key)
    {

        $authorized = 0;

        $query = $this->where('role_id', $role)
            ->with(["role.permissions" => function ($q) use ($slug, $role) {
                $q->where('permissions.slug', $slug);
            }]);


        if ($query->count() > 0) {
            foreach ($query->get() as $value) {
                if ($value->role->permissions->isNotEmpty()) {
                    foreach ($value->role->permissions as $permission) {
                        $authorized = $permission->{$key};
                    }
                }
            }
        }

        return $authorized;
    }
}