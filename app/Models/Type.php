<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $fillable = ['title', 'slug','type','type_id', 'module','user_id','updated_at'];

    public function getTypeId($module, $slug)
    {
        return $this->where('module', $module)->where('slug', $slug)->first()->id;
    }
    public function store($request)
    {
        return $this->create($request->only($this->getFillable()));
    }
    public function getTypeByModule($module)
    {
        return $this->where('module', $module)->get();
    }

    public function getTypesForRules($module)
    {
        $types = $this->where('module', $module)->get();

        $type_string = '';

        foreach ($types as $type) {
            $type_string = $type_string.' '.$type->slug;
        }

        $type_string = preg_replace('/^,/', '', implode(',', explode(' ', $type_string)));

        return $type_string;
    }

}
