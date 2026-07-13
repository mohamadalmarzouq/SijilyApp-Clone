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
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Break_;

class OverviewController extends Controller
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

               // $fetchCashIn = $this->sale_model->fetchCashIn($getYear,$getMonth,$request->user_id,$firstDate,$lastDate,'amount',true);
                //$fetchCashOut = $this->sale_model->fetchCashOut($getYear,$getMonth,$request->user_id,$firstDate,$lastDate,'amount',true,true);



                $fetchCashIn = $this->sale_model->fetchCashIn($getYear,$getMonth,$request->user_id,$firstDate,$lastDate,'amount',false);
                $fetchCashOut = $this->sale_model->fetchCashOut($getYear,$getMonth,$request->user_id,$firstDate,$lastDate,'amount',false,false);

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
            $quarters = $this->get_quarters($request->start_date, $request->end_date);
            // dd($quarters);
            foreach($quarters as $key => $quarter){
                $labelRep = $quarter->period;
                $tCashIN = $this->sale_model->fetchingCashInQuarterly($quarter->period_start,$quarter->period_end,'amount',$request->user_id,$request, TRUE);
                $tCashOUT = $this->sale_model->fetchingCashOutQuarterly($quarter->period_start,$quarter->period_end,'amount',$request->user_id,false,$request);
                $totalCashIn[] = $tCashIN;
                $totalCashOut[] = $tCashOUT;
                $totalNetCash[] = $tCashIN - $tCashOUT;
                $data[$labelRep]['cash_in'] = $tCashIN;
                $data[$labelRep]['cash_out'] = $tCashOUT;
                $data[$labelRep]['net_cash'] =  $tCashIN - $tCashOUT;
            }
            $finalCashOut = array_column($data, 'cash_out');
            $finalCashIn = array_column($data, 'cash_in');
            // if($month_before_dec==true)
            //         $data = array_reverse($data, true);
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
                $cashIN = $this->sale_model->fetchCashInYearDataDateWise($startDate,$endDate,$request->user_id,'amount',false);
                $cashOUT = $this->sale_model->fetchCashOutYearDataDateWise($startDate,$endDate,$request->user_id,'amount',false,false);

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

        $title = trans("Months.".date('M',strtotime($request->start_date)))." ". (date('Y',strtotime($request->start_date)))." - ".trans("Months.".date('M',strtotime($request->end_date)))." ". (date('Y',strtotime($request->end_date)));

        $months = array_reverse($months);
        $searchBy = (isset($request->search_by))?$request->search_by:1;
        $data=[];
        $remaining = null;
        $remaining = $this->getRemainingAmount($request,$searchBy);
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
        }else if($searchBy == 2){
            $quarters = $this->get_quarters($request->start_date, $request->end_date);
            foreach($quarters as $key => $quarter){
                $label = $quarter->period;
                $fiscal = $this->sale_model->FiscalYearQuarterData($quarter->period_start,$quarter->period_end,$label,$request->user_id,$request->all());
                $data[$label] = $fiscal[0]->{$label};
                $sort = $data;
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

        //OlD for adding recieved and not recieved
        // $total = $this->getTotal($sort,$remaining);

        $total = $sort;
        return response()->json(['data' => $sort,'not_received_data' => $remaining,'main_invoice' => $total,'message' => trans('auth.success'),'title' => $info]);
        // return makeClientHappy($sort,trans('auth.success'),'title',$info);
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
        $cash_base_income = true;
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

                    $fetchCashIn = $this->sale_model->fetchCashIn($getYear,$getMonth,$request->user_id,$firstDate,$lastDate,'amount',true);
                    $fetchCashOut = $this->sale_model->fetchCashOut($getYear,$getMonth,$request->user_id,$firstDate,$lastDate,'amount',true,true);

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
            $first_month = 01;
            $last_month = 12;
            $start    = (new \DateTime($request->start_date))->modify('first day of this month');
            $end      = (new \DateTime($request->end_date))->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period   = new \DatePeriod($start, $interval, $end);
            foreach($months as $month){
                $mon = explode("-",$month);
                $mn = date('m',strtotime($mon[0]));
                $onlymonth[] = $mn;
                $onlyyear[] = $mon[1];
            }
            $year = array_values(array_unique($onlyyear));
            $quarters = $this->get_quarters($request->start_date, $request->end_date);
            foreach($quarters as $key => $quarter){
                $labelRep = $quarter->period;
                $totalIn = $this->sale_model->fetchingCashInStatementQuarterly($quarter->period_start,$quarter->period_end,'amount',$request->user_id);
                $totalOut = $this->sale_model->fetchingCashOutStatementQuarterly($quarter->period_start,$quarter->period_end,'amount',$request->user_id,true);
                $totalCashIn[] =$totalIn;
                $totalCashOut[] =$totalOut;
                $totalNetCash[] = $totalIn - $totalOut;
                $data[$labelRep]['revenue'] =  $totalIn;
                $data[$labelRep]['expense'] =  $totalOut;
                $data[$labelRep]['income'] =  $totalIn - $totalOut;
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

                $cashIN = $this->sale_model->fetchCashInYearDataDateWise($startDate,$endDate,$request->user_id,'amount',$cash_base_income);
                $cashOUT = $this->sale_model->fetchCashOutYearDataDateWise($startDate,$endDate,$request->user_id,'amount',true,$cash_base_income);



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
        $sort = $data;
        if($searchBy != 2){
            $sort= array_reverse($data,true);
        }

        return makeClientHappy($sort,trans('auth.success'),'info',$info);
    }

    public function DashboardOverview(Request $request){
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
                $netCash = $this->sale_model->DashboardMonthlyCashIn($mon[0],$mon[1],$request->user_id,$firstDate,$lastDate,'amount') - $this->sale_model->DashboardMonthlyCashOut($mon[0],$mon[1],$request->user_id,$firstDate,$lastDate,'amount',true);
                $totalNetCash[] = $netCash;
                $dateKey = strtoupper(trans("Months.".date('M', mktime(0, 0, 0, $mon[1], 10))))."-".$mon[0];
                $data[$dateKey] = $netCash;
            }
        }elseif($searchBy == 2){
            $quarters = $this->get_quarters($request->start_date, $request->end_date);
            foreach($quarters as $key => $quarter){
                $labelRep = $quarter->period;
                $netCash = $this->sale_model->CashInQuarterly($quarter->period_start,$quarter->period_end,'amount',$request->user_id,$request,true) - $this->sale_model->CashOutQuarterly($quarter->period_start,$quarter->period_end,'amount',$request->user_id,true,$request);
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
                $DashboardCashYearDataTotal = $this->sale_model->DashboardCashInYearData($request->start_date,$request->end_date,$year,$month_n,$request->user_id,'amount') - $this->sale_model->DashboardCashOutYearData($request->start_date,$request->end_date,$year,$month_n,$request->user_id,'amount',true);
                $totalNetCash[] = $DashboardCashYearDataTotal;
                $over[$year] = $DashboardCashYearDataTotal;
                $NetCash = array_sum($totalNetCash);
                $dataLabel = $year;
                if($DashboardCashYearDataTotal !=0){
                    $data[$dataLabel] = $DashboardCashYearDataTotal;
                }else{
                    $data[$dataLabel] = 0;
                }
            }
        }
        $totalNetCash = array_sum($totalNetCash);
        $info['net_cash'] = $totalNetCash;
        return makeClientHappy($data,trans('auth.success'),'info',$info);
    }

    private function month_name($month_number){
        return date('F', mktime(0, 0, 0, $month_number, 10));
    }

    private function month_end_date($year, $month_number){
        return date("t", strtotime("$year-$month_number-01"));
    }

    private function zero_pad($number){
        if($number < 10)
            return "0$number";

        return "$number";
    }

    private function get_quarters($start_date, $end_date){
        $quarters = array();
        $start_month = date( 'm', strtotime($start_date) );
        $start_year = date( 'Y', strtotime($start_date) );
        $end_month = date( 'm', strtotime($end_date) );
        $end_year = date( 'Y', strtotime($end_date) );
        $start_quarter = ceil($start_month/3);
        $end_quarter = ceil($end_month/3);
        $quarter = $start_quarter; // variable to track current quarter
        // Loop over years and quarters to create array
        for( $y = $start_year; $y <= $end_year; $y++ ){
            if($y == $end_year)
                $max_qtr = $end_quarter;
            else
                $max_qtr = 4;

            for($q=$quarter; $q<=$max_qtr; $q++){
                $current_quarter = new \stdClass();
                $end_month_num = $this->zero_pad($q * 3);
                $start_month_num = ($end_month_num - 2);
                $q_start_month = $this->month_name($start_month_num);
                $q_end_month = $this->month_name($end_month_num);
                $current_quarter->period = "Q$q ($y)";
                $current_quarter->period_start = "$y-$start_month_num-01";      // yyyy-mm-dd
                $current_quarter->period_end = "$y-$end_month_num-" . $this->month_end_date($y, $end_month_num);
                $quarters[] = $current_quarter;
                ksort($quarters);
                unset($current_quarter);
            }
            $quarter = 1; // reset to 1 for next year
        }
        current($quarters)->period_start = $start_date;
        end($quarters)->period_end = $end_date;
        // rsort($quarters);
        return $quarters;
    }

    public function getRemainingAmount($request, $searchBy)
    {
        $set = [];
        switch ($searchBy) {
            case 1:
                $period = CarbonPeriod::create($request->start_date, $request->end_date)->month();
                $months = collect($period)->map(function (Carbon $date) {
                    return [
                        'name' => $date->shortEnglishMonth,
                        'year' => $date->format('y')
                    ];
                });
                $remaining = DB::table('sales')
                ->select(DB::raw('DATE_FORMAT(date, "%b") as month_name, DATE_FORMAT(date, "%y") as year , CONCAT(DATE_FORMAT(date, "%b") ,"-",DATE_FORMAT(date, "%y")) as label , SUM(remaining_amount) as remaining_amount'))
                ->whereBetween('date', [$request->start_date, $request->end_date])
                    ->where(['user_id' => $request->user_id])
                    ->whereNull('deleted_at')
                    ->groupBy('month_name')
                    // ->toSql();
                    ->get();

                $months->map(function ($item) use (&$set, $remaining) {
                    $exists = $remaining->where('month_name', $item['name'])->first();
                    if ($exists) {
                        $set[$exists->label] = intval($exists->remaining_amount);
                    } else {
                        $set[$item['name'] . '-' . $item['year']] = 0;
                    }
                });
                break;
            case 2:
                $quarters = $this->get_quarters($request->start_date, $request->end_date);
                $remaining = [];
                foreach ($quarters as $q) {
                    $remaining[] = DB::table('sales')
                        ->select(DB::raw('DATE_FORMAT(date, "%b") as month_name, DATE_FORMAT(date, "%y") as year , CONCAT(DATE_FORMAT(date, "%b") ,"-",DATE_FORMAT(date, "%y")) as label , SUM(remaining_amount) as remaining_amount'))
                        ->whereBetween('date', [$q->period_start, $q->period_end])
                        ->where(['user_id' => $request->user_id])
                        ->whereNull('deleted_at')
                        ->groupBy('month_name')
                        // ->toSql();
                        ->get();
                    }
                    if(count($remaining)){
                    foreach ($quarters as $i => $quarter) {
                        $amount = isset($remaining[$i][0]->remaining_amount) ? $remaining[$i][0]->remaining_amount : 0;
                        $set[$quarter->period] = $amount;
                    }
                }
                break;
                case 3:
                $fiscalYear = [];
                $start_year =  date('Y', strtotime($request->start_date));
                $end_year = date('Y', strtotime($request->end_date));

                $start = new \DateTime($start_year . '-01');
                $start->modify('first day of this month');
                $end = new \DateTime($end_year . '-12');
                $end->modify('first day of next month');
                $interval = \DateInterval::createFromDateString('1 month');
                $period = new \DatePeriod($start, $interval, $end);

                foreach ($period as $dt) {
                    $fiscalYear[] =  $dt->format("Y");
                }


                $years = $fiscalYear;
                $remaining = [];
                foreach($years as $key=>$year){
                    $remaining = DB::table('sales')
                    ->select(DB::raw('DATE_FORMAT(date, "%b") as month_name, DATE_FORMAT(date, "%Y") as year , CONCAT(DATE_FORMAT(date, "%b") ,"-",DATE_FORMAT(date, "%y")) as label , SUM(remaining_amount) as remaining_amount'))
                    ->whereRaw('YEAR(`date`) = '.$year)
                    ->whereBetween('date', [$request->start_date, $request->end_date])
                        ->where(['user_id' => $request->user_id])
                        ->whereNull('deleted_at')
                        ->groupBy('year')
                        // ->toSql();
                        ->get();
                    $set[$remaining[0]->{'year'}] = $remaining[0]->{'remaining_amount'};
                }   
                    break;

            default:
                throw new \Exception('Something went wrong');
        }
        return $set;
    }

    public function getTotal($recieved,$not_received)
    {
        $set = [];
        foreach ($recieved as $index => $item) {
            $set[$index] = intval($item) + intval($not_received[$index]);
        }
        return $set;
    }
}
