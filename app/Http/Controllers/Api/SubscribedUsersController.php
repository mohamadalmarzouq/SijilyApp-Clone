<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SubscribedUser;
use App\Models\UserSubscription;
use App\Models\Subscription;
use Illuminate\Support\Facades\Validator;
use App\Models\AppUser;
use App\Models\Payment;
use App\Temporary;

class SubscribedUsersController extends Controller
{
    public function __construct()
    {
        $this->primary_model = new SubscribedUser();
        $this->user_subscription = new UserSubscription();
        $this->subscription = new Subscription();
        $this->temp_model = new Temporary();
        $this->app_user_model = new AppUser();
        $this->payment_model = new Payment();
        $this->module = $this->primary_model->getTable();
    }

    public function subscribeUser(Request $request){
        try {
            $total_no_of_users = UserSubscription::where('user_id',$request->user_id)->first();
            // dd($total_no_of_users);
            if($request->no_of_users < $total_no_of_users->no_of_users){
                throw new \Exception("Sorry! You can't downgrade user");
            }
            $id = SubscribedUser::create($request->all())->id;
            $no_of_users = SubscribedUser::where('user_id',$request->user_id)->sum('no_of_users');
            UserSubscription::where('user_id',$request->user_id)->update(['no_of_users'=> $request->no_of_users]);
            $this->app_user_model->where('id',$request->user_id)->update(['free_package'=>0]);
            $appUser= $this->app_user_model->getUser($request->user_id);
            return makeClientHappy($appUser,trans('auth.success'));
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function resetSubscription(Request $request){
        $this->primary_model->where('user_id',$request->user_id)->delete();
        $this->user_subscription->where('user_id',$request->user_id)->delete();
        $this->temp_model->where('user_id',$request->user_id)->delete();
        $this->payment_model->where('user_id',$request->user_id)->delete();
        $this->app_user_model->where('id',$request->user_id)->update([
            'free_package' => 0,
            'package_taken' => 0,
            'is_subscribed' => 0,
        ]);

        return makeClientHappy([],'Reset Sucessfully');

    }
}
