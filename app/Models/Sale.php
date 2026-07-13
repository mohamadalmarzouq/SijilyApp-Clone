<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Purchase;
use App\Models\Expense;
class Sale extends Model
{
    use SoftDeletes;
    protected $fillable = ['date','last_trans_update','sub_category_name_ar', 'desc','is_settled', 'customer_id','customer_name', 'amount', 'sys_gen_id', 'status_id', 'user_id','received_amount','remaining_amount','sub_category_id','sub_category_name','recorded_by'];

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function getSales($id)
    {
        return $this->with(['Image','getTransaction.Image'])->findOrFail($id);
    }

    public function getTransaction(){
        return $this->hasMany(Transaction::class, 'ref_id', 'id')->with('Image')->where('type','sale')->where('amount','!=',0)->whereNull('deleted_at');
    }

    public function receivables($request,$limit){

        $data=[];

        $results = $this->with(['Image','status','getTransaction.Image'])
                        ->where("status_id",10)
                        ->whereNull('deleted_at');

        if(isset($request['start_date']) && isset($request['end_date'])){
            $results->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
        }else if(isset($request['start_date'])){
            $results->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
        }else if(isset($request['end_date'])){
            $results->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
        }

        if(isset($request['ageing']) && $request['ageing'] == 1){
            $results->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))>=0 AND  ABS(DATEDIFF(`date`,'".date('Y-m-d')."')) <=30 ");
        }else if(isset($request['ageing']) && $request['ageing'] == 2){
            $results->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))>=30 AND  ABS(DATEDIFF(`date`,'".date('Y-m-d')."')) <=60 ");
        }else if(isset($request['ageing']) && $request['ageing'] == 3){
            $results->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))>=60 AND  ABS(DATEDIFF(`date`,'".date('Y-m-d')."')) <=180 ");
        }else if(isset($request['ageing']) && $request['ageing'] == 4){
            $results->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))>=180 AND  ABS(DATEDIFF(`date`,'".date('Y-m-d')."')) <=360 ");
        }else if(isset($request['ageing']) && $request['ageing'] == 5){
            $results->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))> 360");
        }

        if(isset($request['customer_name'])){
            $results->where("customer_name","LIKE",'%'.$request['customer_name'].'%');
        }

        if(isset($request['recorded_by'])){
            $results->where("recorded_by",$request['recorded_by']);
        }
        if(isset($request['search'])){
            $results->where("desc","LIKE",'%'.$request['search'].'%');
        }

        if(isset($request['is_settled'])){
            $results->where("is_settled",$request['is_settled']);
        }

        // echo $results->toSql();
        // die();

        $result = $results->where('user_id', $request['user_id'])->orderBy('id','desc')->paginate($limit)->toArray();
        $res=[];
        foreach($result['data'] as $key=> $d){
            $d['sub_category_name_ar'] = trans("categories.".str_replace(" ",'_',strtolower($d['sub_category_name_ar'])));
            $d['sub_category_name'] = trans("categories.".str_replace(" ",'_',strtolower($d['sub_category_name'])));
            $res[] = $d;
        }
        $fulldata['data']  = $res;

        //get total amount


        // $fulldata['customers'] = getSalesCustomer($request['user_id']);
        // $fulldata['total']= $this->where('user_id',$request['user_id'])->where('status_id',10)->whereNull('deleted_at')->sum('remaining_amount');
        $data['page'] =  $result;
        unset($data['page']['data']);
        unset($fulldata['data']['message']);
        $new_row = array_merge($fulldata,$data);
        return $new_row;
    }

    public function apiListing($request,$user_id, $limit)
    {
        $data=[];
        $total_amount=$this->whereNull('deleted_at');
        $received_amount=$this->whereNull('deleted_at');
        $not_received_amount=$this->whereNull('deleted_at');

        $results = $this->with(['Image','getTransaction.Image'])
            ->whereNull('deleted_at');
        if(isset($request['start_date']) && isset($request['end_date'])){
            $results->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $total_amount->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $received_amount->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $not_received_amount->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
        }else if(isset($request['start_date'])){
            $results->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $total_amount->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $received_amount->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $not_received_amount->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
        }else if(isset($request['end_date'])){
            $results->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $total_amount->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $received_amount->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $not_received_amount->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
        }
        if(isset($request['search'])){
            $results->where("desc","LIKE",'%'.$request['search'].'%');
            $total_amount->where("desc","LIKE",'%'.$request['search'].'%');
            $received_amount->where("desc","LIKE",'%'.$request['search'].'%');
            $not_received_amount->where("desc","LIKE",'%'.$request['search'].'%');
        }

        if(isset($request['status_id'])){
            $results->where("status_id",$request['status_id']);
            $total_amount->where("status_id",$request['status_id']);
            $received_amount->where("status_id",$request['status_id']);
            $not_received_amount->where("status_id",$request['status_id']);
        }

        if(isset($request['recorded_by'])){
            $results->where("recorded_by",$request['recorded_by']);
            $total_amount->where("recorded_by",$request['recorded_by']);
            $received_amount->where("recorded_by",$request['recorded_by']);
            $not_received_amount->where("recorded_by",$request['recorded_by']);
        }

        $result = $results->where('user_id', $user_id)->orderBy('id','desc')->paginate($limit)->toArray();

        $res =[];
        foreach($result['data'] as $key=> $d){
            $d['sub_category_name_ar'] = trans("categories.".str_replace(" ",'_',strtolower($d['sub_category_name_ar'])));
            $d['sub_category_name'] = trans("categories.".str_replace(" ",'_',strtolower($d['sub_category_name'])));
            $res[] = $d;
        }

        $final_response = [];
        // foreach($res as $re){
        //     if(isset($re->get_transaction)){
        //         foreach($re->get_transaction as $get_tran){

        //         }
        //     }
        // }

        $fulldata['data']  = $res;

        $total = $total_amount->where('user_id',$user_id)->sum('amount');
        $recevied = $received_amount->where('user_id',$user_id)->sum('received_amount');
        $not_received = $not_received_amount->where('user_id',$user_id)->where('is_settled','0')->sum('remaining_amount');


        //get total amount
        $widgets['total_amount'] = $total;// $this->where('user_id',$user_id)->whereNull('deleted_at')->sum('amount');
        $widgets['received_amount'] = $recevied ;//  $this->where('user_id',$user_id)->whereNull('deleted_at')->sum('received_amount');
        $widgets['not_received_amount'] = $not_received;//$this->where('user_id',$user_id)->whereNull('deleted_at')->sum('remaining_amount');

        $fulldata['info'] = $widgets;
        $data['page'] =  $result;
        unset($data['page']['data']);
        unset($fulldata['data']['message']);
        $new_row = array_merge($fulldata,$data);
        return $new_row;
    }

    private function getImages($id){
        return ImageTransaction::where('transaction_id',$id)->get();
    }

    public function searchSales($request, $limit)
    {
        $sale = $this->with(['status', 'user'])->where('user_id', $request['user_id']);

        if (!empty($request['date'])) {
            $sale = $sale->where('date', $request['date']);
        }

        if (!empty($request['status_id'])) {
            $sale = $sale->where('status_id', $request['status_id']);
        }

        if (!empty($request['recorded_by_id'])) {
            $sale = $sale->where('user_id', $request['recorded_by_id']);
        }

        return $sale->paginate($limit)->toArray();
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }

    public function pending()
    {
        return $this->morphOne(Pending::class, 'draftable');
    }

    public function Images(){
        return $this->hasMany(ImageTransaction::class, 'transaction_id', 'id');
    }

    public function Image(){
        return $this->hasMany(Upload::class, 'model_ref_id', 'id')->where('model_name','sales');
    }

    public function getScheduleCustomers($user_id){
        $data=[];
        $customers_writeoff=[];
        $data['customers'] =  getSalesCustomer($user_id);
        $data['total'] =  $this->where('user_id',$user_id)->where('status_id',10)->where('is_settled','0')->whereNull('deleted_at')->sum('remaining_amount');
        $writeoff = getSalesCustomerWriteoff($user_id);
        // if($writeoff[0]->amount > 0){
        if(!empty($writeoff)){
            foreach($writeoff as $off){
              if($off->amount > 0)
                $customers_writeoff[] =  ["customers_name"=>$off->customers_name,"amount"=>$off->amount];
            }
        }

        $data['customers_writeoff']= $customers_writeoff;
        $data['written_off_total'] =  getSalesCustomerWriteoffAmount($user_id);
        // }
        return $data;
    }

    public function getCashIn($request){
        $results = Transaction::where('user_id', $request['user_id'])
        ->where('type','sale')
        ->whereNull('deleted_at');

        if(isset($request['from']) && isset($request['to'])){
            $results->whereBetween("date",[date('Y-m-d',strtotime($request['from'])),date('Y-m-d',strtotime($request['to']))]);
        }else if(isset($request['from'])){
            $results->whereDate("date",">=",date('Y-m-d',strtotime($request['from'])));
        }else if(isset($request['to'])){
            $results->whereDate("date","<=",date('Y-m-d',strtotime($request['to'])));
        }
        $response = $results->sum('amount');



        $startDate = $request['from'];
        $endDate = $request['to'];
        $user_id = $request['user_id'];
        $column = 'amount';

        $owner_account = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$startDate."' AND '".$endDate."'
        THEN $column
        END), 0) AS cash_in
        FROM `owner_accounts`
        WHERE  user_id = '$user_id'
        AND `status_id` = '13'
            AND deleted_at IS NULL");

        //Sabir Plumber
         return $response + $owner_account[0]->cash_in;
    }

    public function report($request){
        $final =[];
        $amount = [];
        if(isset($request['aging']) && $request['aging']== 1){
            $first[]=[
                '0-30'=>receivableAging($request['user_id'],0,30),
                'amount'=>totalAmount($request['user_id'],0,30,false),
                // 'date'=>receivableAgingDate($request['user_id'],0,30,false)
            ];
            $response = array_merge($final,$first);
            $amount = totalAmount($request['user_id'],0,30,false);
        }else if(isset($request['aging']) && $request['aging']== 2){
            $second[]=[
                '30-60'=>receivableAging($request['user_id'],30,60),
                'amount'=>totalAmount($request['user_id'],30,60,false),
                // 'date'=>receivableAgingDate($request['user_id'],30,60,false)
            ];
            $response =array_merge($final,$second);
            $amount = totalAmount($request['user_id'],30,60,false);
        }else if(isset($request['aging']) && $request['aging']== 3){
            $third[]=[
                '60-180'=>receivableAging($request['user_id'],60,180),
                'amount'=>totalAmount($request['user_id'],60,180,false)
            ];
            $response =array_merge($final,$third);
            $amount = totalAmount($request['user_id'],60,180,false);
        }else if(isset($request['aging']) && $request['aging']== 4){
            $fourth[]=[
                '180-360'=>receivableAging($request['user_id'],180,360),
                'amount'=>totalAmount($request['user_id'],180,360,false)
            ];
            $response =array_merge($final,$fourth);
            $amount = totalAmount($request['user_id'],180,360,false);
        }else if(isset($request['aging']) && $request['aging']== 5){
            $fifth[]=[
                '360+' =>receivableAging($request['user_id'],360,'',true),
                'amount'=>totalAmount($request['user_id'],360,'',true)
            ];
            $response =array_merge($final,$fifth);
            $amount = totalAmount($request['user_id'],360,'',true);
        }else{
            $first[] = [
                '0-30'=>receivableAging($request['user_id'],0,30),
                'amount'=>calculate_amount(receivableAging($request['user_id'],0,30)) //totalAmount($request['user_id'],0,30,false)
            ]; //Aging Starts from 0 to 30

             $second[]  = [
                '30-60'=>receivableAging($request['user_id'],30,60),
                'amount'=>calculate_amount(receivableAging($request['user_id'],30,60)) //totalAmount($request['user_id'],30,60,false)
            ]; //Aging Starts from 30 to 60
            $third[]  = [
                '60-180'=>receivableAging($request['user_id'],60,180),
                'amount'=>calculate_amount(receivableAging($request['user_id'],60,180)) //totalAmount($request['user_id'],60,180,false)
            ]; //Aging Starts from 60 to 180

             $fourth[]  = [
                '180-360'=> receivableAging($request['user_id'],180,360),
                'amount'=>calculate_amount(receivableAging($request['user_id'],180,360))//totalAmount($request['user_id'],180,360,false)
            ]; //Aging Starts from 180 to 360
             $fifth[]  = [
                '360+'=> receivableAging($request['user_id'],360,'',true),
                'amount'=>calculate_amount(receivableAging($request['user_id'],360,'',true))// totalAmount($request['user_id'],360,'',true)
            ]; //Aging Starts from 360+ onward
            $response =array_merge($final,$first,$second,$third,$fourth,$fifth);


            if(!empty($response)){
                $count = 0;
                $aging= ['0-30','30-60','60-180','180-360','360+'];
                foreach($response as $key => $resp){

                    $arrayKey = array_keys($resp)[0];
                    if(in_array($arrayKey,$aging)){
                        $agingValue = explode("-",$arrayKey);

                        $final[] = $resp;

                        if($arrayKey == "360+"){
                            $start = 361;
                            $end = '';
                        }else{
                            $start = (isset($agingValue[0])) ? $agingValue[0] : '';
                            $end = (isset($agingValue[1])) ? $agingValue[1] : '';
                        }

                         $is_true = ($count == 4) ? true: false;
                    //     //$final[]['amount'] = totalAmount($request['user_id'],$start,$end,$is_true);
                        $amount[] = calculate_amount(receivableAging($request['user_id'],$start,$end,$is_true));
                        // $amount[] = totalAmount($request['user_id'],$start,$end,$is_true);
                    }
                    $count++;

                }
                $response= $final;
            }
       }

       $res['customers'] = $response;
       $total['total_amount'] =(is_array($amount))? array_sum($amount) : $amount;
       $response = array_merge($res,$total);

     return $response;
    }

    public function getFiscalYearMonthData($year,$firstDate,$lastDate,$month,$label,$user_id){

        $where = '';
        if($firstDate){
            $where .=" `date` BETWEEN '$firstDate' AND '$lastDate' AND MONTH(`date`)='$month' AND YEAR(`date`)='$year'";
        }

        return \DB::select("SELECT COALESCE(SUM(CASE WHEN $where THEN amount END) ,0) AS '$label' FROM `sales` WHERE user_id='$user_id' /* AND `type` = 'sale' */ AND deleted_at IS NULL");
        // return \DB::select("SELECT COALESCE(SUM(CASE WHEN $where THEN amount END) ,0) AS '$label' FROM `transactions` WHERE user_id='$user_id' AND `type` = 'sale' AND deleted_at IS NULL");
    }

    public function getFiscalYearData($year,$month,$user_id,$request){
        $where = '';
        if(isset($request['start_date']) && isset($request['end_date'])){
            $start = $request['start_date'];
            $end_date = $request['end_date'];
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
        }
        return \DB::select("SELECT COALESCE(SUM(CASE WHEN YEAR(`date`) = '$year' THEN amount END) ,0)AS '$year' FROM `transactions`
         WHERE user_id='$user_id' AND `type` = 'sale' AND deleted_at IS NULL");
    }

    public function getFiscalYearMonthSearchData($month_name,$month,$year,$user_id,$request=[]){
        $AND='';
        if(isset($request['start_date']) && isset($request['end_date'])){
            $start_date = $request['start_date'];
            $end_date = $request['end_date'];
            $AND .= " AND `date` BETWEEN '$start_date' AND '$end_date'";
        }
        return \DB::select("SELECT Coalesce(Sum(case when MONTH(`date`)='$month' AND YEAR(`date`)='$year' AND user_id='$user_id' then amount end), 0)AS $month_name
                        FROM   `sales`
                        WHERE  user_id = '$user_id'
                        AND deleted_at IS NULL #$AND
                        ");
    }

    public function getFiscalYearQuarterData($first_array,$last_array,$label,$user_id,$request,$year){
        $onlyMonth = date('m',strtotime($last_array[0]));


        $first= $first_array[1]."-".$first_array[0]."-01";
        $last= $last_array[1]."-".$onlyMonth."-".cal_days_in_month(CAL_GREGORIAN, $onlyMonth, $last_array[1]);

        $first = date('Y-m-d',strtotime($first));
        $last    = date('Y-m-d',strtotime($last));



        $label = str_replace("-","_",$label);

        $where = '';
        if(isset($request['start_date']) && isset($request['end_date'])){
            $start = $request['start_date'];
            $end_date = $request['end_date'];
            $start_month = date('m',strtotime($request['start_date']));
            $end_month = date('m',strtotime($request['end_date']));
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
        }
       return \DB::select("SELECT COALESCE(SUM(CASE WHEN `date` BETWEEN '".$first."' AND '".$last."' THEN amount END) ,0)AS $label FROM  `transactions`
       WHERE user_id='$user_id' AND `type` = 'sale' AND deleted_at IS NULL");
    }


    public function api($year,$month,$user_id){
        $AND='';

        if($month !="12" && $month > date('m')  ){
            $AND .= ' AND MONTH(`date`) <='.$month;
        }else if($month !="12" && $month < date('m')  ){
            $AND .= ' AND MONTH(`date`) >='.$month;
        }

       return \DB::select("SELECT COALESCE(SUM(CASE WHEN YEAR(`date`) = '".$year."' $AND THEN amount END) ,0)AS '$year' FROM  `sales`
       WHERE user_id='$user_id' AND deleted_at IS NULL");
    }

    public function fetchCashIn($year,$month,$user_id,$firstDate,$lastDate,$column='amount',$cashBaseIncome){
        $data=[];

        $where = '';
        $AND = '';
        if($firstDate){
            // AND MONTH(`date`)='$month' AND YEAR(`date`)='$year'
            $where .=" MONTH(`date`)='$month' AND YEAR(`date`)='$year'";
            $AND .=" AND `date` BETWEEN '$firstDate' AND '$lastDate'";
        }

        $sales = \DB::select("SELECT COALESCE(SUM(CASE WHEN $where THEN $column END), 0) AS cash_in FROM `transactions`
        WHERE `type` = 'sale'
        AND  user_id = '$user_id'
               AND deleted_at IS NULL $AND ");
         if(!$cashBaseIncome)
        {
            $owner_account = \DB::select("SELECT COALESCE(SUM(CASE WHEN $where THEN $column END), 0) AS cash_in FROM `owner_accounts`
                WHERE `status_id` = '13'
                AND  user_id = '$user_id'
                    AND deleted_at IS NULL $AND ");

                $caseIn = $sales[0]->cash_in +  $owner_account[0]->cash_in;
         }else
        {


            $caseIn = $sales[0]->cash_in;
        }

        return $caseIn;
    }

    public function fetchCashOut($year,$month,$user_id,$firstDate,$lastDate,$column='amount',$single=false,$cashBaseIncome){

        $data=[];
        $where = '';
        $AND = '';
        if($firstDate){
            // AND MONTH(`date`)='$month' AND YEAR(`date`)='$year'
            $where .=" MONTH(`date`)='$month' AND YEAR(`date`)='$year'";
            $AND .=" AND `date` BETWEEN '$firstDate' AND '$lastDate'";
        }
        // $where .=" YEAR(`date`) = '$year' AND MONTH(`date`) = '$month' ";
        $paid_purchases = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN $where THEN $column
        END), 0)AS paid_purchases
        FROM `transactions`
        WHERE  user_id = '$user_id'
        AND `type` = 'purchase'
               AND deleted_at IS NULL $AND");

        //YEAR(`date`) = '$year' AND MONTH(`date`) = '$month'
        $paid_expenses = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN $where THEN $column
        END), 0)AS paid_expenses
        FROM `transactions`
        WHERE  user_id = '$user_id'
        AND `type` = 'expense'
            AND deleted_at IS NULL $AND");

        if(!$cashBaseIncome)
         {
            $owner_account = \DB::select("SELECT COALESCE(SUM(CASE WHEN $where THEN $column END), 0) AS cash_out FROM `owner_accounts`
            WHERE `status_id` = '14'
            AND  user_id = '$user_id'
            AND deleted_at IS NULL $AND ");

            if($single==true)
            {
                return $paid_expenses[0]->paid_expenses + $owner_account[0]->cash_out;

            }
            else
            {
                 return $paid_purchases[0]->paid_purchases + $paid_expenses[0]->paid_expenses + $owner_account[0]->cash_out;
            }
        }
        else
       {
            if($single==true)
            {
                return $paid_expenses[0]->paid_expenses;

            }
            else
            {
             return $paid_purchases[0]->paid_purchases + $paid_expenses[0]->paid_expenses;
            }
       }

    }

    public function fetchCashInQuarterly($first_array,$last_array,$column='amount',$user_id,$request,$quart=''){

            $first_month = date_parse($last_array[0])['month'];
            $last_month = date_parse($first_array[0])['month'];
            $first= $last_array[1]."-".$first_month."-01";
            $last= $first_array[1]."-".$last_month."-". date("t", strtotime($first_array[1]."-".$last_month."-01"));

            // $last= $last_array[1]."-".$first_array[0]."-".cal_days_in_month(CAL_GREGORIAN,$month, date('Y'));
            // $first = date('Y-m-d',strtotime($first));
            // $last = date('Y-m-d',strtotime($last));

            // echo $last."<br>";

        $where = '';
        if(isset(request()->all()['start_date']) && isset(request()->all()['end_date'])){
            $start = request()->all()['start_date'];
            $end_date = request()->all()['end_date'];
            $start_month = date('m',strtotime($start));
            $end_month = date('m',strtotime($end_date));
            $year = date('Y',strtotime($end_date));
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
        }


        $When ='';
        if($quart==true){
            $When .= " `date` BETWEEN '".$first."' AND '".$last."'";
        }else{
            $When .= " `date` >= '".$first."' AND `date` < '".$last."' ";
        }

        // echo "SELECT
        // COALESCE(SUM(CASE
        // WHEN $When
        //     THEN $column
        // END), 0) AS cash_in
        // FROM   `transactions`
        // WHERE user_id = '$user_id'
        //        AND `type` = 'sale'
        //        AND deleted_at IS NULL";


        $data = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN $When
            THEN $column
        END), 0) AS cash_in
        FROM   `transactions`
        WHERE user_id = '$user_id'
               AND `type` = 'sale'
               AND deleted_at IS NULL");


        return $data[0]->cash_in;
    }

    public function fetchCashOutQuarterly($first_array,$last_array,$column='amount',$user_id,$single=false,$request){

        // $first_month = date('m',strtotime($first_array[0]));
        // $last_month = date('m',strtotime($last_array[0]));
        // $first= $first_array[1]."-".$first_month."-01";
        // $last= $last_array[1]."-".$last_month."-31";
        // // $month = date('m',strtotime($last_array[0]));
        // // $first= $first_array[1]."-".$month."-01";
        // // $last= $last_array[1]."-".$first_array[0]."-".cal_days_in_month(CAL_GREGORIAN,$month, date('Y'));
        // $first = date('Y-m-d',strtotime($first));
        // $last = date('Y-m-d',strtotime($last));

        $first_month = date_parse($last_array[0])['month'];
        $last_month = date_parse($first_array[0])['month'];
        $first= $last_array[1]."-".$first_month."-01";
        $last= $first_array[1]."-".$last_month."-". date("t", strtotime($first_array[1]."-".$last_month."-01"));

        $data=[];



        $where = '';
        if(isset($request['start_date']) && isset($request['end_date'])){
            $start = $request['start_date'];
            $end_date = $request['end_date'];
            $start_month = date('m',strtotime($request['start_date']));
            $end_month = date('m',strtotime($request['end_date']));
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
        }

        $paid_purchases = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
           THEN $column
        END), 0) AS paid_purchases
        FROM `transactions`
        WHERE  user_id = '$user_id'
        AND `type` = 'purchase'
               AND deleted_at IS NULL");


        $paid_expenses = \DB::select("SELECT
                                COALESCE(SUM(CASE
                                WHEN `date` BETWEEN '".$first."' AND '".$last."'
                                THEN $column
                                END), 0) AS paid_expenses
                                FROM `transactions`
                                WHERE  user_id = '$user_id'
                                AND `type` = 'expense'
                                    AND deleted_at IS NULL");
        if($single==true){
            return $paid_expenses[0]->paid_expenses;
        }else{
            return $paid_purchases[0]->paid_purchases + $paid_expenses[0]->paid_expenses;
        }

    }


    public function fetchCashInStatementQuarterly($first_array,$last_array,$column='amount',$user_id){
        $month = date('m',strtotime($last_array[0]));
        $first= $first_array[1]."-".$first_array[0]."-01";
        $last= $last_array[1]."-".$month."-".cal_days_in_month(CAL_GREGORIAN,$month, date('Y'));
        $first = date('Y-m-d',strtotime($first));
        $last = date('Y-m-d',strtotime($last));


        $where = '';
        $case ='';
        if(isset(request()->all()['start_date']) && isset(request()->all()['end_date'])){
            $start = request()->all()['start_date'];
            $end_date = request()->all()['end_date'];
            $start_month = date('m',strtotime($start));
            $end_month = date('m',strtotime($end_date));
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
            $case .=" `date` BETWEEN '$start' AND '$end_date'";
        }

        $data = \DB::select("SELECT
        COALESCE(SUM(CASE
         WHEN `date`  BETWEEN '".$first."' AND '".$last."'
            THEN $column
        END), 0) AS cash_in
        FROM   `transactions`
        WHERE  user_id = '$user_id'
        AND `type` = 'sale'
               AND deleted_at IS NULL $where");
        return $data[0]->cash_in;
    }

    public function fetchCashOutStatementQuarterly($first_array,$last_array,$column='amount',$user_id,$single=false,$month_n=''){

        $month = date('m',strtotime($last_array[0]));
        $last= $first_array[1]."-".$first_array[0]."-01";
        $first= $last_array[1]."-".$month."-".cal_days_in_month(CAL_GREGORIAN,$month, date('Y'));
        $first = date('Y-m-d',strtotime($first));
        $last = date('Y-m-d',strtotime($last));


        $where = '';
        if(isset(request()->all()['start_date']) && isset(request()->all()['end_date'])){
            $start = request()->all()['start_date'];
            $end_date = request()->all()['end_date'];
            $start_month = date('m',strtotime($start));
            $end_month = date('m',strtotime($end_date));
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
        }

        $paid_purchases = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date`  BETWEEN '".$last."' AND '".$first."'
           THEN $column
        END), 0) AS paid_purchases
        FROM `transactions`
        WHERE  user_id = '$user_id'
        AND `type` = 'purchase'
               AND deleted_at IS NULL $where");


        $paid_expenses = \DB::select("SELECT
                                COALESCE(SUM(CASE
                                WHEN `date`  BETWEEN '".$last."' AND '".$first."'
                                THEN $column
                                END), 0) AS paid_expenses
                                FROM `transactions`
                                WHERE  user_id = '$user_id'
                                AND `type` = 'expense'
                                    AND deleted_at IS NULL $where");

        if($single==true)
            return $paid_expenses[0]->paid_expenses;
        else
            return $paid_purchases[0]->paid_purchases + $paid_expenses[0]->paid_expenses;

    }

    public function fetchCashInYearData($year,$month,$user_id,$column='received_amount'){
        $AND='';
        if($month !="12" && $month > date('m')  ){
            $AND .= ' AND MONTH(`date`) <='.$month;
        }else if($month !="12" && $month < date('m')  ){
            $AND .= ' AND MONTH(`date`) >='.$month;
        }


        $where = '';
        if(isset(request()->all()['start_date']) && isset(request()->all()['end_date'])){
            $start = request()->all()['start_date'];
            $end_date = request()->all()['end_date'];
            $start_month = date('m',strtotime(request()->all()['start_date']));
            $end_month = date('m',strtotime(request()->all()['end_date']));
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
        }
        $data = \DB::select("SELECT COALESCE(SUM(CASE WHEN YEAR(`date`) = '".$year."' THEN $column END) ,0) AS year_data FROM  `sales`
        WHERE user_id='$user_id' AND deleted_at IS NULL");
        return $data[0]->year_data;
    }

    public function fetchCashOutYearData($year,$month,$user_id,$column='amount_paid',$single =false){

        $AND='';
        if($month !="12" && $month > date('m')  ){
            $AND .= ' AND MONTH(`date`) <='.$month;
        }else if($month !="12" && $month < date('m')  ){
            $AND .= ' AND MONTH(`date`) >='.$month;
        }


        $where = '';
        if(isset(request()->all()['start_date']) && isset(request()->all()['end_date'])){
            $start = request()->all()['start_date'];
            $end_date = request()->all()['end_date'];
            $start_month = date('m',strtotime(request()->all()['start_date']));
            $end_month = date('m',strtotime(request()->all()['end_date']));
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
        }


        $purchase_amount = \DB::select("SELECT COALESCE(SUM(CASE WHEN YEAR(`date`) = '".$year."' THEN $column END) ,0) AS purchase_amount FROM  `purchases`
        WHERE user_id='$user_id' AND deleted_at IS NULL");

        $expense_amount = \DB::select("SELECT COALESCE(SUM(CASE WHEN YEAR(`date`) = '".$year."' THEN $column END) ,0) AS expense_amount FROM  `expenses`
        WHERE user_id='$user_id' AND deleted_at IS NULL");

        if($single ==true)
            return $expense_amount[0]->expense_amount;
        else
            return $purchase_amount[0]->purchase_amount + $expense_amount[0]->expense_amount;
    }
    public function getDataByCategories($year,$firstMonth,$lastMonth,$user_id,$month_n){

        if(count($year) < 2){
            $prevMonth = $year[0];
                $nextMonth = (isset($year[1]))?$year[1]:$year[0];
                $lastDay = cal_days_in_month(CAL_GREGORIAN, 12, date('Y'));
                $EndDate = $nextMonth."-12"."-".$lastDay;
                $startDate = $prevMonth."-01"."-01";

       }else{
            if($month_n == 12){
                $prevMonth = $year[0];
                $nextMonth = (isset($year[1]))?$year[1]:$year[0];
                $lastDay = cal_days_in_month(CAL_GREGORIAN, 12, date('Y'));
                $startDate = $nextMonth."-12"."-".$lastDay;
                $EndDate = $prevMonth."-01"."-01";

            }else if($month_n < date('m')){
                $prevMonth = $year[0];
                $nextMonth = (isset($year[1]))?$year[1]:$year[0];
                $lastDay = cal_days_in_month(CAL_GREGORIAN, $lastMonth, date('Y'));
                $startDate = $prevMonth."-".$lastMonth."-01";
                $EndDate = $nextMonth."-".$firstMonth."-".$lastDay;
            }else{
                $year = array_reverse($year);
                $prevMonth = $year[0];
                $nextMonth = (isset($year[1]))?$year[1]:$year[0];
                $lastDay = cal_days_in_month(CAL_GREGORIAN, $lastMonth, date('Y'));
                $startDate = $prevMonth."-".$lastMonth."-01";
                $EndDate = $nextMonth."-".$firstMonth."-".$lastDay;
            }
       }


       $where = '';
       if(isset(request()->all()['start_date']) && isset(request()->all()['end_date'])){
           $startDate = request()->all()['start_date'];
           $EndDate = request()->all()['end_date'];
           $start_month = date('m',strtotime($startDate));
           $end_month = date('m',strtotime($EndDate));
           $where .=" AND s.`date` BETWEEN '$startDate' AND '$EndDate'";
       }

        $data=[];
        $cates = \DB::select("SELECT
        sub.title,
                    COALESCE(SUM(CASE
                                WHEN s.sub_category_id = sub.id
                            AND s.`date` BETWEEN '$startDate'  AND '$EndDate'
                                THEN received_amount
                                END), 0) amount
            FROM   `sales` s
                    INNER JOIN `sub_categories` sub
                            ON s.user_id = sub.user_id
            WHERE  s.user_id = '$user_id'
                    AND s.deleted_at IS NULL $where
            GROUP  BY sub.id; ");

           foreach($cates as $cat){
            if($cat->amount == 0)
                continue;
             $data[] = (object) ["title"=>trans("categories.".str_replace(" ","_",strtolower($cat->title))),"amount"=>$cat->amount];
           }

           return $data;

    }

    public function DashboardMonthlyCashIn($year,$month,$user_id,$firstDate,$lastDate,$column='amount'){
        $data=[];
        $where = '';
        $AND = '';
         if($firstDate){
            //`date` BETWEEN '$firstDate' AND '$lastDate' AND
            $where .="  MONTH(`date`)='$month' AND YEAR(`date`)='$year'";
        }

       /*  if($firstDate){
            // AND MONTH(`date`)='$month' AND YEAR(`date`)='$year'
            $where .=" MONTH(`date`)='$month' AND YEAR(`date`)='$year'";
            $AND .=" AND `date` BETWEEN '$firstDate' AND '$lastDate'";
        } */
        // echo "SELECT COALESCE(SUM(CASE WHEN $where THEN $column END), 0) AS cash_in FROM `sales`
        // WHERE  user_id = '$user_id'
        //        AND deleted_at IS NULL \n";
        $data = \DB::select("SELECT COALESCE(SUM(CASE WHEN $where THEN $column END), 0) AS cash_in FROM `transactions`
        WHERE  user_id = '$user_id'
               AND `type` = 'sale'
               AND deleted_at IS NULL ");

        // $owner_account = \DB::select("SELECT COALESCE(SUM(CASE WHEN $where THEN $column END), 0) AS cash_in FROM `owner_accounts`
        // WHERE `status_id` = '13'
        // AND  user_id = '$user_id'
        //     AND deleted_at IS NULL $AND ");
        return $data[0]->cash_in;  //+  $owner_account[0]->cash_in;
    }

    public function DashboardMonthlyCashOut($year,$month,$user_id,$firstDate,$lastDate,$column='amount',$single=false){
        $data=[];
        $where = '';
        $AND = '';
        if($firstDate){
            //`date` BETWEEN '$firstDate' AND '$lastDate' AND
            $where .="  MONTH(`date`)='$month' AND YEAR(`date`)='$year'";
        }
        // $where .=" YEAR(`date`) = '$year' AND MONTH(`date`) = '$month' ";
        $paid_purchases = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN $where THEN $column
        END), 0)AS paid_purchases
        FROM `transactions`
        WHERE  user_id = '$user_id'
        AND `type` = 'purchase'
               AND deleted_at IS NULL");

        //YEAR(`date`) = '$year' AND MONTH(`date`) = '$month'
        $paid_expenses = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN $where THEN $column
        END), 0)AS paid_expenses
        FROM `transactions`
        WHERE  user_id = '$user_id'
        AND `type` = 'expense'
            AND deleted_at IS NULL");

       /*  $owner_account = \DB::select("SELECT COALESCE(SUM(CASE WHEN $where THEN $column END), 0) AS cash_out FROM `owner_accounts`
        WHERE `status_id` = '14'
        AND  user_id = '$user_id'
        AND deleted_at IS NULL $AND "); */


        if($single==true)
            return $paid_expenses[0]->paid_expenses;//+$owner_account[0]->cash_out;
        else
            return $paid_purchases[0]->paid_purchases + $paid_expenses[0]->paid_expenses;// + $owner_account[0]->cash_out;
    }

    public function DashboardCashInQuarterly($first_array,$last_array,$column='amount',$user_id,$request,$quart=''){
        $month = date('m',strtotime($first_array[0]));
        $first= $last_array[1]."-".$last_array[0]."-01";
        $last= $first_array[1]."-".$month."-".cal_days_in_month(CAL_GREGORIAN,$month, date('Y'));
        $first = date('Y-m-d',strtotime($first));
        $last = date('Y-m-d',strtotime($last));
        $data = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
            THEN $column
        END), 0) AS cash_in
        FROM   `transactions`
        WHERE user_id = '$user_id' AND `type` = 'sale'
            AND deleted_at IS NULL");

        $owner_account = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
        THEN $column
        END), 0) AS cash_in
        FROM `owner_accounts`
        WHERE  user_id = '$user_id'
        AND `status_id` = '13'
            AND deleted_at IS NULL");

        return $data[0]->cash_in + $owner_account[0]->cash_in;
    }

    public function DashboardCashOutQuarterly($first_array,$last_array,$column='amount',$user_id,$single=false,$request){
        $month = date('m',strtotime($first_array[0]));
        $first= $last_array[1]."-".$last_array[0]."-01";
        $last= $first_array[1]."-".$month."-".cal_days_in_month(CAL_GREGORIAN,$month, date('Y'));
        $first = date('Y-m-d',strtotime($first));
        $last = date('Y-m-d',strtotime($last));
        $data=[];

        $paid_purchases = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
           THEN $column
        END), 0) AS paid_purchases
        FROM `transactions`
        WHERE  user_id = '$user_id' AND `type` = 'purchase'
               AND deleted_at IS NULL");

        $paid_expenses = \DB::select("SELECT
                                COALESCE(SUM(CASE
                                WHEN `date` BETWEEN '".$first."' AND '".$last."'
                                THEN $column
                                END), 0) AS paid_expenses
                                FROM `transactions`
                                WHERE  user_id = '$user_id' AND `type` = 'expense'
                                    AND deleted_at IS NULL");
        $owner_account = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
        THEN $column
        END), 0) AS cash_out
        FROM `owner_accounts`
        WHERE  user_id = '$user_id'
        AND `status_id` = '14'
            AND deleted_at IS NULL");
        if($single==true){
            return $paid_expenses[0]->paid_expenses + $owner_account[0]->cash_out;
        }else{
            return $paid_purchases[0]->paid_purchases + $paid_expenses[0]->paid_expenses + $owner_account[0]->cash_out;
        }
    }

    public function DashboardCashInYearData($startDate,$endDate,$year,$month,$user_id,$column='received_amount'){
        $data = \DB::select("SELECT COALESCE(SUM(CASE WHEN YEAR(`date`) = '".$year."' THEN $column END) ,0) AS year_data FROM  `transactions`
        WHERE user_id='$user_id' AND `type` = 'sale' AND deleted_at IS NULL");

       /*  $owner_account = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$startDate."' AND '".$endDate."'
        THEN $column
        END), 0) AS cash_in
        FROM `owner_accounts`
        WHERE  user_id = '$user_id'
        AND `status_id` = '13'
            AND deleted_at IS NULL"); */

        return $data[0]->year_data; //+ $owner_account[0]->cash_in;
    }

    public function DashboardCashOutYearData($startDate,$endDate,$year,$month,$user_id,$column='amount_paid',$single =false){

        $purchase_amount = \DB::select("SELECT COALESCE(SUM(CASE WHEN YEAR(`date`) = '".$year."' THEN $column END) ,0) AS purchase_amount FROM  `transactions`
        WHERE user_id='$user_id' AND `type` = 'purchase' AND deleted_at IS NULL");

        $expense_amount = \DB::select("SELECT COALESCE(SUM(CASE WHEN YEAR(`date`) = '".$year."' THEN $column END) ,0) AS expense_amount FROM  `transactions`
        WHERE user_id='$user_id' AND `type` = 'expense' AND deleted_at IS NULL");

       /*   $owner_account = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$startDate."' AND '".$endDate."'
        THEN $column
        END), 0) AS cash_out
        FROM `owner_accounts`
        WHERE  user_id = '$user_id'
        AND `status_id` = '14'
            AND deleted_at IS NULL"); */

        if($single ==true)
            return $expense_amount[0]->expense_amount; //+ $owner_account[0]->cash_out;
        else
            return $purchase_amount[0]->purchase_amount + $expense_amount[0]->expense_amount;
    }
    public function getRemainingAmount($id){
        return $this->where('id',$id)->pluck('remaining_amount')->first();
    }

    public function fetchCashInYearDataDateWise($startDate,$endDate,$user_id,$column='amount',$cahseBaseIncome){
        $data = \DB::select("SELECT COALESCE(SUM(CASE WHEN `date` BETWEEN '".$startDate."' AND '".$endDate."' THEN $column END) ,0) AS year_data FROM  `transactions`
        WHERE user_id='$user_id' AND `type` = 'sale' AND deleted_at IS NULL");

        if(!$cahseBaseIncome)
        {
                $owner_account = \DB::select("SELECT
            COALESCE(SUM(CASE
            WHEN `date` BETWEEN '".$startDate."' AND '".$endDate."'
            THEN $column
            END), 0) AS cash_in
            FROM `owner_accounts`
            WHERE  user_id = '$user_id'
            AND `status_id` = '13'
                AND deleted_at IS NULL");
          return $data[0]->year_data + $owner_account[0]->cash_in;
        }
        else
        {
            return $data[0]->year_data;
        }

    }

    public function fetchCashOutYearDataDateWise($startDate,$endDate,$user_id,$column='amount',$single =false,$cahseBaseIncome)
    {
        $purchase_amount = \DB::select("SELECT COALESCE(SUM(CASE WHEN `date` BETWEEN '".$startDate."' AND '".$endDate."' THEN $column END) ,0) AS purchase_amount FROM  `transactions`
        WHERE user_id='$user_id' AND `type` = 'purchase' AND deleted_at IS NULL");

        $expense_amount = \DB::select("SELECT COALESCE(SUM(CASE WHEN `date` BETWEEN '".$startDate."' AND '".$endDate."' THEN $column END) ,0) AS expense_amount FROM  `transactions`
        WHERE user_id='$user_id' AND `type` = 'expense' AND deleted_at IS NULL");

        if(!$cahseBaseIncome)
        {
            $owner_account = \DB::select("SELECT
            COALESCE(SUM(CASE
            WHEN `date` BETWEEN '".$startDate."' AND '".$endDate."'
            THEN $column
            END), 0) AS cash_out
            FROM `owner_accounts`
            WHERE  user_id = '$user_id'
            AND `status_id` = '14'
                AND deleted_at IS NULL");

            if($single ==true)
                return $expense_amount[0]->expense_amount + $owner_account[0]->cash_out;
            else
                return $purchase_amount[0]->purchase_amount + $expense_amount[0]->expense_amount + $owner_account[0]->cash_out;
        }
        else
        {
            if($single ==true)
                return $expense_amount[0]->expense_amount;
            else
                return $purchase_amount[0]->purchase_amount + $expense_amount[0]->expense_amount;

        }
    }

    public function CashInQuarterly($first,$last,$column='amount',$user_id,$request,$quart=''){
        $first = date('Y-m-d',strtotime($first));
        $last = date('Y-m-d',strtotime($last));
        $data = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
            THEN $column
        END), 0) AS cash_in
        FROM   `transactions`
        WHERE user_id = '$user_id' AND `type` = 'sale'
            AND deleted_at IS NULL");

       /*  $owner_account = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
        THEN $column
        END), 0) AS cash_in
        FROM `owner_accounts`
        WHERE  user_id = '$user_id'
        AND `status_id` = '13'
            AND deleted_at IS NULL"); */

        return $data[0]->cash_in;//+$owner_account[0]->cash_in;
    }

    public function CashOutQuarterly($first,$last,$column='amount',$user_id,$single=false,$request){
        $first = date('Y-m-d',strtotime($first));
        $last = date('Y-m-d',strtotime($last));
        $data=[];

        $paid_purchases = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
           THEN $column
        END), 0) AS paid_purchases
        FROM `transactions`
        WHERE  user_id = '$user_id' AND `type` = 'purchase'
               AND deleted_at IS NULL");

        $paid_expenses = \DB::select("SELECT
                                COALESCE(SUM(CASE
                                WHEN `date` BETWEEN '".$first."' AND '".$last."'
                                THEN $column
                                END), 0) AS paid_expenses
                                FROM `transactions`
                                WHERE  user_id = '$user_id' AND `type` = 'expense'
                                    AND deleted_at IS NULL");

       /*  $owner_account = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
        THEN $column
        END), 0) AS cash_out
        FROM `owner_accounts`
        WHERE  user_id = '$user_id'
        AND `status_id` = '14'
            AND deleted_at IS NULL"); */

        if($single==true){
            return $paid_expenses[0]->paid_expenses;//+$owner_account[0]->cash_out;
        }else{
            return $paid_purchases[0]->paid_purchases + $paid_expenses[0]->paid_expenses;
        }
    }

    public function fetchingCashInQuarterly($first,$last,$column='amount',$user_id,$request,$quart=''){
        $where = '';
        if(isset(request()->all()['start_date']) && isset(request()->all()['end_date'])){
            $start = request()->all()['start_date'];
            $end_date = request()->all()['end_date'];
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
        }
        $When ='';
        if($quart==true){
            $When .= " `date` BETWEEN '".$first."' AND '".$last."'";
        }else{
            $When .= " `date` >= '".$first."' AND `date` < '".$last."' ";
        }
        $data = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN $When
            THEN $column
        END), 0) AS cash_in
        FROM   `transactions`
        WHERE user_id = '$user_id'
            AND `type` = 'sale'
            AND deleted_at IS NULL");

        $owner_account = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
        THEN $column
        END), 0) AS cash_in
        FROM `owner_accounts`
        WHERE  user_id = '$user_id'
        AND `status_id` = '13'
            AND deleted_at IS NULL");


     $caseIn = $data[0]->cash_in +  $owner_account[0]->cash_in;
     return $caseIn;


    }

    public function fetchingCashOutQuarterly($first,$last,$column='amount',$user_id,$single=false,$request){
        $data=[];
        $where = '';
        if(isset($request['start_date']) && isset($request['end_date'])){
            $start = $request['start_date'];
            $end_date = $request['end_date'];
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
        }

        $paid_purchases = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
        THEN $column
        END), 0) AS paid_purchases
        FROM `transactions`
        WHERE  user_id = '$user_id'
        AND `type` = 'purchase'
            AND deleted_at IS NULL");

        $paid_expenses = \DB::select("SELECT
                                COALESCE(SUM(CASE
                                WHEN `date` BETWEEN '".$first."' AND '".$last."'
                                THEN $column
                                END), 0) AS paid_expenses
                                FROM `transactions`
                                WHERE  user_id = '$user_id'
                                AND `type` = 'expense'
                                    AND deleted_at IS NULL");

        $owner_account = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
        THEN $column
        END), 0) AS cash_out
        FROM `owner_accounts`
        WHERE  user_id = '$user_id'
        AND `status_id` = '14'
            AND deleted_at IS NULL");


        if($single==true){
            return $paid_expenses[0]->paid_expenses;
        }else{

            return $paid_purchases[0]->paid_purchases + $paid_expenses[0]->paid_expenses + $owner_account[0]->cash_out;
        }

    }

    public function FiscalYearQuarterData($first,$last,$label,$user_id,$request){
        $first = date('Y-m-d',strtotime($first));
        $last    = date('Y-m-d',strtotime($last));
        $label = str_replace("-","_",$label);
        $where = '';
        if(isset($request['start_date']) && isset($request['end_date'])){
            $start = $request['start_date'];
            $end_date = $request['end_date'];
            $start_month = date('m',strtotime($request['start_date']));
            $end_month = date('m',strtotime($request['end_date']));
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
        }
        return \DB::select("SELECT COALESCE(SUM(CASE WHEN `date` BETWEEN '".$first."' AND '".$last."' THEN amount END) ,0)AS '".$label."' FROM  `transactions`
        WHERE user_id='$user_id' AND `type` = 'sale' AND deleted_at IS NULL");
    }

    public function fetchingCashInStatementQuarterly($first,$last,$column='amount',$user_id){
        $first = date('Y-m-d',strtotime($first));
        $last = date('Y-m-d',strtotime($last));
        $where = '';
        $case ='';
        $AND ='';
        if($first)
        {
            $AND .=" AND `date` BETWEEN '$first' AND '$last'";
        }
        if(isset(request()->all()['start_date']) && isset(request()->all()['end_date'])){
            $start = request()->all()['start_date'];
            $end_date = request()->all()['end_date'];
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
            $case .=" `date` BETWEEN '$start' AND '$end_date'";
        }

        $data = \DB::select("SELECT
        COALESCE(SUM(CASE
         WHEN `date`  BETWEEN '".$first."' AND '".$last."'
            THEN $column
        END), 0) AS cash_in
        FROM   `transactions`
        WHERE  user_id = '$user_id'
        AND `type` = 'sale'
               AND deleted_at IS NULL $where");
      /*   $owner_account = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
        THEN $column
        END), 0) AS cash_in
        FROM `owner_accounts`
        WHERE  user_id = '$user_id'
        AND `status_id` = '13'
            AND deleted_at IS NULL"); */

        return $data[0]->cash_in; //+ $owner_account[0]->cash_in;
    }

    public function fetchingCashOutStatementQuarterly($first,$last,$column='amount',$user_id,$single=false){
        $first = date('Y-m-d',strtotime($first));
        $last = date('Y-m-d',strtotime($last));
        $where = '';
        $AND ='';
        if($first)
        {
            $AND .=" AND `date` BETWEEN '$first' AND '$last'";
        }
        if(isset(request()->all()['start_date']) && isset(request()->all()['end_date'])){
            $start = request()->all()['start_date'];
            $end_date = request()->all()['end_date'];
            $start_month = date('m',strtotime($start));
            $end_month = date('m',strtotime($end_date));
            $where .=" AND `date` BETWEEN '$start' AND '$end_date'";
        }

        $date_array = [$last,$first];
        sort($date_array);

        $paid_purchases = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date`  BETWEEN '".$date_array[0]."' AND '".$date_array[1]."'
           THEN $column
        END), 0) AS paid_purchases
        FROM `transactions`
        WHERE  user_id = '$user_id'
        AND `type` = 'purchase'
               AND deleted_at IS NULL $where");

        $paid_expenses = \DB::select("SELECT
                                COALESCE(SUM(CASE
                                WHEN `date`  BETWEEN '".$date_array[0]."' AND '".$date_array[1]."'
                                THEN $column
                                END), 0) AS paid_expenses
                                FROM `transactions`
                                WHERE  user_id = '$user_id'
                                AND `type` = 'expense'
                                    AND deleted_at IS NULL $where");
       /*  $owner_account = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$first."' AND '".$last."'
        THEN $column
        END), 0) AS cash_out
        FROM `owner_accounts`
        WHERE  user_id = '$user_id'
        AND `status_id` = '14'
            AND deleted_at IS NULL"); */

        if($single==true)
            return $paid_expenses[0]->paid_expenses; // + $owner_account[0]->cash_out;
        else
            return $paid_purchases[0]->paid_purchases + $paid_expenses[0]->paid_expenses; // + $owner_account[0]->cash_out;

    }
}
