<?php

function sendErrorToClient($error)
{
    $response = [];
    // $response['status'] = 0;
    $response['message'] = $error;
    return response($response, 400);
}

function sendMsgErrorToClient($error,$data=[])
{
    $response = [];
    $response['message'] = $error;
    if(!empty($data)){
        $response['data'] = $data;
    }
    return response($response, 422);
}

function setActivityLogs($log_info=[]){
   $arr = ['\App',"\Models","\ "];
   $model =  trim(implode($arr));
    $log_info['causer_type'] = $model.$log_info['causer_type'];
    $log_info['subject_type'] = $model.$log_info['subject_type'];
    App\Models\ActivityLog::create($log_info);
}

function stockActivityLogs($log_info=[]){
     App\Models\StockLog::create($log_info);
 }

function makeClientHappy($data = [], $msg = 'success',$key='info',$merger=[])
{
    $response = [];
    $response['message'] = $msg;
    $response['data'] = $data;
    if(!empty($merger)){
        $response[$key] = $merger;

    }
    return response($response, 200);

}

function getMonthTitle($months,$month_n,$month_before_dec){
    if($month_n == 12 && $month_before_dec==true){
        $month_title = $months;
     }else if($month_n < date('m')){
        $month_title = $months;
     }else{
        $month_title = $months;
        $month_title = array_reverse($month_title);
     }

    return trans("Months.".date('M',strtotime($month_title[0])))." ".trans("Years.".date('Y',strtotime($month_title[0])))." - ".trans("Months.".date('M',strtotime(end($month_title)))) ." ". trans("Years.".date('Y',strtotime(end($month_title))));
}

function daysLeft($date){
    $now  = \Carbon\Carbon::now();
    $DeferenceInDays = $now->diffInDays($date);
    return $DeferenceInDays;
}

function sendExpToClient($ex)
{
    $response = [];
    $response['message'] = $ex->getMessage();
    return response($response, 400);
}

function PagintionResponse($data , $msg = 'success')
{
    $result=array(
        // 'api_status'=>1,
        'message'=>$msg
    );

    $response = array_merge($result,$data);

    return response($response, 200);
}

function checkIfExist(String $table, String $findingColumn = 'id' ,String $matchingData)
{
    return \DB::table($table)->where($findingColumn,$matchingData)->first();
}

function getSalesCustomer($user_id)
{
    return \DB::select("SELECT DISTINCT(customer_name) AS customers_name,
    (SELECT COALESCE(SUM(CASE WHEN is_settled='0' THEN`remaining_amount` END),0) FROM sales WHERE customer_name=customers_name AND user_id='$user_id' AND status_id = '10' AND deleted_at IS NULL) AS amount
    FROM `sales` WHERE user_id='$user_id' AND status_id = '10' AND deleted_at IS NULL AND is_settled='0' ");
}

function getSalesCustomerWriteoff($user_id)
{
    return \DB::select("SELECT DISTINCT(customer_name) AS customers_name,
    (SELECT COALESCE(SUM(CASE WHEN is_settled='1' THEN`remaining_amount` END),0)  FROM sales WHERE customer_name=customers_name AND user_id='$user_id' AND status_id = '10' AND deleted_at IS NULL) AS amount
    FROM `sales` WHERE user_id='$user_id' AND status_id = '10' AND deleted_at IS NULL");
}

function getSalesCustomerWriteoffAmount($user_id)
{
    $responses =  \DB::select("SELECT DISTINCT(customer_name) AS customers_name,
    (SELECT COALESCE(SUM(CASE WHEN is_settled='1' THEN`remaining_amount` END),0)  FROM sales WHERE customer_name=customers_name AND user_id='$user_id' AND status_id = '10' AND deleted_at IS NULL) AS amount
    FROM `sales` WHERE user_id='$user_id' AND status_id = '10' AND deleted_at IS NULL");
    $amount = [];
    foreach($responses as $response){
        $amount[] = $response->amount;
    }
    return array_sum($amount);
}

function checkLocale($header){
    $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
    return $local;
}


function getOwnerAccount($user_id){
    return  \DB::select("SELECT DISTINCT(owner_name) AS owners_name,
    --  (SELECT SUM(`amount`) FROM owner_accounts WHERE owner_name=owners_name AND user_id='$user_id' AND deleted_at IS NULL) AS amount
    (SELECT SUM(`amount`) FROM owner_accounts WHERE owner_name=owners_name AND user_id='$user_id' AND deleted_at IS NULL AND status_id='13' ) AS inflow,
       (SELECT SUM(`amount`) FROM owner_accounts WHERE owner_name=owners_name AND user_id='$user_id' AND deleted_at IS NULL AND status_id='14') AS outflow
    FROM `owner_accounts` WHERE user_id='$user_id'  AND owner_name IS NOT NULL AND deleted_at IS NULL");

}


function getSubscription($table,$key,$value,$returnValue){
    $data = \DB::table($table)->where($key,$value)->first();
    return $data->$returnValue;
}



function payableAging($type,$user_id,$initial, $end='',$max = false){
    $current_date =date('Y-m-d');
    $AND ='';
    if($max == false){
        $AND .=" AND ABS(DATEDIFF(`date`, '$current_date')) <= $end";
    }

    if ($type == "expenses") {
        $return = \DB::select("SELECT DISTINCT vendor_name AS customer,
        (SELECT COALESCE(SUM(remaining_amount), 0)
        FROM   expenses
        WHERE  `status_id` = '11'
                AND `deleted_at` IS NULL
                AND `user_id` = '$user_id'
                AND `vendor_name` = customer
                AND ABS(DATEDIFF(`date`,'$current_date')) >= $initial " . $AND . "
                ) AS
                remaining_amount
            FROM   `expenses`
            WHERE  status_id = '11'
            AND is_settled = '0'
                AND deleted_at IS NULL
                AND user_id = '$user_id'
            GROUP  BY vendor_name ");
    }elseif($type == "all"){
        $expenses = \DB::select("SELECT DISTINCT vendor_name AS customer,
        (SELECT COALESCE(SUM(remaining_amount), 0)
        FROM   expenses
        WHERE  `status_id` = '11'
                AND `deleted_at` IS NULL
                AND `user_id` = '$user_id'
                AND `vendor_name` = customer
                AND ABS(DATEDIFF(`date`,'$current_date')) >= $initial " . $AND . "
                ) AS
                remaining_amount
            FROM   `expenses`
            WHERE  status_id = '11'
            AND is_settled = '0'
                AND deleted_at IS NULL
                AND user_id = '$user_id'
            GROUP  BY vendor_name ");

        $purchases = \DB::select("SELECT DISTINCT vendor_name AS customer,
        (SELECT COALESCE(SUM(remaining_amount), 0)
        FROM   purchases
        WHERE  `status_id` = '8'
                AND `deleted_at` IS NULL
                AND `user_id` = '$user_id'
                AND `vendor_name` = customer
                AND ABS(DATEDIFF(`date`,'$current_date')) >= $initial " . $AND . "
                ) AS
                remaining_amount
            FROM   `purchases`
            WHERE  status_id = '8'
            AND is_settled = '0'
                AND deleted_at IS NULL
                AND user_id = '$user_id'
            GROUP  BY vendor_name ");

        $return = array_merge($purchases, $expenses);
    }else{
        $return = \DB::select("SELECT DISTINCT vendor_name AS customer,
        (SELECT COALESCE(SUM(remaining_amount), 0)
        FROM   purchases
        WHERE  `status_id` = '8'
                AND `deleted_at` IS NULL
                AND `user_id` = '$user_id'
                AND `vendor_name` = customer
                AND ABS(DATEDIFF(`date`,'$current_date')) >= $initial " . $AND . "
                ) AS
                remaining_amount
            FROM   `purchases`
            WHERE  status_id = '8'
            AND is_settled = '0'
                AND deleted_at IS NULL
                AND user_id = '$user_id'
            GROUP  BY vendor_name ");
    }


    $array=[];
    foreach($return as $ret){

        if($ret->remaining_amount ==0){
            continue;
        }
        $array[] = $ret;
    }

    return $array;
}

function getDatesFromRange($start, $end, $format = 'Y-m-d') {


    // Declare an empty array
    $array = array();

    // Variable that store the date interval
    // of period 1 day
    $interval = new DateInterval('P1D');

    $realEnd = new DateTime($end);
    //$realEnd->add($interval);

    $period = new DatePeriod(new DateTime($start), $interval, $realEnd);
    // Use loop to store date into array
    foreach($period as $date) {
        $array[] = $date->format($format);
    }

    if($start===$end){
        return [$start,$end];
    }
    // Return the array elements

    return $array;
}
function RecordArabicLog($module,$update_id,$data,$recorded_by){
    \DB::table('activity_log')
    ->where('id', $update_id)
    ->update(['description_ar' => $data,'recorded_by'=>$recorded_by]);
}
function NegativeNumber($num){
    return ($num < 0 ? "(".abs($num).")" : $num);
}
function get_next_month($tstamp) {
    return (strtotime('+1 months', strtotime(date('Y-m-01', $tstamp))));
}

function get_month_between_two_datetime($start, $end){
    $start = $start=='' ? time() : strtotime($start);
    $end = $end=='' ? time() : strtotime($end);
    $months = array();

    for ($i = $start; $i <= $end; $i = get_next_month($i)) {
        $months[] = date('Y-m', $i);
    }

    return $months;
}

function receivableAging($user_id,$initial, $end='',$max = false){
    $current_date =date('Y-m-d');
    $AND ='';
    if($max == false){
        $AND .=" AND ABS(DATEDIFF(`date`, '$current_date')) <= $end";
    }
    $return =  \DB::select("SELECT DISTINCT customer_name AS customer,
    (SELECT COALESCE(SUM(remaining_amount), 0)
     FROM   sales
     WHERE  `status_id` = '10'
            AND `deleted_at` IS NULL
            AND `user_id` = '$user_id'
            AND `customer_name` = customer
            AND ABS(DATEDIFF(`date`,'$current_date')) >= $initial ".$AND."
            ) AS
            remaining_amount
        FROM   `sales`
        WHERE  status_id = '10'
        AND is_settled = '0'
            AND deleted_at IS NULL
            AND user_id = '$user_id'
        GROUP  BY customer_name ");
    $array=[];
    foreach($return as $ret){
        if($ret->remaining_amount ==0){
            continue;
        }
        $array[] = $ret;
    }

    return $array;
}


function totalAmount($user_id,$initial,$end,$max=false){
    $current_date =date('Y-m-d');
    $AND ='';
    if($max == false){
        $AND .=" AND ABS(DATEDIFF(`date`, '$current_date')) <= $end";
    }

    $return =  \DB::select("SELECT
                    SUM(remaining_amount) as remaining_amount
                    FROM `sales`
                    WHERE `status_id` = '10'
                    AND `deleted_at` IS NULL
                    AND is_settled='0'
                    AND `user_id` = '$user_id' AND ABS(DATEDIFF(`date`,'$current_date')) >= $initial ".$AND);

    return (!empty($return[0]->remaining_amount)) ? $return[0]->remaining_amount : 0;
}

function calculate_amount($data){
    $amount = [];
    if(!empty($data)){
        foreach($data as $d){
           $amount[] = $d->remaining_amount ? $d->remaining_amount : 0;
        }
        return array_sum($amount);
    }else{
        return 0;
    }
}

function totalAmountPayable($type,$user_id,$initial,$end,$max=false){
    $current_date =date('Y-m-d');
    $AND ='';
    if($max == false){
        $AND .=" AND ABS(DATEDIFF(`date`, '$current_date')) <= $end";
    }

    if ($type == "expenses") {
        $return = \DB::select("SELECT DISTINCT vendor_name AS customer,
        (SELECT COALESCE(SUM(remaining_amount), 0)
        FROM   expenses
        WHERE  `status_id` = '11'
                AND `deleted_at` IS NULL
                AND `user_id` = '$user_id'
                AND `vendor_name` = customer
                AND ABS(DATEDIFF(`date`,'$current_date')) >= $initial " . $AND . "
                ) AS
                remaining_amount
            FROM   `expenses`
            WHERE  status_id = '11'
            AND is_settled = '0'
                AND deleted_at IS NULL
                AND user_id = '$user_id'
            GROUP  BY vendor_name ");

    }else if($type == "all"){
        $expenses = \DB::select("SELECT DISTINCT vendor_name AS customer,
        (SELECT COALESCE(SUM(remaining_amount), 0)
        FROM   expenses
        WHERE  `status_id` = '11'
                AND `deleted_at` IS NULL
                AND `user_id` = '$user_id'
                AND `vendor_name` = customer
                AND ABS(DATEDIFF(`date`,'$current_date')) >= $initial " . $AND . "
                ) AS
                remaining_amount
            FROM   `expenses`
            WHERE  status_id = '11'
            AND is_settled = '0'
                AND deleted_at IS NULL
                AND user_id = '$user_id'
            GROUP  BY vendor_name ");

        $purchases = \DB::select("SELECT DISTINCT vendor_name AS customer,
        (SELECT COALESCE(SUM(remaining_amount), 0)
        FROM   purchases
        WHERE  `status_id` = '8'
                AND `deleted_at` IS NULL
                AND `user_id` = '$user_id'
                AND `vendor_name` = customer
                AND ABS(DATEDIFF(`date`,'$current_date')) >= $initial " . $AND . "
                ) AS
                remaining_amount
            FROM   `purchases`
            WHERE  status_id = '8'
            AND is_settled = '0'
                AND deleted_at IS NULL
                AND user_id = '$user_id'
            GROUP  BY vendor_name ");

        $return = array_merge($purchases, $expenses);
    }else{
        $return = \DB::select("SELECT DISTINCT vendor_name AS customer,
        (SELECT COALESCE(SUM(remaining_amount), 0)
        FROM   purchases
        WHERE  `status_id` = '8'
                AND `deleted_at` IS NULL
                AND `user_id` = '$user_id'
                AND `vendor_name` = customer
                AND ABS(DATEDIFF(`date`,'$current_date')) >= $initial " . $AND . "
                ) AS
                remaining_amount
            FROM   `purchases`
            WHERE  status_id = '8'
            AND is_settled = '0'
                AND deleted_at IS NULL
                AND user_id = '$user_id'
            GROUP  BY vendor_name ");
    }

         $array=[];
        $total_amount=0;
        if(!empty($return)){
            foreach($return as $ret){
                $array[] = $ret;
                $key = 'remaining_amount';
                $array['amount'] = array_sum(array_column($array,$key));
            }
            $total_amount =  $array['amount'];
        }

        return $total_amount;
    // return (!empty($return[0]->remaining_amount)) ? $return[0]->remaining_amount : 0;
}

function getData(String $table, String $findingColumn = 'id' ,String $matchingData){
    return \DB::table($table)->where($findingColumn,$matchingData)->first();
}

function getParentId(String $table, String $findingColumn = 'id' ,String $matchingData){
    return \DB::table($table)->select('parent')->where($findingColumn,$matchingData)->first()->parent;
}
function xgetId(String $table){
    $id =  \DB::select("SELECT id FROM $table ORDER BY id DESC LIMIT 1");
    if(isset($id[0]))
        return $id[0]->id;
    else
        return 0;
}

function getChildrenId(String $table,$user_id){
    $count = \DB::select("SELECT COUNT(*) AS total_count FROM $table WHERE user_id = $user_id");

    if(isset($count[0]))
        return $count[0]->total_count + 1;
    else
        return 0;
}

function getId(String $table,$user_id){
    $count =  \DB::select("SELECT COUNT(*) AS total_count FROM $table WHERE user_id = $user_id");

    if(isset($count[0]))
        return $count[0]->total_count + 1;
    else
        return 0;
}

function sumOfSubscriber($user_id){
    $count =  \DB::select("SELECT SUM(total_amount) AS total_amount FROM user_subscriptions WHERE user_id = $user_id");
    if(isset($count[0]))
        return $count[0]->total_amount;
    else
        return 0;
}

function removeArrayKey($variables){
    unset($variables);
}

function authyResponse($data , $msg = 'success')
{
    $result = [];
    // $result['api_status'] = 1;
    $result['message'] = $msg;
    $result['data'] = $data;
    return $result;
}

function pageResponse($data , $msg = 'success',$data_params='data')
{
    $result = [];
    // $result['api_status'] = 1;
    $result['message'] = $msg;
    $result[$data_params] = $data;
    return $result;
}

function sendMsgToClient($msg)
{
    $result = [];
    $result['message'] = $msg;

    return $result;

}

function uploadFile($file, $user_id = null, $encrypt = false)
{
    $src = "";
    $thumbnail = "";
    $file_size = "";
    $basename = str_slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
    $ext = $file->getClientOriginalExtension();

    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'bmp', 'pdf', 'doc', 'docx'])) {
        $file_size_kb = $file->getSize() / 1024;
        $file_size = ($file_size_kb > 1024 ? (round($file_size_kb / 1024, 2)) . ' MB' : (round($file_size_kb, 2)) . ' KB');
        $file_name = ($encrypt == true ? md5(str_random(5)) : time() . $basename) . '.' . $ext;
        $file_path = 'uploads/' . $user_id;
        //Create Directory Monthly
        Storage::makeDirectory($file_path);
        if (Storage::putFileAs($file_path, $file, $file_name)) {
            $src = $file_path . '/' . $file_name;

            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'bmp'])) {
                //Create Thumbnail
                $img = Image::make($file);

                $img->resize(null, 200, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $resource = $img->stream()->detach();
            }
        }
    }

    return ['source' => $src, 'thumbnail' => $thumbnail, 'filename' => $file->getClientOriginalName(), 'filesize' => $file_size];
}

function checkData(String $table, array $whereData, $extendedQuery = false, $limit = false, $offset = false, $count = false)
{
    $query = \DB::table($table);

    if (Schema::hasColumn($table, 'password')) {
        $query->select(array_diff(Schema::getColumnListing($table), ['password']));
    }

    if ($whereData) {
        $query->where($whereData);
    }

    if ($extendedQuery) {
        $query->select($extendedQuery);
    }

    if ($count) {
        return $query->count();
    }

    if ($offset) {
        $value = $query->skip($offset);
    }

    if ($limit) {
        $value = $query->take($limit)->get();
    } else {
        if ($query->count() === 1) {
            $value = $query->first();
        } else if ($query->count() > 1) {
            $value = $query->get();
        } else {
            $value = false;
        }
    }

    return $value;

}


function uploadCustomFile($file)
{
    $basename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
    $ext = $file->getClientOriginalExtension();
    $name = time() . $basename . "." . $ext;
    $file->move(public_path() . '/files/', $name);
    return '/files/' . $name;
}

function setText($string, $singular = false)
{

    $string = ucwords(str_replace("_", " ", $string));

    if ($singular) {
        $string = Str::singular($string);
    }

    return $string;
}

function getSingleRelationDataForPDF($row, $row_key)
{
    $single_relationship = explode('.', $row_key);

    if (count($single_relationship) > 1) {
        return $row->{$single_relationship[0]}->{$single_relationship[1]};
    }

    return $row->{$single_relationship[0]};
}


