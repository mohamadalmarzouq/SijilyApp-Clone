<?php

namespace App\Models;
use Carbon\Carbon;

use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    // public function getCreatedAtAttribute($date)
    // {
    //     return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d');
    // }

    // public function getUpdatedAtAttribute($date)
    // {
    //     return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d');
    // }
    public function apiListing($request, $user_id, $limit)
    {
        $headers = apache_request_headers();
        $results = $this->select("id","log_name","description","recorded_by","description_ar","subject_id","causer_id","created_at","updated_at");

        if(isset($request['start_date']) && isset($request['end_date'])){
            // $results->whereBetween('created_at',[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            // $results->where("created_at",">=",date('Y-m-d',strtotime($request['start_date'])));
            // $results->where("created_at","<=",date('Y-m-d',strtotime($request['end_date'])));
            $results->whereBetween(\DB::raw('DATE(created_at)'),[$request['start_date'],$request['end_date']]);
        }else if(isset($request['start_date'])){
            $results->where("created_at",">=",date('Y-m-d',strtotime($request['start_date'])));
        }else if(isset($request['end_date'])){
            $results->where("created_at","<=",date('Y-m-d',strtotime($request['end_date'])));
        }

        if(isset($request['recorded_by'])){
            $results->where("recorded_by",$request['recorded_by']);
        }else{
            $results->where("recorded_by",$user_id);
        }
        // dd($results->toSql());
        if(isset($request['category']) && $request['category'] != "12"){
          $categories= ["","pendings","","sales","expenses","account_receivable","account_payable","capital_expenditure","owner_accounts","inventories","bank_reconciles"];
          $results->where("module",$categories[$request['category']]);
        }else if(isset($request['category']) && $request['category']==12){
          $results->whereNotIn("module",['pendings','sales','expenses','account_receivable','account_payable','capital_expenditure','owner_accounts']);
        }

        $result = $results->orderBy('created_at','desc')->paginate($limit)->toArray();
        $data['data'] = [];
        foreach($result['data'] as $res){
            // if(isset($headers['Local']) && $headers['Local']=='ar') {
            //     $res['description'] = $res['description_ar'];
            //     unset($res['description_ar']);
            // }else{
            //     unset($res['description_ar']);
            // }
            $data['data'][] =$res;
        }

        $fulldata['data']  = $data; //$result['data'];
        $data['page'] =  $result;
        unset($data['page']['data']);
        unset($fulldata['data']['message']);
        $new_row = array_merge($fulldata,$data);
        return $new_row;
    }


}



