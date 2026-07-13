<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StockLog;
use App\Models\ActivityLog;

class StockLogController extends Controller
{
    function __construct()
    {
        $this->primary_model = new StockLog();
        $this->activity_model = new ActivityLog();
    }

    public function list(Request $request){
        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){
            $recorded_by = $request->user_id;
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;
            $recorded_by = $request->user_id;
        }
        $request->merge(['user_id'=>$user_id]);
        $LogList = $this->primary_model->listing($request->all());
        return makeClientHappy($LogList,trans('auth.success'));
    }
}
