<?php

/**
 * Description: Library to integrate Rocket Chat
 */

namespace App\Libraries;


use Exception;

class TapPayment
{


    private $apiURL = 'https://api.tap.company/v2/';

    private $apiEndPoints = [
        'charges' => 'charges',
        'card' => 'card',
        'customers' =>'customers',
        'tokens' =>'tokens',
        'payment_agreements' =>'payment_agreements'
    ];


    public function PostRequest($slug, $postdata)
    {

        try { //Send CURL Request to API

            $curl = curl_init();
            $json = json_encode($postdata);
            // dd($json );
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->apiURL.$this->apiEndPoints[$slug],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => array(
                    "authorization: ".env('TAP_GATEWAY_SANDBOX'),
                    "content-type: application/json"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                return $response;
            }

        } catch (\Exception $e) {
            //handle exception
            throw new Exception($e->getMessage());
        }
    }

    public function deleteRequest($url)
    {
        try { //Send CURL Request to API

            $curl = curl_init();
            // dd($json );
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->apiURL.$url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "DELETE",
                CURLOPT_HTTPHEADER => array(
                    "authorization: ".env('TAP_GATEWAY_SANDBOX'),
                    "content-type: application/json"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $json_data = json_decode($response);
                return $json_data;
            }

        } catch (\Exception $e) {
            //handle exception
            throw new Exception($e->getMessage());
        }
    }

    public function customRequest($url, $postdata)
    {
        try { //Send CURL Request to API

            $curl = curl_init();
            $json = json_encode($postdata);
            // dd($json );
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->apiURL.$url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => array(
                    "authorization: ".env('TAP_GATEWAY_SANDBOX'),
                    "content-type: application/json"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $json_data = json_decode($response);
                return $json_data;
            }

        } catch (\Exception $e) {
            //handle exception
            throw new Exception($e->getMessage());
        }
    }

    public function rcCurlGetRequest($slug,$param){
        try { //Send CURL Request to API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiURL . $this->apiEndPoints[$slug].'/'.$param);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "authorization: ".env('TAP_GATEWAY_SANDBOX'),
                "content-type: application/json"
            ));

            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                return "cURL Error #:" . $err;
            } else {
                $json_data = json_decode($response);
                return $json_data;
            }

        } catch (\Exception $e) {
            //handle exception
            throw new Exception($e->getMessage());
        }
    }


    /**
     * @return cURL response
     */
    public function sendCurlRequest($url, $postdata = "")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($postdata != "") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result);
        return $response;
    }

    public function postPaymentData($data,$token,$endpoint){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            // CURLOPT_POSTFIELDS => array('token' => 'card_2gfC53221231XGok3va21509','subscription_id' => '9','amount' => '27','currency' => 'KWD','description' => 'Bruno Mueller 123','country_code' => '965','phone' => '2535884854','is_recur' => '0'),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-Access-Token: '.$token
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }


}
