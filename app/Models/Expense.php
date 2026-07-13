<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = ['date','last_trans_update','sub_cat_name_ar','is_settled','sub_cat_fixed_name_ar','vendor_id','vendor_name','sys_gen_id', 'desc', 'amount', 'status_id', 'user_id', 'sub_cat_id','sub_cat_type','amount_paid','remaining_amount','title','sub_cat_name','sub_cat_fixed_name','recorded_by'];

    protected $guarded = [];

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function getExpenses($id)
    {
        return $this->with(['Image','getTransaction.Image'])->findOrFail($id);
    }
    public function getTransaction(){
        return $this->hasMany(Transaction::class, 'ref_id', 'id')->with('Image')->where('amount','!=',0)->where('type','expense')->whereNull('deleted_at');
    }

    public function apiListing($request,$user_id, $limit)
    {
        $data=[];

        $results = $this->with(['Image','status','getTransaction.Image'])
            ->whereNull('deleted_at');

        $total_amount = $this->whereNull('deleted_at');
        $amount_paid = $this->whereNull('deleted_at');
        $not_paid_amount = $this->whereNull('deleted_at');

        if(isset($request['start_date']) && isset($request['end_date'])){
            // $results->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            // $results->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $results->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $total_amount->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $amount_paid->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $not_paid_amount->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
        }else if(isset($request['start_date'])){
            $results->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $total_amount->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $amount_paid->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $not_paid_amount->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
        }else if(isset($request['end_date'])){
            $results->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $total_amount->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $amount_paid->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $not_paid_amount->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
        }

        if(isset($request['search'])){

            $results->where(function ($query) use ($request){
                $query->where("desc","LIKE",'%'.$request['search'].'%')
                ->orWhere("title","LIKE",'%'.$request['search'].'%');
            });
           // $results->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("title","LIKE",'%'.$request['search'].'%');
           $total_amount->where(function ($query) use ($request){
            $query->where("desc","LIKE",'%'.$request['search'].'%')
            ->orWhere("title","LIKE",'%'.$request['search'].'%');
           });

           $amount_paid->where(function ($query) use ($request){
            $query->where("desc","LIKE",'%'.$request['search'].'%')
            ->orWhere("title","LIKE",'%'.$request['search'].'%');
           });

           $not_paid_amount->where(function ($query) use ($request){
            $query->where("desc","LIKE",'%'.$request['search'].'%')
            ->orWhere("title","LIKE",'%'.$request['search'].'%');
           });


            //$total_amount->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("title","LIKE",'%'.$request['search'].'%');
            //$amount_paid->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("title","LIKE",'%'.$request['search'].'%');
            //$not_paid_amount->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("title","LIKE",'%'.$request['search'].'%');
        }

        if(isset($request['recorded_by'])){
            $results->where("recorded_by",$request['recorded_by']);
            $total_amount->where("recorded_by",$request['recorded_by']);
            $amount_paid->where("recorded_by",$request['recorded_by']);
            $not_paid_amount->where("recorded_by",$request['recorded_by']);
        }

        if(isset($request['status_id'])){
            $results->where("status_id",$request['status_id']);
            $total_amount->where("status_id",$request['status_id']);
            $amount_paid->where("status_id",$request['status_id']);
            $not_paid_amount->where("status_id",$request['status_id']);
        }

        $result = $results->where('user_id', $user_id)->orderBy('id','desc')->paginate($limit)->toArray();
        $fulldata['data']  = $result['data'];

        $total = $total_amount->where('user_id',$user_id)->sum('amount');
        $paid = $amount_paid->where('user_id',$user_id)->sum('amount_paid');
        $not_paid= $not_paid_amount->where('user_id',$user_id)->where('is_settled','0')->sum('remaining_amount');

        //get total amount
        $widgets['total_amount'] =  $total;
        $widgets['amount_paid'] =  $paid;//$this->where('user_id',$user_id)->whereNull('deleted_at')->sum('amount_paid');
        $widgets['not_paid_amount'] = $not_paid;//$this->where('user_id',$user_id)->whereNull('deleted_at')->sum('remaining_amount');

        $fulldata['info'] = $widgets;
        $data['page'] =  $result;
        unset($data['page']['data']);
        unset($fulldata['data']['message']);
        $new_row = array_merge($fulldata,$data);
        return $new_row;
    }

    public function searchExpenses($request, $limit)
    {
        $sale = $this->with(['status', 'type', 'user'])->where('user_id', $request['user_id']);

        if (!empty($request['date'])) {
            $sale = $sale->whereRaw('DATE(created_at) ="' . $request['date'] . '"');
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
    public function Image(){
        return $this->hasMany(Upload::class, 'model_ref_id', 'id')->where('model_name','expenses');
    }

    public function getExpenseVendor($user_id){
        return \DB::select("SELECT distinct(`vendor_name`) FROM `expenses` WHERE
        vendor_name IS NOT NULL AND deleted_at IS NULL AND user_id='$user_id' GROUP BY vendor_name");

    }
    public function getExpenseAmount($vendor_name,$user_id){
        return \DB::select("SELECT SUM(CASE WHEN is_settled='0' THEN`remaining_amount` END) AS amount  FROM `expenses` WHERE vendor_name='$vendor_name' AND deleted_at IS NULL AND vendor_name IS NOT NULL AND user_id='$user_id'");
    }

    public function getExpenseAmountWriteOff($vendor_name,$user_id){
        return \DB::select("SELECT SUM(CASE WHEN is_settled='1' THEN`remaining_amount` END) AS amount  FROM `expenses` WHERE vendor_name='$vendor_name' AND deleted_at IS NULL AND vendor_name IS NOT NULL AND user_id='$user_id'");
    }

    public function getDataByCategories($year,$firstMonth,$lastMonth,$user_id,$type,$month_n=''){

       if(count($year) < 2){
            $prevMonth = $year[0];
            $nextMonth = (isset($year[1]))?$year[1]:$year[0];
            $lastDay = cal_days_in_month(CAL_GREGORIAN, 12, date('Y'));
            $EndDate = $nextMonth."-12"."-".$lastDay;
            $startDate = $prevMonth."-01"."-01";
       }else{
         if($month_n < date('m')){
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

       if(isset(request()->all()['start_date']) && isset(request()->all()['end_date'])){
            $startDate = request()->all()['start_date'];
            $EndDate = request()->all()['end_date'];
       }

        $data=[];
        $categories=[];
        $data = \DB::select("SELECT t.id,t.`title`,t.`type`,
        COALESCE(SUM(amount_paid)) amount
        FROM expenses e
        INNER JOIN `types` t
        ON e.user_id = t.user_id
        WHERE e.`date` BETWEEN '$startDate'
        AND '$EndDate'
        AND e.user_id='$user_id'
        AND e.sub_cat_id=t.id
        AND t.type_id='$type' AND e.deleted_at is NULL GROUP BY t.id");
        // die("SELECT t.id,t.`title`,t.`type`,
        // COALESCE(SUM(amount_paid)) amount
        // FROM expenses e
        // INNER JOIN `types` t
        // ON e.user_id = t.user_id
        // WHERE e.`date` BETWEEN '$startDate'
        // AND '$EndDate'
        // AND e.user_id='$user_id'
        // AND e.sub_cat_id=t.id
        // AND t.type_id='$type' AND e.deleted_at is NULL GROUP BY t.id");

        if(empty($data[0]->id)){
            return $data=[];
        }else{
           // return $data;
            foreach($data as $cat){
                $categories[]=  (object) ["id"=>$cat->id,"title"=>trans("categories.".str_replace(" ","_",strtolower($cat->title))),"type"=>$cat->type,"amount"=>$cat->amount];
            }
            return $categories;
        }


        // if(empty($data))

        //     return $data=[];
        // else
        //

    }

    public function getRemainingAmount($id){
        return $this->where('id',$id)->pluck('remaining_amount')->first();
    }

    public function report($request){
        $final =[];
        $amount = [];
        if(isset($request['aging']) && $request['aging']== 1){
            $first[]=[
                '0-30'=>payableAging($request['type'],$request['user_id'],0,30),
                'amount'=>totalAmountPayable($request['type'],$request['user_id'],0,30,false),
            ];
            $response = array_merge($final,$first);
            $amount = totalAmountPayable($request['type'],$request['user_id'],0,30,false);
        }else if(isset($request['aging']) && $request['aging']== 2){
            $second[]=[
                '30-60'=>payableAging($request['type'],$request['user_id'],30,60),
                'amount'=>totalAmountPayable($request['type'],$request['user_id'],30,60,false),
            ];
            $response =array_merge($final,$second);
            $amount = totalAmountPayable($request['type'],$request['user_id'],30,60,false);
        }else if(isset($request['aging']) && $request['aging']== 3){
            $third[]=[
                '60-180'=>payableAging($request['type'],$request['user_id'],60,180),
                'amount'=>totalAmountPayable($request['type'],$request['user_id'],60,180,false)
            ];
            $response =array_merge($final,$third);
            $amount = totalAmountPayable($request['type'],$request['user_id'],60,180,false);
        }else if(isset($request['aging']) && $request['aging']== 4){
            $fourth[]=[
                '180-360'=>payableAging($request['type'],$request['user_id'],180,360),
                'amount'=>totalAmountPayable($request['type'],$request['user_id'],180,360,false)
            ];
            $response =array_merge($final,$fourth);
            $amount = totalAmountPayable($request['type'],$request['user_id'],180,360,false);
        }else if(isset($request['aging']) && $request['aging']== 5){
            $fifth[]=[
                '360+' =>payableAging($request['type'],$request['user_id'],360,'',true),
                'amount'=>totalAmountPayable($request['type'],$request['user_id'],360,'',true)
            ];
            $response =array_merge($final,$fifth);
            $amount = totalAmountPayable($request['type'],$request['user_id'],360,'',true);
        }else{
            $first[] = [
                '0-30'=>payableAging($request['type'],$request['user_id'],0,30),
                'amount'=>totalAmountPayable($request['type'],$request['user_id'],0,30,false)
            ]; //Aging Starts from 0 to 30

            $second[]  = [
                '30-60'=>payableAging($request['type'],$request['user_id'],30,60),
                'amount'=>totalAmountPayable($request['type'],$request['user_id'],30,60,false)
            ]; //Aging Starts from 30 to 60
            $third[]  = [
                '60-180'=>payableAging($request['type'],$request['user_id'],60,180),
                'amount'=>totalAmountPayable($request['type'],$request['user_id'],60,180,false)
            ]; //Aging Starts from 60 to 180

            $fourth[]  = [
                '180-360'=> payableAging($request['type'],$request['user_id'],180,360),
                'amount'=>totalAmountPayable($request['type'],$request['user_id'],180,360,false)
            ]; //Aging Starts from 180 to 360
            $fifth[]  = [
                '360+'=> payableAging($request['type'],$request['user_id'],360,'',true),
                'amount'=>totalAmountPayable($request['type'],$request['user_id'],360,'',true)
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
                        }else{
                            $start = (isset($agingValue[0])) ? $agingValue[0] : '';
                        }

                        $end = (isset($agingValue[1])) ? $agingValue[1] : '';

                        $is_true = ($count == 4) ? true: false;
                        //     //$final[]['amount'] = totalAmount($request['user_id'],$start,$end,$is_true);
                        $amount[] = totalAmountPayable($request['type'],$request['user_id'],$start,$end,$is_true);

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
}
