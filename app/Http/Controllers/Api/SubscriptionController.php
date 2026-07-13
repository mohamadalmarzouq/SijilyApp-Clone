<?php

namespace App\Http\Controllers\Api;

use App\CancelledSubscription;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AccessToken;
use App\Models\AppUser;
use Illuminate\Support\Str;
use App\Models\UserSubscription as ModelsUserSubscription;

use App\Models\SubscribedUser;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->primary_model = new Subscription();
        $this->app_user_model = new AppUser();
        $this->user_subscription_model = new UserSubscription();
        $this->subscribe_user= new SubscribedUser();
        $this->cancelsubscription = new CancelledSubscription();
    }

    public function CancelSubscription(Request $request){
        try{
            $data = $this->primary_model->where("id",$request->id)->first();
            $suscriber_user = $this->subscribe_user->where("user_id",$request->user_id)->orderBy('created_at', 'DESC')->skip(0)->take(1)->get();
        
            if(isset($suscriber_user[0])){
                $child_users = $this->app_user_model->getChildUsers($request->user_id);

                if (!empty($child_users)) {
                    foreach ($child_users as $user) {
                        $this->app_user_model->where('id', $user['id'])->update(["deleted" => 1, "permanent_delete" => 1]);
                    }
                }
                
                $amount = $suscriber_user[0]['total_amount'];
                $subscriptionId = $request->id;
                $userId = $request->user_id;
                $cancelledBy = $userId;
                // $this->user_subscription_model::where('user_id', $userId)->delete();
                ModelsUserSubscription::where('user_id', $request->user_id)->update(['no_of_users' => 0]);

                $this->app_user_model->where('id',$userId)->update(["free_package" => 0, "package_taken" => 0, "is_subscribed" => 0]);
                $this->setAccessToken($userId);
                $this->cancelsubscription->user_id = $userId;
                $this->cancelsubscription->cancelled_by = $cancelledBy;
                $this->cancelsubscription->amount = $amount;
                $this->cancelsubscription->subscription_id = $subscriptionId;
                $this->cancelsubscription->save();
            }
            return makeClientHappy([],'Subscription cancelled successfully');
        }catch(\Exception $e){
            dd($e);
            return sendExpToClient($e);
        }

    }
    public function CancelSubscriptionGet(Request $request){
         try{
            $cancelsubscription_user = $this->cancelsubscription
                ->where('user_id', $request->user_id)
                ->latest('created_at') // same as orderBy('created_at', 'DESC')
                ->first();   


            return makeClientHappy($cancelsubscription_user,'Subscription cancelled successfully');
        }catch(\Exception $e){
            dd($e);
            return sendExpToClient($e);
        }

    }

    private function setAccessToken($user_id)
    {
        $token = Str::random() . time();
        $exp_time = time() + (365 * 24 * 60 * 60);  // +1 Year
        
        $data = AccessToken::updateOrCreate(
            ['user_id' => $user_id],
            ['user_id' => $user_id, 'access_token' => $token, 'expiry_time' => $exp_time]
        );

        return $data;
    }

    public function AllSubscriptions(Request $request)
    {
        try {
            $response = $this->primary_model->whereNull('deleted_at')->where('status',1)->get();
            return makeClientHappy($response,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }
}
