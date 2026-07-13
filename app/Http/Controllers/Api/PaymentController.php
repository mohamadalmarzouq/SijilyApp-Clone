<?php

namespace App\Http\Controllers\Api;
use App\CancelledSubscription;
use App\Models\Payment;
use App\Models\PaymentMeta;
use App\Models\AppUser;
use App\Models\Subscription;
use App\Http\Validation\RulesPayment as Rules;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Libraries\TapPayment;
use App\Temporary;
use App\Models\SubscribedUser;
use App\Models\UserSubscription;
class PaymentController extends Controller
{
     function __construct()
    {
        $this->primary_model = new Payment();
        $this->meta_model = new PaymentMeta();
        $this->app_user_model = new AppUser();
        $this->subscription_model = new Subscription();
        $this->temp_model = new Temporary();
        $this->cancelsubscription=new CancelledSubscription();
        $this->module = $this->primary_model->getTable();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    public function charge(Request $request){
        try{
            $validation = Validator::make($request->all(), Rules::store());
            if ($validation->fails()) {
                return sendErrorToClient(implode(",",$validation->messages()->all()));
            }

            $payment = new TapPayment();

            $user = $this->getUserInfo($request->user_id);

            $card_info['saved_card']['card_id']=$request->token;
            $card_info['saved_card']['customer_id']=$user->customer_id;
            $response = $payment->PostRequest('tokens',$card_info);
            $response = json_decode($response);

            if(!isset($response->id)){
                return sendErrorToClient('Invalid Card Id');
            }

            $token = $response->id;


            $subscription_id = $request->subscription_id;
            $user_id = $request->user_id;



            $payload = [
                // "customer_initiated"=>false,
                'threeDSecure'=>true,
                'save_card'=>true,
                'source'=>["id"=>$token],
            ];


            $payload['customer']['id'] = $user->customer_id;

            $payload['redirect']['url']= url('/api/redirect_url');
            $request->merge($payload);
            unset($request['user_id'],$request['subscription_id'],$request['token']);
            $response = $payment->PostRequest('charges',$request->all());
            $response = json_decode($response,true);

            $status = [
                'CAPTURED' => "Completed",
                'DECLINED' => "Declined",
                'INITIATED' => 'Initiated',
                'IN_PROGRESS' => 'In Progress',
                'FAILED' => 'Failed',
                'RESTRICTED' => 'Restricted'
            ];
            if(isset($response['errors'])){
                return sendErrorToClient($response);
            }else{
                $serialize_data = serialize($response);
                $request->merge([
                    'tx_id'=>$response['id'],
                    'receipt_id'=>$response['receipt']['id'],
                    'subscription_id'=> $subscription_id,
                    'user_id'=> $user_id,
                    "status"=>$status[$response['status']]
                ]);

                $payment_id = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;
                $meta_data = ["user_id"=>$user_id,"payment_id"=>$payment_id,"meta_data"=>$serialize_data];
                $this->meta_model->create($meta_data);
                $update_data = ['is_recur'=>$request->is_recur,'customer_id'=>$response['customer']['id']];
                if($user->default_card_id==null){
                    $update_data['default_card_id']= $request->token;
                }
                $this->app_user_model->where('id',$user_id)->update($update_data);
                return makeClientHappy($response);
            }
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }

    }

    public function redirectUrl(Request $request){
        try{
            $tap_id = $request->tap_id;
            $temp_data = $this->temp_model->where("ref_no",$tap_id)->first();
            // $this->app_user_model->where('id',$temp_data->user_id)->update(['payment_agreement_id'=>json_encode($request->all())]);
            $subscription_data = json_decode($temp_data->data);
            $payment = new TapPayment();
            $status = [
                'CAPTURED' => "Completed",
                'DECLINED' => "Declined",
                'INITIATED' => 'Initiated',
                'IN_PROGRESS' => 'In Progress',
                'FAILED' => 'Failed',
                'RESTRICTED' => 'Restricted'
            ];

            $payment_query = $this->primary_model->where("tx_id",$tap_id)->where("status",'Initiated')->first();
            $payment = $payment->rcCurlGetRequest('charges',$tap_id);
            if(isset($payment->payment_agreement))
            {
                $this->app_user_model->where('id',$temp_data->user_id)->update(['payment_agreement_id'=>$payment->payment_agreement->id??null]);
            }
            $appUser = $this->app_user_model->getUser($temp_data->user_id);

            if($status[$payment->status]=="CAPTURED" || $status[$payment->status]=="Completed"){
                $appUser['payments'] = $payment;
                $update_data = ['is_recur' => 1, "is_subscribed" => 1];
                $this->app_user_model->where('id', $temp_data->user_id)->update($update_data);
                if(isset($request->is_wallet_payment) && $request->is_wallet_payment==true){
                    $cancelsubscription_user = $this->cancelsubscription
                    ->where('user_id', $temp_data->user_id)
                    ->latest('created_at') // same as orderBy('created_at', 'DESC')
                    ->first();
                    if ($cancelsubscription_user) {
                        $cancelsubscription_user->delete();
                    }
                }

                $payment_query->status = $status[$payment->status];
                $payment_query->receipt_id = $payment->receipt->id;
                $payment_query->save();
                $subscription_data->user_id = $temp_data->user_id;
                $this->createSubscription($subscription_data);
                return redirect('/api/payment_url?success=true');
            }else{
                return redirect('/api/payment_url?success=false');
            }


        }
        catch(\Exception $e){
            return redirect('/api/payment_url?success=false');
        }

    }

    private function createSubscription($request){
        switch ($request->update){
            case 0:
                $subs = Subscription::find($request->subscription_id);
                $expiration = $subs->expire_in;
                $_date = date('Y-m-d h:i:s',strtotime('+'.$subs->duration. 'days'));
                $request->expiry_date = $_date;
                $request->start_date = date('Y-m-d h:i:s');
                $no_of_user = isset($request->no_of_users) ? $request->no_of_users : $subs->register_limit ;
                $total_amount = isset($request->total_amount) ? $request->total_amount : $subs->amount ;
                $tot_user_amount = isset($request->total_user_amount) ? $request->total_user_amount : $subs->per_user_amount ;
                $userSubscriber_data = [
                    'user_id'=> $request->user_id,
                    'subscription_id'=>$request->subscription_id,
                    'start_date'=>$request->start_date,
                    'expiry_date'=>$request->expiry_date ,
                    'no_of_users'=>$no_of_user ,
                    'total_amount'=>$total_amount,
                    'total_user_amount' => $tot_user_amount,
                ];

                $Subscribers = UserSubscription::with(['getUser'])->updateOrCreate(['user_id'=>$request->user_id],$userSubscriber_data);
                $subscribe= ['is_subscribed'=>'1','package_taken'=>1];
                if($request->subscription_id ==1)
                    $subscribe['free_package'] = 1;

                $total_user_amount = ($no_of_user * $subs->amount);
                $total_no_of_users = UserSubscription::where('user_id',$request->user_id)->get()->first()->no_of_users;
                SubscribedUser::create(['user_id'=> $request->user_id,'no_of_users'=> $no_of_user,'total_user_amount'=> $tot_user_amount,'total_amount'=> $total_amount]);
                AppUser::where('id',$request->user_id)->update($subscribe);
            break;
            case 1:
                $userSubscriber_data = [
                    'user_id'=> $request->user_id,
                    'no_of_users'=>$request->no_of_users,
                    'total_amount'=>$request->total_amount,
                    'total_user_amount' =>$request->total_user_amount,
                ];
                $total_no_of_users = UserSubscription::where('user_id',$request->user_id)->get()->first()->no_of_users;
                $id = SubscribedUser::create($userSubscriber_data)->id;
                $no_of_users = SubscribedUser::where('user_id',$request->user_id)->sum('no_of_users');
                UserSubscription::where('user_id',$request->user_id)->update(['no_of_users'=>$request->no_of_users + $total_no_of_users]);
                $this->app_user_model->where('id',$request->user_id)->update(['free_package'=>0]);
            break;
        };
    }

    public function paymentUrl(){

    }

    public function getUserInfo($user_id){
        return AppUser::findOrFail($user_id);
    }

    public function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function getCardListing(Request $request){
        try{
            $res=[];
            $validation = Validator::make($request->all(), Rules::listing());
            if ($validation->fails()) {
                return sendErrorToClient(implode(",",$validation->messages()->all()));
            }
            $payment = new TapPayment();
            $response = $payment->rcCurlGetRequest('card',$request->customer_id);
            if(isset($response->data)){
                foreach($response->data as $resp){
                    $resp->is_default = $this->getDefaultCard($request->user_id,$resp->id);
                    // $resp->is_default = $this->getDefaultCard($request->user_id,$resp->id);
                    $res[] = $resp;
                }
            }

            return makeClientHappy($res);
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function getDefaultCard($user_id,$card_id){
        $user = $this->getUserInfo($user_id);
        if($user->default_card_id==null){
           return 0;
        }else if($card_id == $user->default_card_id){
            return 1;
        }else{
            return 0;
        }
    }

    public function addCard(Request $request){
        try{
            $validation = Validator::make($request->all(), Rules::save());
            if ($validation->fails()) {
                return sendErrorToClient(implode(",",$validation->messages()->all()));
            }
            $payment = new TapPayment();
            $response = $payment->customRequest('card/'.$request->customer_id,['source'=>$request->source]);
            //$response1 = $payment->customRequest('card/'.$request->customer_id,['source'=>$request->source]);
            // $this->app_user_model->where('id',$request->user_id)->update(['payment_agreement_id'=>json_encode($response)]);
            return makeClientHappy($response);
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function defaultCard(Request $request){
        $validation = Validator::make($request->all(), Rules::default());
        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }
        $this->app_user_model->where('id',$request->user_id)->update(['default_card_id'=>$request->card_id]);
        return makeClientHappy(['message'=>'Your default card has been save']);
    }

    public function deleteCard(Request $request){
        try{
            $validation = Validator::make($request->all(), Rules::delete());
            if ($validation->fails()) {
                return sendErrorToClient(implode(",",$validation->messages()->all()));
            }
            $payment = new TapPayment();
            $response = $payment->deleteRequest('card/'.$request->customer_id.'/'.$request->card_id);

            return makeClientHappy($response);
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function paymentHistory(Request $request){
        $user_id = $request->user_id;
        $history = $this->app_user_model->with('subscriptions.subscription')->findOrFail($user_id);
        $subscription = $this->subscription_model->get_details($user_id);
        if(!isset($subscription->subscription_id)){
            return sendErrorToClient('Subscription id not found');
        }
        $subscription_id = $subscription->subscription_id;
        $history['recurring_date']= ['start_date'=>$subscription->start_date,'expory_date'=>$subscription->expiry_date];
        // $history['subscription_details']= $this->subscription_model->get_subscription($subscription_id);
        $history['payment_details']= $this->primary_model->get_details($user_id);
        return makeClientHappy($history);
    }


}
