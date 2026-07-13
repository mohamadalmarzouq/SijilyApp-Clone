<?php

namespace App\Http\Controllers\Api;

use App\Events\UserSignUp;
use App\Http\Validation\RulesAppUser as Rules;
use App\Models\AppUser;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Status;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class OverviewControllers extends Controller
{
    function __construct()
    {
        $this->primary_model = new AppUser();
        $this->status_model = new Status();
        $this->sale_model = new Sale();
        $this->expense_model = new Expense();
        $this->sub_cat_model = new SubCategory();
        $this->module = $this->primary_model->getTable();
    }

    public function CashInOut(Request $request){
        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);
       // $request->merge(['recorded_by'=>$recorded_by]);
        $fiscalYear = $this->primary_model->getUserFiscalYear($request->user_id);
        $searchBy = (isset($request->search_by))?$request->search_by:1;
        if(!empty($fiscalYear)){
            $fiscalMonth = explode("/",$fiscalYear);
        }else{
            return sendErrorToClient("Year ended date required");
        }

        $data=[];
        $months=[];
        if($fiscalMonth[1]=="12"){
            $month_n=$fiscalMonth[1];
        }else if($fiscalMonth[1] < date("m")){
            $month_n = $fiscalMonth[1];
        }else{
            $month_n = $fiscalMonth[1]+1;
        }

        $month_before_dec = ($fiscalMonth[1]=="12")? true:false;
        for ($i = 1; $i <= 12; $i++) {
            if($month_n > date('m') && $month_before_dec==false){
                $fiscalMonthCal = " -$i months";
             }else if($month_n == 12 && $month_before_dec==true){
                $fiscalMonthCal = " +$i months";
             }else if($month_before_dec==false){
                $fiscalMonthCal = " +$i months";
             }else{
                 $fiscalMonthCal = " +$i months";
             }

             if($month_n == 12 && $month_before_dec==true){
                $months[] = date("F-".date("Y"), strtotime(date('Y')."-".$month_n.$fiscalMonthCal));
             }else{
                $months[] = date("F-Y", strtotime(date('Y')."-".$month_n.$fiscalMonthCal));
             }
        }
        if(isset($request->start_date) && isset($request->end_date)){
            $title = trans("Months.".date('M',strtotime($request->start_date)))." ". (date('Y',strtotime($request->start_date)))." - ".trans("Months.".date('M',strtotime($request->end_date)))." ". (date('Y',strtotime($request->end_date)));
        }else{
            $title = getMonthTitle($months,$month_n,$month_before_dec);
        }


        $totalCashIn=[];
        $totalCashOut=[];
        $totalNetCash=[];

        if($searchBy == 1){
            if(isset($request->start_date) && isset($request->end_date)){
                $months = getDatesFromRange($request->start_date,date('Y-m-d', strtotime($request->end_date . ' +1 day')));
            }

            $months = array_reverse($months);

            $firstDate = end($months);
            $lastDate = $months[0];
            $fetchCashIn='';
            $fetchCashOut='';
            foreach($months as $month){
                $mon = explode("-",$month);
                $mn = date('m',strtotime($mon[0]));

                //New Set

                $getYear = date('Y',strtotime($month));
                $getShortYear = date('y',strtotime($month));
                $getMonth = date('m',strtotime($month));
                $getMonthName = date('M',strtotime($month));
                $label = trans("Months.".$getMonthName)."-".($getShortYear);
                // End of new set
                $fetchCashIn = $this->sale_model->fetchCashIn($getYear,$getMonth,$request->user_id,$firstDate,$lastDate);
                $fetchCashOut = $this->sale_model->fetchCashOut($getYear,$getMonth,$request->user_id,$firstDate,$lastDate);
                $totalCashIn[] = $fetchCashIn;
                $totalCashOut[] = $fetchCashOut;
                $totalNetCash[] = $fetchCashIn - $fetchCashOut;
                $dateKey = strtoupper(trans("Months.".$getMonthName))."-".($getShortYear);
                // $dateKey = strtoupper(trans("Months.".date('M',strtotime($mon[0]))))."-".($getShortYear);

                // echo $dateKey;
                $data[$dateKey]['cash_in'] = $fetchCashIn;
                $data[$dateKey]['cash_out'] = $fetchCashOut;
                $data[$dateKey]['net_cash'] = $fetchCashIn - $fetchCashOut;
            }
            $finalCashOut = array_column($data, 'cash_out');
            $finalCashIn = array_column($data, 'cash_in');
            $sort= array_reverse($data,true);
        }else if($searchBy == 2){
            $quarters = [];

            $start_year =  date('Y',strtotime($request->start_date));
            $end_year = date('Y',strtotime($request->end_date));
            $start_month =  date('m',strtotime($request->start_date));
            $end_month = date('m',strtotime($request->end_date));
            $start = new \DateTime($start_year.'-'.$start_month);
            $start->modify('first day of this month');
            $end = new \DateTime($end_year .'-'.$end_month);
            $end->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($start, $interval, $end);

            foreach ($period as $dt) {
                $quarters[] =  $dt->format("M-Y");
            }

            //$quarters = array_chunk($months,3);
            if($month_before_dec==true)
                $quarters = array_reverse($quarters);

            $quarters = array_chunk($quarters,3);
            // print_r($quarters);die;
            //$quarters = array_reverse($quarters);
            $yearArr= [date('Y'),date("Y",strtotime("+1 year"))];

            foreach($quarters as $key=> $quarter){

                if($month_n==12){
                    $onlyMonth = $quarter[0];
                    $onlyYear =end($quarter);
                    $last_array = explode("-",$onlyMonth);
                    $first_array = explode("-",$onlyYear);

                    $last_array = explode("-",end($quarter));
                    $first_array = explode("-",array_values($quarter)[0]);
                    $label =  strtoupper(trans("Months.".substr($last_array[0],0,3))).",".substr($first_array[1], -2)."-".strtoupper(trans("Months.".substr($first_array[0],0,3))).",".substr($last_array[1], -2);
                    //$label =  strtoupper(substr($first_array[0],0,3))."-".strtoupper(substr($last_array[0],0,3));
                    //$label =  strtoupper(trans("Months.".substr($first_array[0],0,3)))."-".strtoupper(trans("Months.".substr($last_array[0],0,3)))."-".$last_array[1];
                }else if($month_n < date('m')){
                    $onlyMonth= $quarter[0];
                    $onlyYear =end($quarter);
                    $first_array = explode("-",$onlyMonth);
                    $last_array = explode("-",$onlyYear);
                    //$label =  strtoupper(substr($first_array[0],0,3))."-".strtoupper(substr($last_array[0],0,3));
                    $label =  strtoupper(trans("Months.".substr($last_array[0],0,3))).",".substr($last_array[1], -2)."-".strtoupper(trans("Months.".substr($first_array[0],0,3))).",".substr($first_array[1], -2);
                }else{
                    $onlyYear = $quarter[0];
                    $onlyMonth =end($quarter);
                    $last_array = explode("-",$onlyMonth);
                    $first_array = explode("-",$onlyYear);
                    $last_array = explode("-",array_values($quarter)[0]);

                    $first_array = explode("-",end($quarter));
                    //$label =  strtoupper(substr($first_array[0],0,3))."-".strtoupper(substr($last_array[0],0,3));
                    $label =  strtoupper(trans("Months.".substr($last_array[0],0,3))).",".substr($last_array[1], -2)."-".strtoupper(trans("Months.".substr($first_array[0],0,3))).",".substr($first_array[1], -2);
                    //$label =  strtoupper(trans("Months.".substr($first_array[0],0,3)))."-".strtoupper(trans("Months.".substr($last_array[0],0,3)))."-".$last_array[1];
                    $first_month = date('m',strtotime($first_array[0]));
                    $last_month = date('m',strtotime($last_array[0]));
                }
                $years = $yearArr;
                $labelRep = $label;//str_replace("-","_",$label);
                $tCashIN = $this->sale_model->fetchCashInQuarterly($first_array,$last_array,'amount',$request->user_id,$request, TRUE);
                $tCashOUT = $this->sale_model->fetchCashOutQuarterly($first_array,$last_array,'amount',$request->user_id,false,$request);
                $totalCashIn[] = $tCashIN;
                $totalCashOut[] = $tCashOUT;
                $totalNetCash[] = $tCashIN - $tCashOUT;
                $data[$labelRep]['cash_in'] = $tCashIN;
                $data[$labelRep]['cash_out'] = $tCashOUT;
                $data[$labelRep]['net_cash'] =  $tCashIN - $tCashOUT;

            }
            $finalCashOut = array_column($data, 'cash_out');
            $finalCashIn = array_column($data, 'cash_in');
            if($month_before_dec==true)
                    $data = array_reverse($data, true);
            $sort= $data;
        }else if($searchBy == 3){
            $fiscalYear=[];
            $start_year =  date('Y',strtotime($request->start_date));
            $end_year = date('Y',strtotime($request->end_date));


            $start = new \DateTime($start_year.'-01');
            $start->modify('first day of this month');
            $end = new \DateTime($end_year .'-12');
            $end->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($start, $interval, $end);


            foreach ($period as $dt) {
                $fiscalYear[] =  $dt->format("Y");
            }



            // $quarters = array_chunk($months,3);
            // if($month_n > date('m') && $month_n != 12){
            //     $fiscalYear = [date('Y'),date("Y",strtotime("-1 year"))];
            //  }else if($month_n == 12){
            //     $fiscalYear =[date('Y')];
            //  }else{
            //      $fiscalYear =[date('Y'),date("Y",strtotime("+1 year"))];
            //  }
             $years= $fiscalYear;
             $month = $fiscalMonth[1];
             $cash_in=[];
             $cash_out=[];
             $net_cash=[];

             $years =  array_unique($years);

            foreach($years as $year){
                if($start_year == $end_year){
                    $startDate = date('Y-m-d', strtotime($request->start_date));
                    $endDate = date('Y-m-d', strtotime($request->end_date));
                }else{
                    if($year == $start_year){
                        $startDate = date('Y-m-d', strtotime($request->start_date));
                        $endDate = date('Y-m-d', strtotime('31-12-'.$start_year));
                    }else{
                        $startDate = date('Y-m-d', strtotime('01-01-'.$end_year));
                        $endDate = date('Y-m-d', strtotime($request->end_date));
                    }
                }
                $cashIN = $this->sale_model->fetchCashInYearDataDateWise($startDate,$endDate,$request->user_id);
                $cashOUT = $this->sale_model->fetchCashOutYearDataDateWise($startDate,$endDate,$request->user_id);

                $totalCashIn[] = $cashIN;
                $totalCashOut[] = $cashOUT;
                $totalNetCash[] = $cashIN - $cashOUT;

                $cash_in[($year)]['cash_in'] = $cashIN;
                $cash_out[($year)]['cash_out'] = $cashOUT;
                $net_cash[($year)]['net_cash'] = $cashIN - $cashOUT;
                $data[($year)]['cash_in'] = $cashIN;;//array_sum($totalCashIn);
                $data[($year)]['cash_out'] =  $cashOUT;//array_sum($totalCashOut);
                $data[($year)]['net_cash'] = $cashIN - $cashOUT;//array_sum($totalNetCash);
            }
            $finalCashOut = array_column($data, 'cash_out');
            $finalCashIn = array_column($data, 'cash_in');
            $sort= $data;
        }
        // dd(array_unique($totalCashIn));
        $info['total_cash_in'] = array_sum($finalCashIn);
        $info['total_cash_out'] = array_sum($finalCashOut);
        $info['total_net_cash'] = array_sum($finalCashIn) - array_sum($finalCashOut);
        $info['title'] = $title;
        return makeClientHappy($sort,trans('auth.success'),'info',$info);
    }

    public function SaleOverview(Request $request){
        // if(isset($request->local) && $request->local=="ar"){
        //     app()->setLocale('ar');
        // }
        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);
        //$request->merge(['recorded_by'=>$recorded_by]);
        $fiscalYear = $this->primary_model->getUserFiscalYear($request->user_id);
        if(!empty($fiscalYear)){
            $fiscalMonth = explode("/",$fiscalYear);
        }else{
            return sendErrorToClient("Year ended date required");
        }

        $months=[];
        if($fiscalMonth[1]=="12"){
            $month_n=$fiscalMonth[1];
        }else if($fiscalMonth[1] < date("m")){
            $month_n = $fiscalMonth[1];
        }else{
            $month_n = $fiscalMonth[1]+1;
        }
        //$month_n=($fiscalMonth[1]=="12") ? $fiscalMonth[1]: $fiscalMonth[1];
        $month_before_dec = ($fiscalMonth[1]=="12")? true:false;
        for ($i = 1; $i <= 12; $i++) {
            if($month_n > date('m') && $month_before_dec==false){
                $fiscalMonthCal = " -$i months";
             }else if($month_n == 12 && $month_before_dec==true){
                $fiscalMonthCal = " +$i months";
             }else if($month_before_dec==false){
                $fiscalMonthCal = " +$i months";
             }else{
                 $fiscalMonthCal = " +$i months";
             }

             if($month_n == 12 && $month_before_dec==true){
                $months[] = date("F-2020", strtotime(date('Y')."-".$month_n.$fiscalMonthCal)); //2020
             }else{
                $months[] = date("F-Y", strtotime(date('Y')."-".$month_n.$fiscalMonthCal));
             }
        }

        if($month_n == 12 && $month_before_dec==true){
            $month_title = $months;
         }else if($month_n < date('m')){
            $month_title = $months;
         }else{
            $month_title = $months;
            $month_title = array_reverse($month_title);
         }

        //$title = date('M Y',strtotime($month_title[0]))." - ".date('M Y',strtotime(end($month_title)));
        $title = trans("Months.".date('M',strtotime($request->start_date)))." ". (date('Y',strtotime($request->start_date)))." - ".trans("Months.".date('M',strtotime($request->end_date)))." ". (date('Y',strtotime($request->end_date)));
        // if($month_n==12){
        //     $months = array_reverse($months);
        // }

        $months = array_reverse($months);
        // print_r($months);
        $searchBy = (isset($request->search_by))?$request->search_by:1;
        $data=[];

        if($searchBy == 1){

            if(isset($request->start_date) && isset($request->end_date)){
                $months = getDatesFromRange($request->start_date,date('Y-m-d', strtotime($request->end_date . ' +1 day')));
                $firstDate = $months[0];
                $lastDate = end($months);
                $months = array_reverse($months);
                foreach($months as $month){
                    $getYear = date('Y',strtotime($month));
                    $getShortYear = date('y',strtotime($month));
                    $getMonth = date('m',strtotime($month));
                    $getMonthName = date('M',strtotime($month));
                    $label = trans("Months.".$getMonthName)."-".($getShortYear);
                    $fiscal = $this->sale_model->getFiscalYearMonthData($getYear,$firstDate,$lastDate,$getMonth,$label,$request->user_id);
                    $dateKey = strtoupper(date("M",strtotime($month)));//."-".substr($mon[1],2);
                    $data[$label] = $fiscal[0]->{$label};

                 }

            }
            $sort = array_reverse($data,true);
        //$title = trans("Months.".date('M',strtotime($request->start_date)))." ". (date('y',strtotime($request->start_date)))." - ".trans("Months.".date('M',strtotime($request->end_date)))." ". (date('y',strtotime($request->end_date)));
        }else if($searchBy == 2){
           //$quarters = array_chunk($months,3);
           $quarters = [];

            $start_year =  date('Y',strtotime($request->start_date));
            $end_year = date('Y',strtotime($request->end_date));
            $start_month =  date('m',strtotime($request->start_date));
            $end_month = date('m',strtotime($request->end_date));
            $start = new \DateTime($start_year.'-'.$start_month);
            $start->modify('first day of this month');
            $end = new \DateTime($end_year .'-'.$end_month);
            $end->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($start, $interval, $end);

            foreach ($period as $dt) {
                $quarters[] =  $dt->format("M-Y");
            }
            $quarters = array_reverse($quarters);
            $quarters = array_chunk($quarters,3);

            $yearArr= [date('Y'),date("Y",strtotime("+1 year"))];

            foreach($quarters as $key => $quarter){

                //$quarters = array_reverse($quarters);
                $onlyYear = $quarter[0];
                $onlyMonth =end($quarter);

                $first_array = explode("-",$onlyMonth);
                $last_array = explode("-",$onlyYear);

                //print_r($last_array);

                $label =  strtoupper(trans("Months.".substr($first_array[0],0,3)))."-".strtoupper(trans("Months.".substr($last_array[0],0,3)))."-".$first_array[1];//date('y',strtotime($first_array[1]));
               // echo $onlyMonth."=".$onlyYear."\n";
                $years = $yearArr;
                $labelRep = str_replace("-","_",$label);
                $fiscal = $this->sale_model->getFiscalYearQuarterData($first_array,$last_array,$label,$request->user_id,$request->all(),$first_array[1]);
                //echo $labelRep;
                $label =  strtoupper(trans("Months.".substr($first_array[0],0,3))).','.substr($first_array[1], -2)."-".strtoupper(trans("Months.".substr($last_array[0],0,3)))."-".','.substr($last_array[1], -2);//date('y',strtotime($first_array[1]));
                $data[$label] = $fiscal[0]->{$labelRep};
              //  $data = array_reverse($data);
              $sort = array_reverse($data,true);
            }
        }else if($searchBy == 3){
            $fiscalYear=[];
            $start_year =  date('Y',strtotime($request->start_date));
            $end_year = date('Y',strtotime($request->end_date));

             $start = new \DateTime($start_year.'-01');
             $start->modify('first day of this month');
             $end = new \DateTime($end_year .'-12');
             $end->modify('first day of next month');
             $interval = \DateInterval::createFromDateString('1 month');
             $period = new \DatePeriod($start, $interval, $end);

             foreach ($period as $dt) {
                 $fiscalYear[] =  $dt->format("Y");
             }


            $years= $fiscalYear;
            $final_amount=[];
            foreach($years as $key=>$year){
                $fiscal = $this->sale_model->getFiscalYearData($year,$month_n,$request->user_id,$request->all());
                $final_amount[] = $fiscal[0]->{$year};
                $data[$year] = $fiscal[0]->{$year};
            }
            $sort = $data;
        }

        $info = $title;


        return makeClientHappy($sort,trans('auth.success'),'title',$info);
        //return makeClientHappy($sort,'success');
    }

    public function CashBasisIncomeStatement(Request $request){
        // if(isset($request->local) && $request->local=="ar"){
        //     app()->setLocale('ar');
        // }
        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);
       // $request->merge(['recorded_by'=>$recorded_by]);
        $fiscalYear = $this->primary_model->getUserFiscalYear($request->user_id);
        $searchBy = (isset($request->search_by))?$request->search_by:1;
        if(!empty($fiscalYear)){
            $fiscalMonth = explode("/",$fiscalYear);
        }else{
            return sendErrorToClient("Year ended date required");
        }

        $data=[];
        $months=[];
        if($fiscalMonth[1]=="12"){
            $month_n=$fiscalMonth[1];
        }else if($fiscalMonth[1] < date("m")){
            $month_n = $fiscalMonth[1];
        }else{
            $month_n = $fiscalMonth[1]+1;
        }
        $month_before_dec = ($fiscalMonth[1]=="12")? true:false;
        for ($i = 1; $i <= 12; $i++) {
            if($month_n > date('m') && $month_before_dec==false){
                $fiscalMonthCal = " -$i months";
             }else if($month_n == 12 && $month_before_dec==true){
                $fiscalMonthCal = " +$i months";
             }else if($month_before_dec==false){
                $fiscalMonthCal = " +$i months";
             }else{
                 $fiscalMonthCal = " +$i months";
             }

             if($month_n == 12 && $month_before_dec==true){
                $months[] = date("F-2020", strtotime(date('Y')."-".$month_n.$fiscalMonthCal));
             }else{
                $months[] = date("F-Y", strtotime(date('Y')."-".$month_n.$fiscalMonthCal));
             }
        }

        if($month_n == 12 && $month_before_dec==true){
            $month_title = $months;
         }else if($month_n < date('m')){
            $month_title = $months;
         }else{
            $month_title = $months;
            $month_title = array_reverse($month_title);
         }




         if(isset($request->start_date) && isset($request->end_date)){
            $title = trans("Months.".date('M',strtotime($request->start_date)))." ". (date('Y',strtotime($request->start_date)))." - ".trans("Months.".date('M',strtotime($request->end_date)))." ". (date('Y',strtotime($request->end_date)));
        }else{
            $title = getMonthTitle($months,$month_n,$month_before_dec);
        }

        //$title = date('M Y',strtotime($month_title[0]))." - ".date('M Y',strtotime(end($month_title)));
       // $title = trans("Months.".date('M',strtotime($month_title[0])))." ". (date('Y',strtotime($month_title[0])))." - ".trans("Months.".date('M',strtotime(end($month_title))))." ". (date('Y',strtotime(end($month_title))));

        $totalCashIn=[];
        $totalCashOut=[];
        $totalNetCash=[];
        $byCategory='';
        $fixedCategory='';
        $variableCategory='';
        $onlymonth = [];
        $onlyyear = [];
        $fixed=[];
        $variable=[];

        // if($month_n==12){
            //$months = array_reverse($months);
        // }
        $revenue=[];
        if($searchBy == 1){

            if(!empty($months)){
                $count = 0;

                if(isset($request->start_date) && isset($request->end_date)){
                    $months = getDatesFromRange($request->start_date,date('Y-m-d', strtotime($request->end_date . ' +1 day')));
                }


                $months = array_reverse($months);

                $firstDate = end($months);
                $lastDate = $months[0];

                foreach($months as $month){
                    $mon = explode("-",$month);
                    $mn = date('m',strtotime($mon[0]));

                    $getYear = date('Y',strtotime($month));
                    $getShortYear = date('y',strtotime($month));
                    $getMonth = date('m',strtotime($month));
                    $getMonthName = date('M',strtotime($month));
                    $label = trans("Months.".$getMonthName)."-".($getShortYear);

                    $mon = explode("-",$month);
                    $mn = $getMonthName;
                    $fetchCashIn = $this->sale_model->fetchCashIn($getYear,$getMonth,$request->user_id,$firstDate,$lastDate);
                    $fetchCashOut = $this->sale_model->fetchCashOut($getYear,$getMonth,$request->user_id,$firstDate,$lastDate,'amount',true);
                    $totalCashIn[] = $fetchCashIn;
                    $totalCashOut[] = $fetchCashOut;
                    $totalNetCash[] = $fetchCashIn - $fetchCashOut;
                    $dateKey = strtoupper(trans("Months.".$getMonthName))."-".$getShortYear;//."-".substr($mon[1],2);
                    $data[$dateKey]['revenue'] = $fetchCashIn;
                    $data[$dateKey]['expense'] = $fetchCashOut;
                    $data[$dateKey]['income'] = $fetchCashIn - $fetchCashOut;
                    $onlymonth[] = $getMonth;
                    $onlyyear[] =$getYear;
                }
                $year = array_values(array_unique($onlyyear));
                if($month_n < date('m')){
                    $lastMonth = $onlymonth[0];
                    $firstMonth = end($onlymonth);
                    $year = array_reverse($year);
                }else{
                    $firstMonth = end($onlymonth);
                    $lastMonth = $onlymonth[0];
                }

                $byCategory = $this->sale_model->getDataByCategories($year,$firstMonth,$lastMonth,$request->user_id,$month_n);
                $fixedCategory = $this->expense_model->getDataByCategories($year,$firstMonth,$lastMonth,$request->user_id,6,$month_n);
                $variableCategory=$this->expense_model->getDataByCategories($year,$firstMonth,$lastMonth,$request->user_id,7,$month_n);
            }
        }else if($searchBy == 2){

            $quarters = [];

            // $start_year =  date('Y',strtotime($request->start_date));
            // $end_year = date('Y',strtotime($request->end_date));

            // $start = new \DateTime($start_year.'-01');
            // $start->modify('first day of this month');
            // $end = new \DateTime($end_year .'-12');
            // $end->modify('first day of next month');
            // $interval = \DateInterval::createFromDateString('1 month');
            // $period = new \DatePeriod($start, $interval, $end);

            $start    = (new \DateTime($request->start_date))->modify('first day of this month');
            $end      = (new \DateTime($request->end_date))->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period   = new \DatePeriod($start, $interval, $end);


            foreach ($period as $dt) {
                $quarters[] =  $dt->format("M-Y");
            }
            //$quarters = array_chunk($months,3);
            // if($month_n==12 || $month_n < date('m'))
            //     $quarters = array_reverse($quarters);



            $quarters = array_reverse($quarters);

            $quarters = array_chunk($quarters,3);

            //$quarters = array_reverse($quarters);
            $yearArr= [date('Y'),date("Y",strtotime("+1 year"))];

            // $quarters = array_chunk($months,3);
            // $yearArr= [date('Y'),date("Y",strtotime("+1 year"))];
            // if($month_n==12 || $month_n < date('m'))
            //     $quarters = array_reverse($quarters);

            foreach($months as $month){
                $mon = explode("-",$month);
                $mn = date('m',strtotime($mon[0]));
                $onlymonth[] = $mn;
                $onlyyear[] = $mon[1];
            }

            // $first_month = $onlymonth[0];
            // $last_month = end($onlymonth);

            $year = array_values(array_unique($onlyyear));



            foreach($quarters as $key=>$quarter){

                    $onlyYear = end($quarter);
                    $onlyMonth =$quarter[0];
                    $first_array = explode("-",$onlyYear);
                    $last_array = explode("-",$onlyMonth);
                    //$label =  strtoupper(substr($last_array[0],0,3))."-".strtoupper(substr($first_array[0],0,3));
                    //$label =  strtoupper(trans("Months.".substr($last_array[0],0,3)))."-".strtoupper(trans("Months.".substr($first_array[0],0,3)));
                    $label =  strtoupper(trans("Months.".substr($first_array[0],0,3))).','.substr($first_array[1], -2)."-".strtoupper(trans("Months.".substr($last_array[0],0,3))).','.substr($last_array[1], -2);

                    $first_month = 01;
                    $last_month = 12;

                // if($month_n==12){
                //     $onlyYear = $quarter[0];
                //     $onlyMonth =end($quarter);
                //     $first_array = explode("-",$onlyMonth);
                //     $last_array = explode("-",$onlyYear);
                //     //$label =  strtoupper(substr($last_array[0],0,3))."-".strtoupper(substr($first_array[0],0,3));
                //     //$label =  strtoupper(trans("Months.".substr($last_array[0],0,3)))."-".strtoupper(trans("Months.".substr($first_array[0],0,3)));
                //     $label =  strtoupper(trans("Months.".substr($first_array[0],0,3)))."-".strtoupper(trans("Months.".substr($last_array[0],0,3)))."-".$last_array[1];

                //     $first_month = 01;
                //     $last_month = 12;

                // }else if($month_n < date('m')){

                //     $onlyMonth = $quarter[0];
                //     $onlyYear =end($quarter);
                //     $last_array = explode("-",$onlyMonth);
                //     $first_array = explode("-",$onlyYear);

                //     // $last_array = explode("-",end($quarter));
                //     // $first_array = explode("-",array_values($quarter)[0]);

                //     //$label =  strtoupper(substr($last_array[0],0,3))."-".strtoupper(substr($first_array[0],0,3));
                //     //$label =  strtoupper(trans("Months.".substr($last_array[0],0,3)))."-".strtoupper(trans("Months.".substr($first_array[0],0,3)));
                //     $label =  strtoupper(trans("Months.".substr($first_array[0],0,3)))."-".strtoupper(trans("Months.".substr($last_array[0],0,3)))."-".$last_array[1];

                //     $first_month = date('m',strtotime($first_array[0]));
                //     $last_month = date('m',strtotime($last_array[0]));

                //    // echo $first_month."==".$last_month."\n";
                // }else{
                //     $onlyYear = $quarter[0];
                //     $onlyMonth =end($quarter);
                //     $last_array = explode("-",$onlyMonth);
                //     $first_array = explode("-",$onlyYear);

                //     $first_month = date('m',strtotime($first_array[0]));
                //     $last_month = date('m',strtotime($last_array[0]));
                //     //$label =  strtoupper(substr($last_array[0],0,3))."-".strtoupper(substr($first_array[0],0,3));
                //     //$label =  strtoupper(trans("Months.".substr($last_array[0],0,3)))."-".strtoupper(trans("Months.".substr($first_array[0],0,3)));
                //    $label =  strtoupper(trans("Months.".substr($first_array[0],0,3)))."-".strtoupper(trans("Months.".substr($last_array[0],0,3)))."-".$last_array[1];

                // }

               //echo $first_month."===".$last_month;
                // $onlyMonth = $quarter[0];
                // $onlyYear =end($quarter);


                // $last_array = explode("-",$onlyYear);
                // $first_array = explode("-",$onlyMonth);

                // $first_array = explode("-",array_values($quarter)[0]);
                // $label =  $last_array[0]."-".$first_array[0];
                // // echo $label;
                // $last_array = explode("-",end($quarter));
                $years = $yearArr;
                $labelRep = $label;// str_replace("-","_",$label);

                $totalCashIn[] =$this->sale_model->fetchCashInStatementQuarterly($first_array,$last_array,'amount',$request->user_id);
                $totalCashOut[] =$this->sale_model->fetchCashOutStatementQuarterly($first_array,$last_array,'amount',$request->user_id,true,$month_n);
                $totalNetCash[] = $this->sale_model->fetchCashInStatementQuarterly($first_array,$last_array,'amount',$request->user_id) - $this->sale_model->fetchCashOutStatementQuarterly($first_array,$last_array,'amount',$request->user_id,$month_n);
                $data[$labelRep]['revenue'] =  $this->sale_model->fetchCashInStatementQuarterly($first_array,$last_array,'amount',$request->user_id);
                $data[$labelRep]['expense'] =  $this->sale_model->fetchCashOutStatementQuarterly($first_array,$last_array,'amount',$request->user_id,true,$month_n);
                $data[$labelRep]['income'] =  $this->sale_model->fetchCashInStatementQuarterly($first_array,$last_array,'amount',$request->user_id) - $this->sale_model->fetchCashOutStatementQuarterly($first_array,$last_array,'amount',$request->user_id,true,$month_n);
            }

            $byCategory = $this->sale_model->getDataByCategories($year,$first_month,$last_month,$request->user_id,$month_n);
            $fixedCategory = $this->expense_model->getDataByCategories($year,$first_month,$last_month,$request->user_id,6,$month_n);
            $variableCategory=$this->expense_model->getDataByCategories($year,$first_month,$last_month,$request->user_id,7,$month_n);
        }else if($searchBy == 3){
            // $quarters = array_chunk($months,3);
            // $onlyyear=[];
            // foreach($months as $month){
            //     $mon = explode("-",$month);
            //     $mn = date('m',strtotime($mon[0]));
            //     $onlymonth[] = $mn;
            //     $onlyyear[] = $mon[1];
            // }
            // $year = array_values(array_unique($onlyyear));
            // $yearOnly = array_unique($onlyyear);

            // foreach($quarters as $key=>$quarter){
            //     $onlyMonth = $quarter[0];
            //     $onlyYear =end($quarter);


            //     $first_array = explode("-",$onlyMonth);
            //     $last_array = explode("-",$onlyYear);

            //     $label =  $first_array[0]."-".$last_array[0];


            //     $first_array = explode("-",array_values($quarter)[0]);
            //     $last_array = explode("-",end($quarter));
            //     $label =  $first_array[0]."-".$last_array[0];
            //     // $first_month = date('m',strtotime($first_array[0]));
            //     // $last_month = date('m',strtotime($last_array[0]));
            //     $labelRep = str_replace("-","_",$label);
            // }
            // if($month_n > date('m') && $month_n != 12){
            //     $fiscal = [date('Y'),date("Y",strtotime("-1 year"))];
            //  }else if($month_n == 12){
            //     $fiscal =[date('Y')];
            //  }else{
            //      $fiscal =[date('Y'),date("Y",strtotime("+1 year"))];
            //  }

            // $years= $fiscal;

            $fiscalYear=[];
            $start_year =  date('Y',strtotime($request->start_date));
            $end_year = date('Y',strtotime($request->end_date));


            $start = new \DateTime($start_year.'-01');
            $start->modify('first day of this month');
            $end = new \DateTime($end_year .'-12');
            $end->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($start, $interval, $end);


            foreach ($period as $dt) {
                $fiscalYear[] =  $dt->format("Y");
            }

             $years= $fiscalYear;
             $month = $fiscalMonth[1];


            $rev=[];
            $exp=[];
            $inc=[];
            $years =  array_unique($years);
            foreach($years as $year){
                if($start_year == $end_year){
                    $startDate = date('Y-m-d', strtotime($request->start_date));
                    $endDate = date('Y-m-d', strtotime($request->end_date));
                }else{
                    if($year == $start_year){
                        $startDate = date('Y-m-d', strtotime($request->start_date));
                        $endDate = date('Y-m-d', strtotime('31-12-'.$start_year));
                    }else{
                        $startDate = date('Y-m-d', strtotime('01-01-'.$end_year));
                        $endDate = date('Y-m-d', strtotime($request->end_date));
                    }
                }

                $cashIN = $this->sale_model->fetchCashInYearDataDateWise($startDate,$endDate,$request->user_id);
                $cashOUT = $this->sale_model->fetchCashOutYearDataDateWise($startDate,$endDate,$request->user_id);



                $totalCashIn[] = $cashIN;
                $totalCashOut[] = $cashOUT;
                $totalNetCash[] = $cashIN - $cashOUT;
                $onlyyear[] = $year;
                $rev[$year]['revenue'] = $cashIN;
                $exp[$year]['expense'] = $cashOUT;
                $inc[$year]['income'] = $cashIN - $cashOUT;
                $data[$year]['revenue'] = $cashIN;
                $data[$year]['expense'] = $cashOUT;;
                $data[$year]['income'] = $cashIN - $cashOUT;
            }

            // if(count($years) > 1){
            //     $years =  array_reverse($years);
            // }



            if($month_n < date('m')){
                $last_month = $month;
                $first_month =$month;
                $years = array_reverse($years);
            }else{
                $first_month = $month;
                $last_month = $month;
                $years = array_reverse($years);
            }

            $byCategory = $this->sale_model->getDataByCategories($years,$first_month,$last_month,$request->user_id,$month_n);
            $fixedCategory = $this->expense_model->getDataByCategories($years,$first_month,$last_month,$request->user_id,6,$month_n);
            $variableCategory=$this->expense_model->getDataByCategories($years,$first_month,$last_month,$request->user_id,7,$month_n);
        }
        foreach($byCategory as $Category){
            $revenue[]=$Category->amount;
        }
        foreach($fixedCategory as $Category){
            $fixed[]=$Category->amount;
        }
        foreach($variableCategory as $Category){
            $variable[]=$Category->amount;
        }
        $revenue_total = array_sum($revenue);

        $fixed_total = array_sum($fixed);
        $variable_total = array_sum($variable);

        $info['revenue']['cateogries'] = $byCategory;//["sale_of_good"=>'142,055',"service_to_customer"=>'67,055']; //
        $info['revenue']['total'] = $revenue_total;
        $info['expenses']['cateogries'][] = (object)['title' => trans("categories.fixed_expense"),'categories'=>$fixedCategory];// ["rent"=>'142,055',"salaries"=>'67,055'];
        $info['expenses']['cateogries'][] = (object)['title' => trans("categories.variable_expense"),'categories'=>$variableCategory];
        $info['expenses']['total'] = $fixed_total + $variable_total;
        $totalIncome = array_sum($totalCashIn);
        $info['total_revenue'] = $revenue_total;
        $info['total_expense'] = $fixed_total+$variable_total;
        $info['total_income'] = $revenue_total - ($fixed_total + $variable_total);
        $info['title'] = $title;
        $sort= array_reverse($data,true);
        return makeClientHappy($sort,trans('auth.success'),'info',$info);
    }

    public function DashboardOverview(Request $request){
        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);
        //$request->merge(['recorded_by'=>$recorded_by]);


        $info=[];
        $fiscalYear = $this->primary_model->getUserFiscalYear($request->user_id);
        $searchBy = (isset($request->search_by))?$request->search_by:1;
        if(!empty($fiscalYear)){
            $fiscalMonth = explode("/",$fiscalYear);
        }else{
            return sendErrorToClient("Year ended date required");
        }

        $data=[];
        $months=[];
        if($fiscalMonth[1]=="12"){
            $month_n=$fiscalMonth[1];
        }else if($fiscalMonth[1] < date("m")){
            $month_n = $fiscalMonth[1];
        }else{
            $month_n = $fiscalMonth[1]+1;
        }

        $month_before_dec = ($fiscalMonth[1]=="12")? true:false;

        for ($i = 1; $i <= 12; $i++) {
            if($month_n > date('m') && $month_before_dec==false){
                $fiscalMonthCal = " -$i months";
             }else if($month_n == 12 && $month_before_dec==true){
                $fiscalMonthCal = " +$i months";
             }else if($month_before_dec==false){
                $fiscalMonthCal = " +$i months";
             }else{
                 $fiscalMonthCal = " +$i months";
             }


             if($month_n == 12 && $month_before_dec==true){
                $months[] = date("F-".date("Y"), strtotime(date('Y')."-".$month_n.$fiscalMonthCal));
             }else{
                $months[] = date("F-Y", strtotime(date('Y')."-".$month_n.$fiscalMonthCal));
             }
        }
        $totalCashIn=[];
        $totalCashOut=[];
        $totalNetCash=[];

        // $months = array_reverse($months);
        if($month_n == 12 && $month_before_dec==true){
            $months= array_reverse($months);
        }
        if($searchBy == 1){
            if(!empty($months)){
                // if($month_n == 12 && $month_before_dec==true){
                //     $months = $months;
                // }else if($month_n == 3){
                //     $months = array_reverse($months);
                // }else{
                //     $months = array_reverse($months);
                // }

                //comment by zuhair
                // if($month_before_dec==true){
                //     $months = array_reverse($months);
                // }
                // if($month_n <= 3){
                //     $months = array_reverse($months);
                // }


                // else{
                //     $months = array_reverse($months);
                // }

                // dd($months);


                $firstDate = $months[0];
                $lastDate = end($months);

                $firstDate = date('Y-m-d',strtotime($firstDate));
                $lastDate = date('Y-m-d',strtotime($lastDate));

                // $months= array_reverse($months);
                foreach($months as $month){
                    $mon = explode("-",$month);
                    $mn = date('m',strtotime($mon[0]));
                    $netCash = $this->sale_model->DashboardMonthlyCashIn($mon[1],$mn,$request->user_id,$firstDate,$lastDate,'amount') - $this->sale_model->DashboardMonthlyCashOut($mon[1],$mn,$request->user_id,$firstDate,$lastDate,'amount',true);

                    $totalNetCash[] = $netCash;//$this->sale_model->fetchCashIn($mon[1],$mn,$request->user_id,'received_amount') - $this->sale_model->fetchCashOut($mon[1],$mn,$request->user_id,'amount_paid',true);
                    $dateKey = strtoupper(trans("Months.".date("M",strtotime($mon[0]))))."-".$mon[1];

                    // $data[$mon[0]] = ($netCash < 0 ? "(".abs($netCash).")" : $netCash);//$this->sale_model->fetchCashIn($mon[1],$mn,$request->user_id,'received_amount') - $this->sale_model->fetchCashOut($mon[1],$mn,$request->user_id,'amount_paid',true);
                    $data[$dateKey] = $netCash; //$this->sale_model->DashboardMonthlyCashIn($mon[1],$mn,$request->user_id,$firstDate,$lastDate,'amount') - $this->sale_model->DashboardMonthlyCashOut($mon[1],$mn,$request->user_id,$firstDate,$lastDate,'amount',true);
                }
            }
        } else if($searchBy == 2){
            if($month_n == 12 && $month_before_dec==true){
                $months= array_reverse($months);
            }
            // if($month_n == 12 && $month_before_dec==true){
            //     $months = $months;
            // }else{
            // }
            // if($month_before_dec==true){
            //     $months = array_reverse($months);
            // }
            // if($month_n <= 3){
            //     $months = array_reverse($months);
            // }
            $quarters = array_chunk($months,3);

            $yearArr = [date('Y'),date("Y",strtotime("+1 year"))];

            // if($month_n == 12 && $month_before_dec==true){
            //     $quarters = $quarters;
            // }else{
            //     // $quarters = array_reverse($quarters);
            // }
            foreach($quarters as $key => $quarter){
                // if($month_n == 12 && $month_before_dec==true){
                //     $onlyMonth = current($quarter); //April-2021
                //     $onlyYear =end($quarter); //June-2021
                // }else{
                //     $onlyMonth = current($quarter); //April-2021
                //     $onlyYear =end($quarter); //June-2021
                // }
                $onlyMonth = current($quarter); //April-2021
                $onlyYear = end($quarter); //June-2021
                $first_array = explode("-",$onlyMonth);
                $last_array = explode("-",$onlyYear);
                if($month_before_dec==true){
                    $label =  strtoupper(trans("Months.".substr($first_array[0],0,3))).','.substr($first_array[1], -2)."-".strtoupper(trans("Months.".substr($last_array[0],0,3))).','.substr($last_array[1], -2);
                }else{
                    $label =  strtoupper(trans("Months.".substr($last_array[0],0,3))).','.substr($last_array[1], -2)."-".strtoupper(trans("Months.".substr($first_array[0],0,3))).','.substr($first_array[1], -2);
                }


                $years = $yearArr;
                $labelRep = $label;//str_replace("-","_",$label);
                //$netCash = $this->sale_model->DashboardCashInQuarterly($first_array,$last_array,'received_amount',$request->user_id,$request,true);
                if($month_before_dec==true){
                    $netCash = $this->sale_model->DashboardCashInQuarterly($last_array,$first_array,'amount',$request->user_id,$request,true) - $this->sale_model->DashboardCashOutQuarterly($last_array,$first_array,'amount',$request->user_id,true,$request);
                }else{
                    $netCash = $this->sale_model->DashboardCashInQuarterly($first_array,$last_array,'amount',$request->user_id,$request,true) - $this->sale_model->DashboardCashOutQuarterly($first_array,$last_array,'amount',$request->user_id,true,$request);
                }
                $totalNetCash[] = $netCash;//$this->sale_model->fetchCashInQuarterly($first_array,$last_array,'received_amount',$request->user_id) - $this->sale_model->fetchCashOutQuarterly($first_array,$last_array,'amount_paid',$request->user_id,true);
                // $data[$labelRep] = ($netCash < 0 ? "(".abs($netCash).")" : $netCash);//$this->sale_model->fetchCashInQuarterly($first_array,$last_array,'received_amount',$request->user_id) - $this->sale_model->fetchCashOutQuarterly($first_array,$last_array,'amount_paid',$request->user_id,true);
                $data[$labelRep] = $netCash; //$this->sale_model->DashboardCashInQuarterly($first_array,$last_array,'received_amount',$request->user_id,$request,true) - $this->sale_model->DashboardCashOutQuarterly($first_array,$last_array,'amount_paid',$request->user_id,true,$request);
            }
            if($month_before_dec==true){
                $data= array_reverse($data);
            }
        } else if($searchBy == 3){
            $quarters = array_chunk($months,3);
            if($month_n > date('m') && $month_n != 12){
                $fiscalYear = [date('Y'),date("Y",strtotime("-1 year"))];
             }else if($month_n == 12){
                $fiscalYear =[date('Y')];
             }else{
                 $fiscalYear =[date('Y'),date("Y",strtotime("+1 year"))];
             }
             $years= $fiscalYear;
             $over=[];
            foreach($years as $year){
                $DashboardCashYearDataTotal = $this->sale_model->DashboardCashInYearData($year,$month_n,$request->user_id,'amount') - $this->sale_model->DashboardCashOutYearData($year,$month_n,$request->user_id,'amount',true);
                $totalNetCash[] = $DashboardCashYearDataTotal; //$this->sale_model->DashboardCashInYearData($year,$month_n,$request->user_id,'received_amount') - $this->sale_model->DashboardCashOutYearData($year,$month_n,$request->user_id,'amount_paid',true);
                $over[$year] = $DashboardCashYearDataTotal; //$this->sale_model->DashboardCashInYearData($year,$month_n,$request->user_id,'received_amount') - $this->sale_model->DashboardCashOutYearData($year,$month_n,$request->user_id,'amount_paid',true);
            }
            // if(count($years) > 1){
            //     $years = array_reverse($years);
            // }
            $NetCash = array_sum($totalNetCash);
            // $data[$years[0]] = ($NetCash < 0 ? "(".abs($NetCash).")" : $NetCash);
            $dataLabel = (end($years) == $years[0]) ? $years[0] : end($years).'-'.$years[0];
            $data[$dataLabel] = $NetCash;
        }
        $totalNetCash = array_sum($totalNetCash);

        $info['net_cash'] = $totalNetCash;//($totalNetCash < 0 ? "(".abs($totalNetCash).")" : $totalNetCash);
        $sort = array_reverse($data,true);
        return makeClientHappy($sort,trans('auth.success'),'info',$info);
    }

    public function DashboardOverviewZuhair(Request $request){
        $parent_id = getParentId('app_users','id',$request->user_id);
        $user_id = ($parent_id !=0)?$parent_id:$request->user_id;
        $request->merge(['user_id'=>$user_id]);
        $fiscalYear = $this->primary_model->getUserFiscalYear($request->user_id);
        $searchBy = (isset($request->search_by))?$request->search_by:1;
        if(!empty($fiscalYear)){
            $fiscalMonth = explode("/",$fiscalYear);
        }else{
            return sendErrorToClient("Year ended date required");
        }
        $data = $months =[];
        if($fiscalMonth[1]=="12"){
            $month_n=$fiscalMonth[1];
        }else if($fiscalMonth[1] < date("m")){
            $month_n = $fiscalMonth[1];
        }else{
            $month_n = $fiscalMonth[1]+1;
        }
        $month_before_dec = ($fiscalMonth[1]=="12")? true:false;
        for ($i = 1; $i <= 12; $i++) {
            if($month_n > date('m') && $month_before_dec==false){
                $fiscalMonthCal = " -$i months";
             }else if($month_n == 12 && $month_before_dec==true){
                $fiscalMonthCal = " +$i months";
             }else if($month_before_dec==false){
                $fiscalMonthCal = " +$i months";
             }else{
                 $fiscalMonthCal = " +$i months";
             }

             if($month_n == 12 && $month_before_dec==true){
                $months[] = date("F-".date("Y"), strtotime(date('Y')."-".$month_n.$fiscalMonthCal));
             }else{
                $months[] = date("F-Y", strtotime(date('Y')."-".$month_n.$fiscalMonthCal));
             }
        }
        if(isset($request->start_date) && isset($request->end_date)){
            $title = trans("Months.".date('M',strtotime($request->start_date)))." ". (date('Y',strtotime($request->start_date)))." - ".trans("Months.".date('M',strtotime($request->end_date)))." ". (date('Y',strtotime($request->end_date)));
        }else{
            $title = getMonthTitle($months,$month_n,$month_before_dec);
        }
        $totalNetCash=[];
        if($searchBy == 1){
            if(isset($request->start_date) && isset($request->end_date)){
                $months = get_month_between_two_datetime($request->start_date,$request->end_date);
            }
            $firstDate = date('Y-m-d',strtotime(end($months)));
            $lastDate = date('Y-m-d',strtotime($months[0]));
            foreach($months as $month){
                $mon = explode("-",$month);
                $mn = $mon[0];
                $netCash = $this->sale_model->DashboardMonthlyCashIn($mon[1],$mn,$request->user_id,$firstDate,$lastDate,'amount') - $this->sale_model->DashboardMonthlyCashOut($mon[1],$mn,$request->user_id,$firstDate,$lastDate,'amount',true);
                $totalNetCash[] = $netCash;
                $dateKey = strtoupper(trans("Months.".date('M', mktime(0, 0, 0, $mon[1], 10))))."-".$mon[0];
                $data[$dateKey] = $netCash;
            }
        }elseif($searchBy == 2){
            $quarters = [];
            $start_year =  date('Y',strtotime($request->start_date));
            $end_year = date('Y',strtotime($request->end_date));
            $start_month =  date('m',strtotime($request->start_date));
            $end_month = date('m',strtotime($request->end_date));
            $start = new \DateTime($start_year.'-'.$start_month);
            $start->modify('first day of this month');
            $end = new \DateTime($end_year .'-'.$end_month);
            $end->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($start, $interval, $end);
            foreach ($period as $dt) {
                $quarters[] =  $dt->format("M-Y");
            }
            if($month_before_dec==true)
                $quarters = array_reverse($quarters);
            $quarters = array_chunk($quarters,3);
            $yearArr= [date('Y'),date("Y",strtotime("+1 year"))];
            foreach($quarters as $key => $quarter){
                $onlyMonth = current($quarter); //April-2021
                $onlyYear = end($quarter); //June-2021
                $first_array = explode("-",$onlyMonth);
                $last_array = explode("-",$onlyYear);
                // dd($month_before_dec);
                // $label =  strtoupper(trans("Months.".substr($first_array[0],0,3))).','.substr($first_array[1], -2)."-".strtoupper(trans("Months.".substr($last_array[0],0,3))).','.substr($last_array[1], -2);
                // if($month_before_dec==true){
                //     $label =  strtoupper(trans("Months.".substr($first_array[0],0,3))).','.substr($first_array[1], -2)."-".strtoupper(trans("Months.".substr($last_array[0],0,3))).','.substr($last_array[1], -2);
                // }else{
                //     $label =  strtoupper(trans("Months.".substr($last_array[0],0,3))).','.substr($last_array[1], -2)."-".strtoupper(trans("Months.".substr($first_array[0],0,3))).','.substr($first_array[1], -2);
                // }
                $label = "Q-".($key + 1);
                $years = $yearArr;
                $labelRep = $label;
                if($month_before_dec==true){
                    $netCash = $this->sale_model->DashboardCashInQuarterly($last_array,$first_array,'amount',$request->user_id,$request,true) - $this->sale_model->DashboardCashOutQuarterly($last_array,$first_array,'amount',$request->user_id,true,$request);
                }else{
                    $netCash = $this->sale_model->DashboardCashInQuarterly($first_array,$last_array,'amount',$request->user_id,$request,true) - $this->sale_model->DashboardCashOutQuarterly($first_array,$last_array,'amount',$request->user_id,true,$request);
                }
                $totalNetCash[] = $netCash;
                $data[$labelRep] = $netCash;
            }
        }elseif($searchBy == 3){
            $fiscalYear=[];
            $start_year =  date('Y',strtotime($request->start_date));
            $end_year = date('Y',strtotime($request->end_date));
            $start = new \DateTime($start_year.'-01');
            $start->modify('first day of this month');
            $end = new \DateTime($end_year .'-12');
            $end->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($start, $interval, $end);
            foreach ($period as $dt) {
                $fiscalYear[] =  $dt->format("Y");
            }
            $years= $fiscalYear;
            $month = $fiscalMonth[1];
            $years =  array_unique($years);
            $over=[];
            foreach($years as $year){
                $DashboardCashYearDataTotal = $this->sale_model->DashboardCashInYearData($year,$month_n,$request->user_id,'amount') - $this->sale_model->DashboardCashOutYearData($year,$month_n,$request->user_id,'amount',true);
                $totalNetCash[] = $DashboardCashYearDataTotal;
                $over[$year] = $DashboardCashYearDataTotal;
            }
            $NetCash = array_sum($totalNetCash);
            $dataLabel = (end($years) == $years[0]) ? $years[0] : $years[0] . '-' . end($years);
            $data[$dataLabel] = $NetCash;
        }
        $totalNetCash = array_sum($totalNetCash);
        $info['net_cash'] = $totalNetCash;
        return makeClientHappy($data,trans('auth.success'),'info',$info);
    }

}
