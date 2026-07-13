<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use App\Http\Validation\RulesCustomer as Rules;

class CustomerController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Customer();
    }
    public function store(Request $request){
        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;
        }
        $request->merge(['user_id'=>$user_id]);

        $validation = Validator::make($request->all(), Rules::store());
        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

        $user = $this->primary_model->create($request->only($this->primary_model->getFillable()));
        return makeClientHappy($user,trans('auth.success'));
    }

    public function list(Request $request){

        $user = $this->primary_model->where('status',1);
        if(isset($request->type)){
            $user->where('type',$request->type);
        }

        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){

            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);

        // $request->merge(['recorded_by'=>$recorded_by]);


        $users = $user->where('user_id',$request->user_id)->get();

        return makeClientHappy($users,trans('auth.success'));
    }

    public function delete(Request $request){

        $validation = Validator::make($request->all(), Rules::delete());
        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

        $this->primary_model->where('id',$request->id)->update(['status'=>0]);
        return sendMsgToClient(trans('auth.deleted'));
    }
}
