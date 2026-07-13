<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    public function hasChildren($id)
    {
        return (bool)$this->where('parent', $id)->count();
    }

    public function children()
    {
        return $this->hasMany(Module::class, 'parent');
    }
}
