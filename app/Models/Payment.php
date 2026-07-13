<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['subscription_id','amount','tx_id','receipt_id','customer_id',"user_id","status"];

    public function card_info(){
        return $this->hasMany(PaymentMeta::class,'payment_id','id')->select('meta_data','payment_id');
    }

    public function get_details($user_id){
return $this->with(['get_subscription', 'card_info'])
            ->where('user_id', $user_id)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->toArray();

    }

    public function subscription(){
        return $this->hasOne(Subscriptions::class, 'subscription_id', 'id');
    }

    public function get_subscription(){
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id')->select('subscription','id');
    }

}
