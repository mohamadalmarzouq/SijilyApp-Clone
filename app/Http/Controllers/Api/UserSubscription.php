<?php

namespace App\Http\Controllers\Api;

use App\CancelledSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;
use App\Models\UserSubscription as Subscribers;
use App\Models\Subscription;
use App\Models\AppUser;
use App\Models\SubscribedUser;
use Illuminate\Support\Facades\Validator;
use App\Models\Payment;
use App\Libraries\TapPayment;
use App\Models\PaymentMeta;
use App\Models\UserSubscription as ModelsUserSubscription;
use App\Temporary;
use App\Models\PaymentLogs;

class UserSubscription extends Controller
{
    public function __construct()
    {
        $this->primary_model = new Subscribers();
        $this->app_user_model = new AppUser();
        $this->subscription = new Subscription();
        $this->payment_model = new Payment();
        $this->app_user_model = new AppUser();
        $this->meta_model = new PaymentMeta();
        $this->temp_model = new Temporary();
        $this->payment_logs_model = new PaymentLogs();
        $this->cancelsubscription=new CancelledSubscription();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function getUserInfo($user_id)
    {
        return AppUser::findOrFail($user_id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $response = '';
        $getSubscriptions = $this->subscription->SubscriptionsIds($request);

        $is_wallet_payment=$request->is_wallet_payment;
        if (!$getSubscriptions) {
            return sendErrorToClient('Couldn\'t find subscription id');
        }

        $validation = Validator::make($request->all(), [
            'subscription_id' => 'required|in:' . $getSubscriptions,
            "is_recur" => 'required',
            "description" => 'required',
            "country_code" => 'required',
            "phone" => 'required',
            "amount" => 'required',
            "currency" => 'required',
        ]);

        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        $header = apache_request_headers();
        $endpoint = url('/api/charge');

        try {
            $status = [
                'CAPTURED' => "Completed",
                'DECLINED' => "Declined",
                'INITIATED' => 'Initiated',
                'IN_PROGRESS' => 'In Progress',
                'FAILED' => 'Failed',
                'RESTRICTED' => 'Restricted'
            ];

            $payment = new TapPayment();
            $user = $this->getUserInfo($request->user_id);
            // $this->app_user_model->where('id',$request->user_id)->update(['payment_agreement_id'=>json_encode($request->all())]);
            if (!isset($request->tap_id) && $request->amount != 0) {
                $card_info['saved_card']['card_id'] = $request->card_id;
                $card_info['saved_card']['customer_id'] = $user->customer_id;
                $response = $payment->PostRequest('tokens', $card_info);
                $response = json_decode($response);
                if (!isset($response->id)) {
                    return sendErrorToClient('Invalid Card Id');
                }

                $token = $response->id;

                $payload = [
                    // "customer_initiated"=>false,
                    'amount' => $request->amount,
                    'currency' => 'KWD', //$request->currency,
                    'threeDSecure' => true,
                    'save_card' => true,
                    'source' => [
                        "id" => $token
                    ],
                ];

                $payload['customer']['id'] = $user->customer_id;
                $redirect_url='/api/redirect_url';
                if (isset($is_wallet_payment) && $is_wallet_payment == true) {
                    $params = [
                        'is_wallet_payment' => $is_wallet_payment,
                    ];
                    // append query parameters safely
                    $redirect_url .= (parse_url($redirect_url, PHP_URL_QUERY) ? '&' : '?') . http_build_query($params);
                }
                $payload['redirect']['url'] = url($redirect_url);
                $response = $payment->PostRequest('charges', $payload);
                // $response1 = $payment->PostRequest('charges',$payload);
                $response = json_decode($response, true);

                if (isset($response['error']) || isset($response['errors'])) {
                    return sendErrorToClient($response);
                }
                if ($response['status'] == "FAILED" || $response['status'] == "DECLINED" || $response['status'] == "ABANDONED" || $response['status'] == "CANCELLED" || $response['status'] == "RESTRICTED" || $response['status'] == "VOID" || $response['status'] == "TIMEDOUT" || $response['status'] == "UNKNOWN") {
                    return sendErrorToClient("Payment Failed");
                }
                $serialize_data = serialize($response);

                $request->merge([
                    'tx_id' => $response['id'],
                    'receipt_id' => isset($response['receipt']['id']) ? $response['receipt']['id'] : '',
                    'subscription_id' => $request->subscription_id,
                    'user_id' => $request->user_id,
                    "status" => $status[$response['status']]
                ]);


                $payment_id = $this->payment_model->create($request->only($this->payment_model->getFillable()))->id;

                $meta_data = ["user_id" => $request->user_id, "payment_id" => $payment_id, "meta_data" => $serialize_data];

                $this->meta_model->create($meta_data);



                // $update_data['payment_agreement_id'] = $response1??null;
                if ($user->default_card_id == null) {
                    $update_data['default_card_id'] = $request->card_id;
                }



                if ($response['status'] == "CAPTURED") {
                    $update_data = ['is_recur' => $request->is_recur, "is_subscribed" => 1];

                    if(isset($is_wallet_payment) && $is_wallet_payment==true){
                        $cancelsubscription_user = $this->cancelsubscription
                        ->where('user_id', $request->user_id)
                        ->latest('created_at') // same as orderBy('created_at', 'DESC')
                        ->first();
                        if ($cancelsubscription_user) {
                            $cancelsubscription_user->delete();
                        }
                    }

                    $this->createSubscription($request);
                }

                if ($response['status'] == "INITIATED") {
                    $update_data = ['is_recur' => 0, "is_subscribed" => 0];

                    $data['ref_no'] = $response['id'];
                    $data['data'] = json_encode($request->all());
                    $data['user_id'] = $request->user_id;
                    $data['status'] = $request->status;
                    $payment_id = $this->temp_model->create($data)->id;
                }
                $this->app_user_model->where('id', $request->user_id)->update($update_data);
            }


            if (!isset($request->tap_id) && $request->amount == 0) {
                    if(isset($is_wallet_payment) && $is_wallet_payment==true){
                        $cancelsubscription_user = $this->cancelsubscription
                        ->where('user_id', $request->user_id)
                        ->latest('created_at') // same as orderBy('created_at', 'DESC')
                        ->first();
                        if ($cancelsubscription_user) {
                            $cancelsubscription_user->delete();
                        }
                    }
                $this->createSubscription($request);
            }



            //    $this->app_user_model->where('id',$request->user_id)->update(['payment_agreement_id'=>json_encode($request->all())]);
            if (isset($request->tap_id)) {
                $payment_query = $this->payment_model->where("tx_id", $request->tap_id)->where("status", 'Initiated')->first();
                if ($payment_query) {
                    $payment = $payment->rcCurlGetRequest('charges', $request->tap_id);
                    $appUser = $this->app_user_model->getUser($request->user_id);
                    // $this->app_user_model->where('id',$request->user_id)->update(['payment_agreement_id'=>json_encode($payment)]);
                    $appUser['payments'] = $payment;
                    $this->createSubscription($request);
                    $payment_query->status = $status[$payment->status];
                    $payment_query->receipt_id = $payment->receipt->id ?? null;
                    $payment_query->save();
                    return makeClientHappy($appUser, trans('auth.success'));
                }
            }

            $appUser = $this->app_user_model->getUser($request->user_id);
            $appUser['payments'] = $response;
            return makeClientHappy($appUser, trans('auth.success'));
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    private function createSubscription($request)
    {
        switch ($request->update) {
            case 0:
                $subs = Subscription::find($request->subscription_id);
                $expiration = $subs->expire_in;
                $_date = date('Y-m-d h:i:s', strtotime('+' . $subs->duration . 'days'));
                $request->merge(['expiry_date' => $_date]);
                $request->merge(['start_date' => date('Y-m-d h:i:s')]);
                $Subscribers = Subscribers::with(['getUser'])->updateOrCreate(['user_id' => $request->user_id], $request->all());
                $subscribe = ['is_subscribed' => 1, 'package_taken' => 1];
                if ($subs->register_limit == 0)
                    $subscribe['free_package'] = 1;

                $total_no_of_users = ModelsUserSubscription::where('user_id', $request->user_id)->first()->no_of_users;
                SubscribedUser::create(['user_id' => $request->user_id, 'no_of_users' => $request->no_of_users, 'total_user_amount' => $request->total_user_amount, 'total_amount' => $request->total_amount]);
                AppUser::with(['Subscription'])->where('id', $request->user_id)->update($subscribe);
                ModelsUserSubscription::where('user_id', $request->user_id)->update(['no_of_users' => $request->no_of_users]);

                break;
            case 1:
                $total_no_of_users = ModelsUserSubscription::where('user_id', $request->user_id)->first()->no_of_users;
                $id = SubscribedUser::create($request->all())->id;
                ModelsUserSubscription::where('user_id', $request->user_id)->update(['no_of_users' => $request->no_of_users]);
                $this->app_user_model->where('id', $request->user_id)->update(['free_package' => 0]);
                break;
        };
    }

    public function get(Request $request)
    {
        try {
            $sale = $this->primary_model->getUserSubscription($request->user_id);

            return makeClientHappy($sale, trans('auth.success'));
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function getUsersExpiry(Request $request)
    {
        $subscription = $this->primary_model->where("user_id", $request->user_id)->get()->first();
        if (!empty($subscription)) {
            $current_date = date('Y-m-d');
            $expire_date = date('Y-m-d', strtotime($subscription->expiry_date));
            $response['message'] = "success";
            // echo $current_date."==".$expire_date;
            if ($current_date > $expire_date) {
                $response['expiry'] = 1;
            } else {
                $response['expiry'] = 0;
            }

            return $response;
        }
    }
}
