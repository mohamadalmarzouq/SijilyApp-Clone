<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoin;

class Status extends Model
{

    use EloquentJoin;
    protected $useTableAlias = true;
    protected $appendRelationsCount = false;
    protected $leftJoin = false;
    protected $aggregateMethod = 'MAX';
    protected $hidden = ['module'];

    public function getStatusByModule($module)
    {
        return $this->where('module', $module)->get();
    }

    public function getStatusID($module, $slug)
    {
        return $this->where('module', $module)->where('slug', $slug)->first()->id;
    }

    public function getStatusSlug($module, $id)
    {
        return $this->where('module', $module)->where('id', $id)->first()->slug;
    }

    public function getStatusName($module, $id)
    {
        return $this->where('module', $module)->where('id', $id)->first()->title;
    }
    public function getStatusTitle($id,$type)
    {
        return $this->where('id', $id)->first()->{$type};
    }
}
