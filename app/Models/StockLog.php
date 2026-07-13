<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    protected $fillable = ['user_id','log','log_ar','stock_id'];
    protected $hidden = ['stock_id'];

    public function listing($request){
        $headers = apache_request_headers();
        $data = [];
        $log =  $this->where("user_id",$request['user_id']);
        if(isset($request['id'])){
            $log->where('stock_id',$request['id']);
        }
        $logs = $log->orderBy('id','desc')->get()->toArray();
        $i = 0;
        foreach($logs as $log){
            if(isset($headers['Local']) && $headers['Local']=='ar') {
                $logs[$i]['log'] = $log['log_ar'];
                unset($logs[$i]['log_ar']);
            }else{
                unset($logs[$i]['log_ar']);
            }

            $data[] = $logs[$i];
            $i++ ;
        }
        return $data;
    }
}
