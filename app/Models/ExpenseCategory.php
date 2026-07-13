<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['title','slug','updated_at'];

    protected $guarded = [];


   public function category(){
        return $this->hasMany(Type::class, 'type', 'slug');
   }
}
