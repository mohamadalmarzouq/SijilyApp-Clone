<?php

namespace App\Http\Controllers\Api;

use App\Http\Validation\RulesStatus as Rules;
use App\Models\Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Expense;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class AccountPayableController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Purchase();
        $this->other_model = new Expense();
        $this->module = $this->primary_model->getTable();
    }

    public function listing(Request $request)
    {
        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);


        $limit = (isset($request->limit) && !empty($request->limit)) ? $request->limit : 10;
        $perPage = 10;
        $input = $request->all();
        if (isset($input['page']) && !empty($input['page'])) { $currentPage = $input['page']; } else { $currentPage = 1; }

        $purchase = Purchase::with(['status','getTransaction.Image','Image'])->where("status_id",8);
        $expense = Expense::with(['status','getTransaction.Image','Image'])->where("status_id",11);

        if(isset($request['start_date']) && isset($request['end_date'])){
            $purchase->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $expense->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
        }else if(isset($request['start_date'])){
            $purchase->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $expense->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
        }else if(isset($request['end_date'])){
            $purchase->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $expense->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
        }

        if(isset($request['vendor_name'])){
            $purchase->where("vendor_name","LIKE",'%'.$request['vendor_name'].'%');
            $expense->where("vendor_name","LIKE",'%'.$request['vendor_name'].'%');
        }

        if(isset($request['search'])){

            $purchase->where(function ($query) use ($request){
                $query->where("desc","LIKE",'%'.$request['search'].'%')
                ->orWhere("asset_name","LIKE",'%'.$request['search'].'%');
            });

            $expense->where(function ($query) use ($request){
                $query->where("desc","LIKE",'%'.$request['search'].'%')
                ->orWhere("title","LIKE",'%'.$request['search'].'%');
            });

            //$purchase->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("asset_name","LIKE",'%'.$request['search'].'%');
           // $expense->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("title","LIKE",'%'.$request['search'].'%');
        }

        if(isset($request['ageing']) && $request['ageing'] == 1){
            $purchase->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))>=0 AND  ABS(DATEDIFF(`date`,'".date('Y-m-d')."')) <=30 ");
            $expense->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))>=0 AND  ABS(DATEDIFF(`date`,'".date('Y-m-d')."')) <=30 ");
        }else if(isset($request['ageing']) && $request['ageing'] == 2){
            $purchase->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))>=30 AND  ABS(DATEDIFF(`date`,'".date('Y-m-d')."')) <=60 ");
            $expense->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))>=30 AND  ABS(DATEDIFF(`date`,'".date('Y-m-d')."')) <=60 ");
        }else if(isset($request['ageing']) && $request['ageing'] == 3){
            $purchase->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))>=60 AND  ABS(DATEDIFF(`date`,'".date('Y-m-d')."')) <=180 ");
            $expense->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))>=60 AND  ABS(DATEDIFF(`date`,'".date('Y-m-d')."')) <=180 ");
        }else if(isset($request['ageing']) && $request['ageing'] == 4){
            $purchase->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))>=180 AND  ABS(DATEDIFF(`date`,'".date('Y-m-d')."')) <=360 ");
            $expense->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))>=180 AND  ABS(DATEDIFF(`date`,'".date('Y-m-d')."')) <=360 ");
        }else if(isset($request['ageing']) && $request['ageing'] == 5){
            $purchase->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))> 360");
            $expense->whereRaw("ABS(DATEDIFF(`date`,'".date('Y-m-d')."'))> 360");
        }

        if(isset($request['is_settled'])){
            $purchase->where("is_settled",$request['is_settled']);
            $expense->where("is_settled",$request['is_settled']);
        }

        if(isset($request['recorded_by'])){
            $purchase->where("recorded_by",$request['recorded_by']);
            $expense->where("recorded_by",$request['recorded_by']);
        }

        $purchases = $purchase->where("user_id",$request->user_id)->get();
        $expenses = $expense->where("user_id",$request->user_id)->get();

        $result = $purchases->merge($expenses)->sortByDesc('id')->paginate($limit)->toArray();

        $fulldata['data'] = array_values($result['data']);

        $data['page'] = $result;
        unset($data['page']['data']);
        $new_row = array_merge($fulldata,$data);
        // return $new_row;
        return PagintionResponse($new_row,trans('auth.success'));
    }

    public function getSchedule(Request $request){
        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){
            $recorded_by = $request->user_id;
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;
            $recorded_by = $request->user_id;
        }
        $writeoff=[];
        $request->merge(['user_id'=>$user_id]);
        $request->merge(['recorded_by'=>$recorded_by]);
        $schedule=[];
        $combine=[];
        $purchases = $this->primary_model->getPurchaseVendor($request->user_id);
        $expenses = $this->other_model->getExpenseVendor($request->user_id);
        $schedule = array_merge($purchases,$expenses);
        $name = array_column($schedule, 'vendor_name');
        $filteredKeys = array_unique($name);

        $total= [];
        $write_off_total=[];
        foreach (array_keys($filteredKeys) as $key => $value) {
            $vendor_name= $schedule[$value]->vendor_name;
            $purchases_amount = $this->primary_model->getPurchaseAmount($vendor_name,$request->user_id);
            $expenses_amount = $this->other_model->getExpenseAmount($vendor_name,$request->user_id);

            $purchases_amount_writeOff = $this->primary_model->getPurchaseAmountWriteOff($vendor_name,$request->user_id);
            $expenses_amount_writeOff = $this->other_model->getExpenseAmountWriteOff($vendor_name,$request->user_id);

            $writeoff_amount = $purchases_amount_writeOff[0]->amount + $expenses_amount_writeOff[0]->amount;
            $total_amount = $purchases_amount[0]->amount + $expenses_amount[0]->amount; //amount

            if($total_amount > 0){
                $combine[]=[
                    'vendor_name'=>$vendor_name,
                    'amount'=>$total_amount,
                ];
             }
            if($writeoff_amount > 0){
                $writeoff[]=[
                    'vendor_name'=>$vendor_name,
                    'amount'=>$writeoff_amount,
                ];
             }
            $total[] = $total_amount;
            $write_off_total[] = $writeoff_amount;
        }
        $data['data']['vendors']= $combine;
        $total_amount = array_sum($total);
        $data['data']['total'] = $total_amount;
        $data['data']['vendors_writeoff']= $writeoff;
        $data['data']['written_off_total']= array_sum($write_off_total);
        return PagintionResponse($data,trans('auth.success'));
    }

    public function getVendorName(Request $request){
        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){
            $recorded_by = $request->user_id;
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;
            $recorded_by = $request->user_id;
        }
        $request->merge(['user_id'=>$user_id]);
        $request->merge(['recorded_by'=>$recorded_by]);
        $schedule=[];
        $combine=[];
        $purchases = $this->primary_model->getPurchaseVendor($request->user_id);
        $expenses = $this->other_model->getExpenseVendor($request->user_id);
        $schedule = array_merge($purchases,$expenses);
        $name = array_column($schedule, 'vendor_name');
        $filteredKeys = array_unique($name);
        $data['data'] =$filteredKeys;
        return PagintionResponse($data,trans('auth.success'));
    }
}
