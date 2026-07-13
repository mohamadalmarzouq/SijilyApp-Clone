<?php

function getDuration($duration){
    switch($duration){
        case 'today':
           return \Carbon\Carbon::today();
        break;
        case 'yesterday':
            return \Carbon\Carbon::now()->subDays(1);
        break;
        case '7days':
            return \Carbon\Carbon::now()->subDays(7);
        break;
        case '30days':
            return \Carbon\Carbon::now()->subDays(30);
        break;
        case '7':
            return \Carbon\Carbon::now()->subDays(7);
        break;
        case '30':
            return \Carbon\Carbon::now()->subDays(30);
        break;
        default:
        return \Carbon\Carbon::now()->subDays(3650);
    }

}


function getExpirationDuration($duration){
    switch($duration){
        case 'today':
           return \Carbon\Carbon::today();
        break;
        case 'tomorrow':
            return \Carbon\Carbon::now()->addDays(1);
        break;
        case '7days':
            return \Carbon\Carbon::now()->addDays(7);
        break;
        case '7':
            return \Carbon\Carbon::now()->addDays(7);
        break;
        case '30days':
            return \Carbon\Carbon::now()->addDays(30);
        break;
        case '30':
            return \Carbon\Carbon::now()->addDays(30);
        break;
        default:
        return \Carbon\Carbon::now()->subDays(3650);
    }

}

function checkInMultiDeminsionalArray($array, $keyToFind, $valueToFind , $getValue = false)
{
    if (!$array) {
        return false;
    }

    foreach ($array as $value) {
        if ($value[$keyToFind] == $valueToFind) {

            if($getValue) {
                return $value;
            }
            return true;
        }

    }
    return false;
}

function hasRole($slug, $key)
{
    return Auth()->user()->hasPermission($slug, Auth()->user()->role_id, $key);
}

function removeKey($values){
    unset($values);
}


function checkIfRoleChecked($permissions, $slug, $type)
{
    $value = '';

    if (isset($permissions)) {
        foreach ($permissions as $permission) {
            if ($permission->slug == $slug && $permission->{$type} == 1) {
                $value = 'checked';
            }
        }
    }

    return $value;
}

function checkUserPermissions($permissions, $id)
{
    $value = '';
    if (isset($permissions)) {
        foreach ($permissions as $permission) {
            if ($permission['module_id'] == $id) {
                $value = 'checked';
            }
        }
    }

    return $value;
}


function hasPermissions($slug,$role_id)
{
    $permissions =  \DB::table('permissions')->select("slug","role_id","is_visible")->get();
    $value = 'no';
    if (isset($permissions)) {
        foreach ($permissions as $permission) {
            if ($permission->slug == $slug['slug'] && $permission->role_id == $role_id && $permission->is_visible == 1) {
                $value = 'yes';
            }
        }
    }
    return $value;
}
