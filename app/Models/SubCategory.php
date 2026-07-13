<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id','module','title','slug'];

    public function store($request)
    {
        return $this->create($request->only($this->getFillable()));
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
