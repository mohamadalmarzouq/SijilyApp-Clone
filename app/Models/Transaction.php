<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Transaction extends Model
{
    use SoftDeletes;

        protected $fillable = ["child_sys_gen_id","ref_id","type_id","type","date","amount","note","user_id","updated_at","customer_id","customer_name","recorded_by"];

    public function getTransaction($id){
        return $this->with('Image')->where('id',$id)->get()->first();
    }

    public function getChildTransaction($id){
        return $this->where('id',$id)->get()->first();
    }
    
    public function Image(){
        return $this->hasMany(ImageTransaction::class, 'transaction_id', 'id');
    }
}
