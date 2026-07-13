<?php

namespace App\Http\Controllers;

use App\Http\Validation\RulesPayment as Rules;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Libraries\TapPayment;
use App\Models\Payment;
use App\Models\PaymentMeta;
use App\Models\AppUser;
use App\Models\UserSubscription;
use App\Models\PaymentLogs;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Payment();
        $this->meta_model = new PaymentMeta();
        $this->app_user_model = new AppUser();
        $this->user_subscription_model = new UserSubscription();
        $this->payment_logs_model = new PaymentLogs();
        $this->module = $this->primary_model->getTable();
    }

    public function GetUserInfo($user_id)
    {
        $userObj = $this->app_user_model->with(['token'])->where('id', $user_id)->first();

        if ($userObj !==null && $userObj->parent !== 0) {
            $user = $this->primary_model->getUser($userObj->id, ['id','role_id', 'parent', 'full_name', 'user_name', 'email']);
            $user->parent_info = $this->app_user_model->getParentUser($user->parent);
            $user_id = $user->parent;
        } else {
            $user = $this->app_user_model->getUser($userObj->id);
            $user_id = $user->id;
        }

//        $user = $this->primary_model->with(['token','status', 'subCategories', 'subscriptions'])->where("id",$request->user_id)->get()->toArray();
        return $user;
    }

    public function recurringPayment(){

            $app_users = $this->app_user_model->with('Subscription')->where([
                ['is_recur', '=', 1],
                ['customer_id', '!=', null],
                ['default_card_id', '!=', null],
                ['default_card_id', '!=', null],
                ['payment_agreement_id', '!=', null],
            ])->get()->toArray();

            if(!empty($app_users)){
                $payment = new TapPayment();
                // print_r($app_users);

                foreach($app_users as $app_user){
                    if(date('Y-m-d') > $app_user['subscription']['expiry_date']){
                        $user_id = $app_user['id'];
                        $subscription_id = getSubscription('user_subscriptions','user_id',$user_id,'subscription_id');
                        $total_amount = getSubscription('user_subscriptions','user_id',$user_id,'total_amount');

                        $subscription_duration = getSubscription('subscriptions','id',$subscription_id,'duration');
                        $expiry_date = date('Y-m-d h:i:s',strtotime('+'.$subscription_duration. 'days'));
                        $user = $this->getUserInfo($user_id);

                        $card_info['saved_card']['card_id']= $user->default_card_id;
                        $card_info['saved_card']['customer_id']=$user->customer_id;

                        $response = $payment->PostRequest('tokens',$card_info);
                        $response = json_decode($response);

                        if(isset($response->id))
                        {
                            $token = $response->id;
                            $payload = [
                                "customer_initiated"=>false,
                                'threeDSecure'=>false,
                                'save_card'=>false,
                                'amount'=> $total_amount,
                                'currency'=> 'KWD',
                                'source'=>[
                                    "id"=>$token
                                ],
                                "payment_agreement" => [
                                    "id" => $user->payment_agreement_id, // Correct structure
                                ],
                            ];

                            if($user->customer_id == null){
                                $payload['customer'] = [
                                    "first_name"=> $user->full_name,
                                    "email" => $user->email,
                                    "phone" => [
                                        "country_code" => $user->country_code,
                                        "number" => $user->phone,
                                    ]
                                ];
                            }else{
                                $payload['customer']['id'] = $user->customer_id;
                            }
                            $response = $payment->PostRequest('charges', $payload);
                            $response = json_decode($response,true);
                            if(isset($response['errors'])){
                                Log::error('Payment Charges Error===>'.json_encode($response));
                                //$this->payment_logs_model->create(['message'=>json_encode($response),'status'=>'error']);
                                continue;
                            }else{

                                $serialize_data = serialize($response);
                                $payment_data = [
                                    'tx_id'=>$response['id'],
                                    'receipt_id'=>$response['receipt']['id'],
                                    'subscription_id'=> $subscription_id,
                                    'user_id'=> $user_id,
                                    'status' => $response['status'],
                                    'amount' => $response['amount']
                                ];
                                //$this->payment_logs_model->create(['message'=>json_encode($response),'status'=>'success']);
                                $payment_id = $this->primary_model->create($payment_data)->id;
                                $meta_data = ["user_id"=>$user_id,"payment_id"=>$payment_id,"meta_data"=>$serialize_data];
                                $this->meta_model->create($meta_data);
                                $this->user_subscription_model->where('user_id',$user_id)->where('subscription_id',$subscription_id)->update(['start_date' => date('Y-m-d h:i:s'),"expiry_date" => $expiry_date]);
                            }
                        }
                        else
                        {
                            Log::error('Token Error===>'.json_encode($response));
                        }

                    }



                }
            }
        }
}
