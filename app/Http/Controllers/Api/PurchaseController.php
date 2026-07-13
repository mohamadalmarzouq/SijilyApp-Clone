<?php

namespace App\Http\Controllers\Api;

use App\Http\Validation\RulesPurchase as Rules;
use App\Imports\Importer;
use App\Models\Purchase;
use App\Models\Upload;
use App\Models\Pending;
use App\Models\AppUser;
use App\Models\Transaction;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class PurchaseController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Purchase();
        $this->status_model = new Status();
        $this->uploadModel = new Upload();
        $this->module = $this->primary_model->getTable();
        $this->transaction_model = new Transaction();
    }

    public function store(Request $request)
    {
        $user_loggedin_id = $request->user_id;
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

        $request->merge(['last_trans_update'=>date('Y-m-d h:i:s')]);

        // app()->setLocale('ar');
        $current_user = AppUser::find($user_loggedin_id);

        $validation = Validator::make($request->all(), Rules::store());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

        try {

            if($request->hasFile('file')){
                $files = $request->file('file');
                $total_file = count($files);
            }

            $status = $this->status_model->getStatusSlug($this->module, $request->status_id);

            if ($status == 'un_paid') {

                $validation = Validator::make($request->all(), [
                    'amount_paid' => 'required',
                    'vendor_name' => 'required',
                ]);

                if ($validation->fails()) {
                    return sendErrorToClient($validation->errors()->first());
                }

                $request->merge(['remaining_amount' => $request->amount - $request->amount_paid]);

            } else {
                $request->merge(['amount_paid' => $request->amount,'remaining_amount' => 0]);
            }

            if (isset($request->depreciation) && $request->depreciation == 1) {
                $validation = Validator::make($request->all(), [
                    'asset_life' => 'required',
                ]);

                if ($validation->fails()) {
                    return sendErrorToClient($validation->errors()->first());
                }
                $start_date = strtotime($request->date);
                $end_date = strtotime(date('Y-m-d'));
                $difference = ($end_date - $start_date)/60/60/24;
                $depreciable_amount = round((($request->amount / $request->asset_life) / 365.25) * ($difference));
                $depreciable_amount = $depreciable_amount > $request->amount ? $request->amount : $depreciable_amount;
                $depreciation_net_amount = $request->amount - number_format($depreciable_amount,2, '.', '');

                $depreciated_value = number_format(($depreciable_amount / $request->amount),2, '.', '') * 100;
                $request->merge(['asset_life' => $request->asset_life,'depreciated_value'=> number_format($depreciated_value,2, '.', ''),'depreciable_amount'=>number_format($depreciable_amount,2, '.', ''),'depreciation_net_amount'=>number_format($depreciation_net_amount,2, '.', '')]);
            } else {
                   $request->merge(['asset_life' => 0,'depreciated_value'=> 0,'depreciable_amount'=>0,'depreciation_net_amount'=>0]);
            }
            // $request->merge(['status_id' => $status_id]);

            $purchase = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;

            $prefix_id = ($request->amount_paid == 0) ? '-0' : '-1';
            $subTransactionId = $this->transaction_model->create([
                'ref_id'=> $purchase,
                'user_id'=> $request->user_id,
                'type_id'=>3,
                'type'=>'purchase',
                'customer_id'=> $request->vendor_id,
                'customer_name'=> $request->vendor_name,
                'amount'=> $request->amount_paid,
                'date'=> $request->date,
                'recorded_by'=> $request->user_id,
                'child_sys_gen_id' => $request->sys_gen_id . $prefix_id
            ])->id;

            if(isset($request->pending_delete_ids)){
                $ids = explode(",",$request->pending_delete_ids);
                foreach($ids as $d)
                    $this->uploadModel->where('id',$d)->where("model_name","pendings")->delete();
            }

            if(isset($request->pending_id)){
                Pending::where("id",$request->pending_id)->update(["status"=>0]);
                $this->uploadModel->where("model_ref_id",$request->pending_id)->where("model_name","pendings")->update(["model_ref_id"=>$purchase,"model_name" => $this->primary_model->getTable()]);
                //Upload::where("model_ref_id",$request->pending_id)->where("model_name","pendings")->update(["model_ref_id"=>$purchase,"model_name" => $this->primary_model->getTable()]);
            }


            if($request->hasFile('file') && $total_file > 0){
                for($i = 0; $i < $total_file;$i++){
                    $data[$i]['file'] = $files[$i];
                    $file_details = $this->uploadModel->uploadSingleFile($files[$i], $purchase,$this->primary_model->getTable());
                    $this->uploadModel->transactionUpload($subTransactionId,$file_details['source']);
                }
            }

            $purchase = $this->primary_model->getPurchase($purchase);
            $headers = apache_request_headers();
            if ($status == 'un_paid') {
                app()->setLocale('ar');

                setActivityLogs([
                    'log_name' => 'capital expenditures has recorded',
                    'subject_id'=>$purchase['id'],
                    'subject_type' => 'Purchase',
                    'causer_id'=>$current_user->id,
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$current_user->id,
                    'description' =>$current_user->full_name .' has recorded capital expenditures, Supplier '.$request->vendor_name .', receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount - $request->amount_paid) . ' not paid',
                    'module' => 'purchases',
                    'description_ar' => trans('Logs.amount_to')." ".($request->amount - $request->amount_paid).trans('Logs.not_paid').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",".$request->vendor_name.trans('Logs.has_recorded_capital_expenditures') .",".trans('Logs.supplier'). $current_user->full_name
                ]);
                // session()->put('activity_log_data', [
                //     'identifier' => 'add_payable',
                //     'subject_type' => $purchase,
                //     'name' => 'asset_name',
                //     'data' => 'supplier ' . $request->vendor_name . ' receipt ID ' . $request->sys_gen_id . ' Amounted to ' . ($request->amount - $request->amount_paid). ' unpaid',
                //     'module' => 'capital_expenditure',
                //     'data_ar' =>trans('Logs.amount_to').($request->amount - $request->amount_paid).trans('Logs.supplier').$request->vendor_name.trans('Logs.not_paid').$request->sys_gen_id.trans('Logs.has_recorded_revenue').",".trans('Logs.receipt_id')." ".$current_user->full_name

                // ]);
            }else{
                app()->setLocale('ar');
                setActivityLogs([
                    'log_name' => 'Capital Expenditure has recorded',
                    'subject_id'=>$purchase['id'],
                    'subject_type' => 'Purchase',
                    'causer_id'=>$current_user->id,
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$current_user->id,
                    'description' =>$current_user->full_name .' has recorded capital expenditures, receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount) . ' paid',
                    'module' => 'purchases',
                    'description_ar' => trans('Logs.amount_to')." ".($request->amount).trans('Logs.paid').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",". trans('Logs.has_recorded_capital_expenditures'). $current_user->full_name
                ]);
                // session()->put('activity_log_data', [
                //     'identifier' => 'added',
                //     'subject_type' => $purchase,
                //     'name' => 'asset_name',
                //     'data' => 'receipt ID ' . $request->sys_gen_id . ' Amounted to ' . $request->amount . ' paid',
                //     'module' => 'capital_expenditure',
                //     'data_ar' =>trans('Logs.amount_to').($request->amount).trans('Logs.paid').$request->sys_gen_id.trans('Logs.has_recorded_revenue').",".trans('Logs.receipt_id')." ".$current_user->full_name
                // ]);
            }
            $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
            app()->setLocale($local);

            return makeClientHappy($purchase,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function update(Request $request)
    {
        $user_loggedin_id = $request->user_id;
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

        $request->merge(['last_trans_update'=>date('Y-m-d h:i:s')]);

        app()->setLocale('ar');

        if(!isset($request->id)){
            $request->merge(['id' => $request->idd]);
        }

        $validation = Validator::make($request->all(), Rules::update());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

        try {

            if(isset($request->delete_files) && !empty($request->delete_files)){
                $id = explode(",",$request->delete_files);
                $this->uploadModel->whereIn('id',$id)->delete();
            }

            if($request->hasFile('file')){
                $files = $request->file('file');
                $total_file = count($files);
            }

            $status = $this->status_model->getStatusSlug($this->module, $request->status_id);

            if ($status == 'un_paid') {
                $validation = Validator::make($request->all(), [
                    'amount_paid' => 'required',
                    'vendor_name' => 'required',
                ]);

                if ($validation->fails()) {
                    return sendErrorToClient($validation->errors()->first());
                }

                $request->merge(['remaining_amount' => $request->amount - $request->amount_paid]);

            } else {
                $request->merge(['amount_paid' => 0,'remaining_amount' => 0,'vendor_name'=>NULL]);
            }

            if (isset($request->depreciation) && $request->depreciation == 1) {
                $validation = Validator::make($request->all(), [
                    'asset_life' => 'required',
                ]);

                if ($validation->fails()) {
                    return sendErrorToClient($validation->errors()->first());
                }

                $start_date = strtotime($request->date);
                $end_date = strtotime(date('Y-m-d'));
                $difference = ($end_date - $start_date)/60/60/24;
                $depreciable_amount = round((($request->amount / $request->asset_life) / 365.25) * ($difference));
                $depreciable_amount = $depreciable_amount > $request->amount ? $request->amount : $depreciable_amount;
                $depreciation_net_amount = $request->amount - number_format($depreciable_amount,2, '.', '');
                $depreciated_value = number_format(($depreciable_amount / $request->amount),2, '.', '') * 100;
                $request->merge(['asset_life' => $request->asset_life,'depreciated_value'=> number_format($depreciated_value,2, '.', ''),'depreciable_amount'=>number_format($depreciable_amount,2, '.', ''),'depreciation_net_amount'=>number_format($depreciation_net_amount,2, '.', '')]);
                // $request->merge(['asset_life' => $request->asset_life,'depreciated_value'=> number_format($depreciated_value,2, '.', '')]);
            } else {
                $request->merge(['asset_life' => 0,'depreciated_value'=> 0]);
            }

            // $request->merge(['status_id' => $status_id]);

            $this->primary_model->where('id',$request->id)->update($request->only($this->primary_model->getFillable()));

            if($request->hasFile('file') && $total_file > 0){
                for($i = 0; $i < $total_file;$i++){
                    $data[$i]['file'] = $files[$i];
                    $this->uploadModel->uploadSingleFile($files[$i], $request->id,$this->primary_model->getTable());
                    // $this->uploadModel->transactionUpload($subTransactionId,$file_details['source']);
                }
            }

            if(isset($request->transaction)){
                $transaction = json_decode($request->transaction,true);

                foreach($transaction as $trans){
                    $validation = Validator::make($trans, [
                        'customer_id' => 'required',
                        'customer_name' => 'required',
                        'amount' => 'required',
                        'note' => 'required',
                        'date' => 'required'
                    ]);

                    if ($validation->fails()) {
                        return sendErrorToClient($validation->errors()->first());
                    }
                    $this->transaction_model->where('id',$trans['id'])->where('type','purchase')->update([
                        'customer_id'=>$trans['customer_id'],
                        'customer_name'=>$trans['customer_name'],
                        'amount'=>$trans['amount'],
                        'note'=>$trans['note'],
                        'date'=>$trans['date'],
                    ]);
                }
            }
            if(isset($request->is_settled) && $request->is_settled==1){
                $this->primary_model->where('id',$request->id)->update(['is_settled'=>1]);//,'status_id'=>9
            }
            $current_user = AppUser::find($user_loggedin_id);
            $purchase = $this->primary_model->getPurchase($request->id);
            $headers = apache_request_headers();
            app()->setLocale('ar');

            setActivityLogs([
                'log_name' => 'Capital Expenditure has updated',
                'subject_id'=>$purchase['id'],
                'subject_type' => 'Purchase',
                'causer_id'=>$current_user->id,
                'causer_type'=>'AppUser',
                'recorded_by'=>$current_user->id,
                'description' =>$current_user->full_name .' has edited capital expenditure, ID ' . $purchase['sys_gen_id'] . ', Amounted to ' . ($purchase['amount']),
                'module' => 'purchases',
                'description_ar' => trans('Logs.amount_to')." ".($purchase['amount']).",".$purchase['sys_gen_id']." " .trans('Logs.id').",". trans('Logs.has_edited'). $current_user->full_name
            ]);
            // session()->put('activity_log_data', [
            //     'identifier' => 'updated',
            //     'subject_type' => $purchase,
            //     'name' => 'asset_name',
            //     'module' => $this->module,
            //     'data' => 'ID ' . $request->id . ' Amounted to ' . ($request->amount),
            //     'data_ar' => trans('Logs.has_edited'). 'ID ' . $request->id . ' Amounted to ' . ($request->amount)
            // ]);
            $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
            app()->setLocale($local);
            return makeClientHappy($purchase,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }


    public function delete(Request $request)
    {
        $current_user = AppUser::find($request->user_id);
        $validation = Validator::make($request->all(), Rules::delete());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }
        try {

            $purchase = $this->primary_model->findOrFail($request->id);

            $purchase->delete();

            $upload = $this->uploadModel->where('model_name','purchases')->where('model_ref_id',$request->id);
            $upload->delete();
            $this->transaction_model->where("type","purchase")->where('ref_id',$request->id)->delete();
            app()->setLocale('ar');

            setActivityLogs([
                'log_name' => 'Capital Expenditure has deleted',
                'subject_id'=>$purchase['id'],
                'subject_type' => 'Purchase',
                'causer_id'=>$current_user->id,
                'causer_type'=>'AppUser',
                'recorded_by'=>$current_user->id,
                'description' =>$current_user->full_name .' has deleted capital expenditure, ID ' . $purchase['sys_gen_id'] . ', Amounted to ' . ($purchase['amount']),
                'module' => 'purchases',
                'description_ar' => trans('Logs.amount_to')." ".($purchase['amount']).",".$purchase['sys_gen_id']." " .trans('Logs.id').",". trans('Logs.has_deleted'). $current_user->full_name
            ]);

            // session()->put('activity_log_data', [
            //     'identifier' => 'deleted',
            //     'subject_type' => $purchase,
            //     'name' => 'item_name',
            //     'module' => $this->module,
            //     'data' => 'ID ' . $request->id . ' Amounted to ' . ($purchase->amount),
            //     'data_ar' => trans('Logs.has_edited'). 'ID ' . $request->id . ' Amounted to ' . ($purchase->amount)
            // ]);

            return sendMsgToClient(trans('auth.deleted_successfully'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function get(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::get());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

        try {

            $purchase = $this->primary_model->getPurchase($request->id);

            return makeClientHappy($purchase,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function search(Request $request)
    {
        try {

            $expense = $this->primary_model->searchPurchase($request->all(), $this->data_limit);

            return makeClientHappy($expense,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function listing(Request $request)
    {
        try {
            $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){

            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);

        // $request->merge(['recorded_by'=>$recorded_by]);

            $response = $this->primary_model->apiListing($request->all(),$request->user_id, $this->data_limit);

            return PagintionResponse($response,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function import(Request $request)
    {
        $validation = Validator::make($request->all(), ['file' => 'required|file']);

        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        try {
            global $data_to_insert;

            $status_id = $this->status_model->getStatusID($this->module, 'un_paid');

            $rules = Rules::store();

            Excel::import(new Importer($request->user_id, $status_id, $rules), request()->file('file'));

            $this->primary_model->insert($data_to_insert);

            $response = $this->primary_model->apiListing($request->user_id, $this->data_limit);

            return PagintionResponse($response,trans('auth.success'));

        } catch (ValidationException $e) {
            return sendErrorToClient(reset($e->errors()[0]));
        }
    }
}
