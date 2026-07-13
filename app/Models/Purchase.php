<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Expense;

class Purchase extends Model
{
    use SoftDeletes;
    protected $fillable = ['sys_gen_id','asset_name' ,'desc','is_settled', 'amount', 'status_id', 'user_id', 'quantity',
                           'vendor_name','vendor_id','date','status_id','depreciation','asset_life','depreciated_value',
                           'remaining_amount','amount_paid','depreciable_amount','depreciation_net_amount','recorded_by','last_trans_update'];
    // protected $hidden = ['depreciation'];

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function getPurchase($id)
    {
        return $this->with(['status','Image','getTransaction.Image'])->findOrFail($id);
    }

    public function getTransaction(){
        return $this->hasMany(Transaction::class, 'ref_id', 'id')->with('Image')->where('amount','!=',0)->where('type','purchase')->whereNull('deleted_at');
    }

    public function apiListing($request,$user_id, $limit)
    {
            $data=[];

            $results = $this->with(['Image','getTransaction.Image'])
                ->whereNull('deleted_at');

            $total=$this->whereNull('deleted_at');
            $paid=$this->whereNull('deleted_at');
            $un_paid=$this->whereNull('deleted_at');

            if(isset($request['start_date']) && isset($request['end_date'])){
                $results->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
                $total->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
                $paid->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
                $un_paid->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            }else if(isset($request['start_date'])){
                $results->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
                $total->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
                $paid->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
                $un_paid->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            }else if(isset($request['end_date'])){
                $results->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
                $total->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
                $paid->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
                $un_paid->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            }

            if(isset($request['search'])){
                $results->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("asset_name","LIKE",'%'.$request['search'].'%');
                $total->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("asset_name","LIKE",'%'.$request['search'].'%');
                $paid->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("asset_name","LIKE",'%'.$request['search'].'%');
                $un_paid->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("asset_name","LIKE",'%'.$request['search'].'%');
            }

            if(isset($request['recorded_by'])){
                $results->where("recorded_by",$request['recorded_by']);
                $total->where("recorded_by",$request['recorded_by']);
                $paid->where("recorded_by",$request['recorded_by']);
                $un_paid->where("recorded_by",$request['recorded_by']);
            }


            if(isset($request['status_id'])){
                $results->where("status_id",$request['status_id']);
                $total->where("status_id",$request['status_id']);
                $paid->where("status_id",$request['status_id']);
                $un_paid->where("status_id",$request['status_id']);
            }

            $result = $results->where('user_id', $user_id)->orderBy('id','desc')->paginate($limit)->toArray();
            $fulldata['data']  = $result['data'];


            //get total amount
            $widgets['total'] =  $total->where('user_id',$user_id)->sum('amount');
            $widgets['paid'] =  $paid->where('user_id',$user_id)->sum('amount_paid');
            $widgets['un_paid'] = $un_paid->where('user_id',$user_id)->where('is_settled','0')->sum('remaining_amount');

            $fulldata['info'] = $widgets;
            $data['page'] =  $result;
            unset($data['page']['data']);
            unset($fulldata['data']['message']);
            $new_row = array_merge($fulldata,$data);
            return $new_row;
    }

    public function searchPurchase($request, $limit)
    {
        $sale = $this->with(['status', 'user'])->where('user_id', $request['user_id']);

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
        return $this->hasMany(Upload::class, 'model_ref_id', 'id')->where('model_name','purchases');
    }

    public function getPurchaseVendor($user_id){
       return \DB::select("SELECT distinct(`vendor_name`) FROM `purchases` WHERE
       vendor_name IS NOT NULL AND deleted_at IS NULL AND user_id='$user_id'  GROUP BY vendor_name");
    }
    public function getPurchaseAmount($vendor_name,$user_id){
        return \DB::select("SELECT SUM(CASE WHEN is_settled='0' THEN`remaining_amount` END) AS amount FROM `purchases` WHERE vendor_name='$vendor_name' AND deleted_at IS NULL AND vendor_name IS NOT NULL AND user_id='$user_id'");
    }

    public function getPurchaseAmountWriteOff($vendor_name,$user_id){
        return \DB::select("SELECT SUM(CASE WHEN is_settled='1' THEN`remaining_amount` END) AS amount FROM `purchases` WHERE vendor_name='$vendor_name' AND deleted_at IS NULL AND vendor_name IS NOT NULL AND user_id='$user_id'");
    }

    public function getCashOut($request){
        $Purchases = $this->where('user_id', $request['user_id'])
        ->whereNull('deleted_at');

        $Expense = Expense::where('user_id', $request['user_id'])
        ->whereNull('deleted_at');

        if(isset($request['from']) && isset($request['to'])){
            $Purchases->whereBetween("date",[date('Y-m-d',strtotime($request['from'])),date('Y-m-d',strtotime($request['to']))]);
            $Expense->whereBetween("date",[date('Y-m-d',strtotime($request['from'])),date('Y-m-d',strtotime($request['to']))]);
        }

        // else if(isset($request['from'])){
        //     $Purchases->whereDate("date",">=",date('Y-m-d',strtotime($request['from'])));
        //     $Expense->whereDate("date",">=",date('Y-m-d',strtotime($request['from'])));
        // }else if(isset($request['to'])){
        //     $Purchases->whereDate("date","<=",date('Y-m-d',strtotime($request['to'])));
        //     $Expense->whereDate("date","<=",date('Y-m-d',strtotime($request['to'])));
        // }

        $purchase_response = $Purchases->sum('amount_paid');
        $expense_response = $Expense->sum('amount_paid');
        $response = $purchase_response + $expense_response;

        $startDate = $request['from'];
        $endDate = $request['to'];
        $user_id = $request['user_id'];
        $column = 'amount';

        $owner_account = \DB::select("SELECT
        COALESCE(SUM(CASE
        WHEN `date` BETWEEN '".$startDate."' AND '".$endDate."'
        THEN $column
        END), 0) AS cash_out
        FROM `owner_accounts`
        WHERE  user_id = '$user_id'
        AND `status_id` = '14'
            AND deleted_at IS NULL");
         return $response + $owner_account[0]->cash_out;
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
            $request['type'] = $request['type'] == 'all' ? 'purchases' : $request['type'];
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
