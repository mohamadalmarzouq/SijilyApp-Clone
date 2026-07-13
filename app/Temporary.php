<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Temporary extends Model
{
   protected $fillable = ["user_id","ref_no","data","status"];
}
