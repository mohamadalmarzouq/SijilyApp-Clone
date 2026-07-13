<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoin;

class UserSubscription extends Model
{
      use EloquentJoin;
    protected $useTableAlias = true;
    protected $appendRelationsCount = false;
    protected $leftJoin = true;
    protected $aggregateMethod = 'MAX';
    protected $fillable = [
        'user_id',
        'subscription_id',
        'start_date',
        'expiry_date',
        'no_of_users',
        'total_amount',
        'total_user_amount'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function getStartDateAttribute($value)
    {
       return date("Y-m-d",strtotime($value));
    }
    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = $value;
    }


    public function getExpiryDateAttribute($value)
    {
       return date("Y-m-d",strtotime($value));
    }

    public function setExpiryDateAttribute($value)
    {
        $this->attributes['expiry_date'] = $value;
    }

    public function getUserSubscription($user_id){
        return $this->where('user_id', $user_id)->get()->toArray();
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function getUser(){
        return $this->hasOne(AppUser::class, 'id','user_id');
    }


}
