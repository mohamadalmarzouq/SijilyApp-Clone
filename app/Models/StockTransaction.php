<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = ['child_sys_gen_id','stock_id','date','user_id','recorded_by','description','status_id','is_deletable','deleted','date_timestamp','emp_incharge'];
    protected $hidden =['deleted'];

    public function getStockTransaction($id){
        return $this->with(['status','files'])->findOrFail($id);
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function files()
    {
        return $this->hasMany(InventoryImages::class, 'stock_id','id');
    }

    public function listing($request){
        $transaction =  $this->with(['status','files'])->where('deleted',0);
        if(isset($request['id'])){
            $transaction->where('stock_id',$request['id']);
        }
        $stockTransaction = $transaction->where('user_id',$request['user_id'])->where('deleted',0)->orderBy('date','desc')->get()->toArray();
        return $stockTransaction;
    }
}
