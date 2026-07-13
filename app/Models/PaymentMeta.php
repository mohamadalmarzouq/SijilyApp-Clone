<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMeta extends Model
{
    protected $fillable = ['payment_id','meta_data'];

    public function getMetaDataAttribute($value)
    {
        $data = unserialize($value);
        $card['card'] = $data['card'];
        return $card;
    }

}
