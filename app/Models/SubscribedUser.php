<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscribedUser extends Model
{
    protected $fillable=['user_id','no_of_users','total_user_amount','total_amount','status','updated_at','expiry_date','start_date'];

}
