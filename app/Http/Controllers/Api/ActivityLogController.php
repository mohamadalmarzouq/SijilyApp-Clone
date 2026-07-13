<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ActivityLogController extends Controller
{
    function __construct()
    {
        $this->primary_model = new ActivityLog();
        $this->module = $this->primary_model->getTable();
    }

    public function listing(Request $request)
    {
        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){
            $user_id = $parent_id;
            // $request->merge(['recorded_by'=>$user_id]);
        }else {
            $user_id = $request->user_id;
        }
        $request->merge(['user_id'=>$user_id]);

        try {
            $response = $this->primary_model->apiListing($request->all(),$request->user_id, $this->data_limit);
            return PagintionResponse($response,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }
}
