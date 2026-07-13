<?php

namespace App\Libraries;

use Stripe\Account;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\WebhookEndpoint;

class StripePayment
{
    public static function getApiKey()
    {
        return env('STRIPE_PAYMENT_KEY');
    }

    public static function create_customer($data)
    {
        Stripe::setApiKey(self::getApiKey());
        return Customer::create(['email' => $data['email']]);
    }

    public static function listCustomerCards($customer_id)
    {
        Stripe::setApiKey(self::getApiKey());
        return Customer::allSources($customer_id, ['object' => 'card', 'limit' => 20]);
    }

    //Create Customer on stripe
    public static function createCustomerCard($stripe_id, $token)
    {
        Stripe::setApiKey(self::getApiKey());
        return Customer::createSource($stripe_id, ['source' => $token]);
    }

    //Update Customer Card
    public static function editCustomerCard($customer_id, $card_id, $params)
    {
        Stripe::setApiKey(self::getApiKey());
        return Customer::updateSource($customer_id, $card_id, $params);
    }

    //Delete Customer Card
    public static function deleteCustomerCard($customer_id, $card_id)
    {
        Stripe::setApiKey(self::getApiKey());
        return Customer::deleteSource($customer_id, $card_id);
    }

    //set Default Card
    public static function setDefaultCard($customer_id, $card_id)
    {
        Stripe::setApiKey(self::getApiKey());
        return Customer::update($customer_id, [
            'default_source' => $card_id
        ]);
    }

    //Create External Account on stripe
    public static function createAccount($data)
    {
        Stripe::setApiKey(self::getApiKey());
        $first_name = is_null($data['user']->name) ? config('app.stripe_dummy_data.first_name') : $data['user']->name;
        $last_name = is_null($data['user']->name) ? config('app.stripe_dummy_data.last_name') : $data['user']->name;
        $email = is_null($data['user']->email) ? config('app.stripe_dummy_data.email') : $data['user']->email;
        $dob_date = is_null($data['user']->dob_date) ? config('app.stripe_dummy_data.dob_date') : $data['user']->dob_date;
        $dob_month = is_null($data['user']->dob_month) ? config('app.stripe_dummy_data.dob_month') : $data['user']->dob_month;
        $dob_year = is_null($data['user']->dob_year) ? config('app.stripe_dummy_data.dob_year') : $data['user']->dob_year;

        return Account::create(
            [
                'type' => config('app.account_type'),
                'business_type' => config('app.business_type'),
                'country' => config('app.country'),
                'email' => $data['user']->email,
                'capabilities' => [
                    'transfers' => ['requested' => true]
                ],
                'individual'=>[
                    'first_name'=>$first_name,
                    'last_name'=>$last_name,
                    'email'=> $email,
                    'dob'=>[
                        'day'=>$dob_date,
                        'month'=>$dob_month,
                        'year'=>$dob_year
                    ],
                    'ssn_last_4'=>$data['ssn']
                ],
                'business_profile' => ['name' => $first_name, 'url' => config('app.stripe_url'),'mcc'=>'5734'],
                'external_account' => [
                    'object' => config('app.external_account_object'),
                    'country' => config('app.country'),
                    'currency' => config('app.currency'),
                    'account_holder_name' => $data['account_holder_name'],
                    'account_holder_type' => config('app.account_holder_type'),
                    'account_number' => $data['account_number'] , //request
                    'routing_number' => $data['routing_number'] //request
                ],
                'tos_acceptance' => [
                    'date' => time(),
                    'ip' => app('request')->ip(), // Assumes you're not using a proxy
                ],
            ]
        );;
    }


    public static function updateAccount($data)
    {
        Stripe::setApiKey(self::getApiKey());
        return Account::update(
            $data['user']->connect_stripe_id,
            [
                'business_type' => config('app.business_type'),
                'email' => $data['user']->email,
                'capabilities' => [
                    'transfers' => ['requested' => true]
                ],
                'individual'=>[
                    'first_name'=>$data['user']->first_name,
                    'last_name'=>$data['user']->last_name,
                    'email'=> $data['user']->email,
                    'dob'=>[
                        'day'=>$data['dob_date'],
                        'month'=>$data['dob_month'],
                        'year'=>$data['dob_year']
                    ],
                    'ssn_last_4'=>$data['ssn']
                ],
                'business_profile' => ['name' => $data['user']->name, 'url' => config('app.stripe_url'),'mcc'=>'5734'],
                'external_account' => [
                    'object' => config('app.external_account_object'),
                    'country' => config('app.country'),
                    'currency' => config('app.currency'),
                    'account_holder_name' => $data['account_holder_name'],
                    'account_holder_type' => config('app.account_holder_type'),
                    'account_number' => $data['account_number'], //request
                    'routing_number' => $data['routing_number'] //request
                ],
                'tos_acceptance' => [
                    'date' => time(),
                    'ip' => app('request')->ip(), // Assumes you're not using a proxy
                ],
            ]
        );
    }

    public static function getAccount($customer_stripe_id){
        Stripe::setApiKey(self::getApiKey());
        return Account::retrieve(
            $customer_stripe_id,
            []
        );
    }

    //Add bank to connected(external) account
    public static function createBankToken($bank_data)
    {
        Stripe::setApiKey(self::getApiKey());
        return Token::create(['bank_account' => $bank_data]);
    }

    //Add bank to connected(external) account
    public static function addBankAccount($bank_data, $account_id)
    {
        Stripe::setApiKey(self::getApiKey());
        return Account::createExternalAccount($account_id, ['external_account' => $bank_data]);
    }

    //List External Accounts
    public static function listExternalAccounts($account_id, $object)
    {
        Stripe::setApiKey(self::getApiKey());
        return Account::allExternalAccounts($account_id, ['object' => $object, 'limit' => 20]);
    }

    //Update External Accounts
    public static function updateExternalAccount($account_id, $bank_id, $params)
    {
        Stripe::setApiKey(self::getApiKey());
        return Account::updateExternalAccount($account_id, $bank_id, $params);
    }

    //Delete External Account
    public static function deleteExternalAccount($account_id, $bank_id)
    {
        Stripe::setApiKey(self::getApiKey());
        return Account::deleteExternalAccount($account_id, $bank_id);
    }

    //List all connected accounts
    public static function getAllConnectedAccountsList()
    {
        Stripe::setApiKey(self::getApiKey());
        return Account::all();
    }

    //Payout to connected account
    public static function payout($pay_data)
    {
        Stripe::setApiKey(self::getApiKey());
        return Payout::create($pay_data);
    }

    //Transfer money to connected account
    public static function transferMoney($transfer_data)
    {
        Stripe::setApiKey(self::getApiKey());
        return Transfer::create($transfer_data);
    }


    public function setPaymentTransfer($data){

        Stripe::setApiKey(self::getApiKey());
        $transfer = \Stripe\Transfer::create([
            'amount' => $data['transfer_amount'],
            'currency' => 'usd',
            'destination' => $data['vendor_connected_id'],
            'transfer_group' => $data['transfer_group'],
            'source_transaction'=>$data['source_transaction']
        ]);

        return $transfer;
    }

    //Set Payment Intent
    public static function setIntent($stripe_id,$payment_details,$destination_id)
    {
        Stripe::setApiKey(self::getApiKey());

        // $app_fees = percentageOff($payment_details['amount']);
        // $app_fees = number_format($app_fees * 100 , 0 , '' , '');
        // $payment_details['amount'] = $payment_details['amount'] + ($payment_details['amount'] * config('constant.transaction_fee_percent')) + config('constant.stripe_fee');
        $payment_details['amount'] = number_format($payment_details['amount'] * 100 , 0 , '' , '');
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $payment_details['amount'],
            'currency' => config('app.currency'),
            'payment_method_types' => ['card'],
            'metadata'=>[
                'order_id'=>$payment_details['id'],
                'vendor_connected_id'=>$destination_id,
                'transfer_amount'=> $payment_details['amount']
            ],
            'customer'=>$stripe_id['id'],
            'transfer_group' => "Order_".uniqid(),
        ]);

        $ephemeralKey = \Stripe\EphemeralKey::create(
            ['customer' => $stripe_id['id']],
            ['stripe_version' => '2020-08-27']
        );

        return compact('paymentIntent','ephemeralKey');
    }

    public static function setEphemeral($customer_id, $amount)
    {
        Stripe::setApiKey(self::getApiKey());
        $ephemeralKey = \Stripe\EphemeralKey::create(
            ['customer' => $customer_id],
            ['stripe_version' => '2020-08-27']
        );
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount * 100,
            'currency' => 'usd',
            'customer' => $customer_id
        ]);
        $response = [
            'paymentIntent' => $paymentIntent->client_secret,
            'ephemeralKey' => $ephemeralKey->secret,
            'customer' => $customer_id
        ];
        return $response;
    }

    //Create Charge
    public static function charge($stripe_data)
    {
        Stripe::setApiKey(self::getApiKey());
        $response = Charge::create($stripe_data);

        return $response;
    }

    public function deletePaymentIntent($payment_intent_id){


        $stripe = new \Stripe\StripeClient(
            self::getApiKey()
        );
        return $stripe->paymentIntents->cancel(
            $payment_intent_id,
            []
        );

    }


    //Create Webhook
    public function createWebhook($url,$events){
        Stripe::setApiKey(self::getApiKey());

        return WebhookEndpoint::create([
            'url'=>$url,
            'enabled_events'=>$events,

        ]);

    }


}
