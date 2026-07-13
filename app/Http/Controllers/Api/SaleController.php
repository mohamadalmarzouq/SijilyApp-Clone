<?php

namespace App\Http\Controllers\Api;

use App\Http\Validation\RulesSales as Rules;
use App\Imports\Importer;
use App\Models\Sale;
use App\Models\SubCategory;
use App\Models\Status;
use App\Models\Pending;
use App\Models\AppUser;
use App\Models\Upload;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class SaleController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Sale();
        $this->sub_cat_model = new SubCategory();
        $this->status_model = new Status();
        $this->module = $this->primary_model->getTable();
        $this->uploadModel = new Upload();
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

        app()->setLocale('ar');
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
            //check status
            $status = $this->status_model->getStatusSlug($this->module, $request->status_id);

            if ($status == 'not_received') {

                $validation = Validator::make($request->all(), [
                    'received_amount' => 'required|numeric',
                    'customer_name' => 'required'
                ]);

                if ($validation->fails()) {
                    return sendErrorToClient($validation->errors()->first());
                }

                $request->merge(['remaining_amount' => $request->amount - $request->received_amount]);

            } else {
                $request->merge(['received_amount' => $request->amount,'remaining_amount' => 0]);
            }

            if(isset($request->sub_category_id)){
                app()->setLocale('ar');
                $cate_type= $this->sub_cat_model->where("title",trim($request->sub_category_name))->where("user_id",$request->user_id)->first();
                $request->merge(['sub_category_name_ar' => trans('categories.'.$cate_type['slug'])]);
            }

            $id = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;

            $prefix_id = ($request->received_amount == 0) ? '-0' : '-1';
            $subTransactionId = $this->transaction_model->create([
                'ref_id'=> $id,
                'user_id'=> $request->user_id,
                'type_id'=>1,
                'type'=>'sale',
                'customer_id'=> $request->customer_id,
                'customer_name'=> $request->customer_name,
                'amount'=> $request->received_amount,
                'date'=> $request->date,
                'recorded_by'=> $request->user_id,
                'child_sys_gen_id' => $request->sys_gen_id . $prefix_id,
            ])->id;


            if(isset($request->pending_delete_ids)){
                $ids = explode(",",$request->pending_delete_ids);
                foreach($ids as $d)
                    $this->uploadModel->where('id',$d)->where("model_name","pendings")->delete();
            }

            if(isset($request->pending_id)){
                Pending::where("id",$request->pending_id)->update(["status"=>0]);
                $this->uploadModel->where("model_ref_id",$request->pending_id)->where("model_name","pendings")->update(["model_ref_id"=>$id,"model_name" => $this->primary_model->getTable()]);
            }

            if($request->hasFile('file') && $total_file > 0){
                for($i = 0; $i < $total_file;$i++){
                    $data[$i]['file'] = $files[$i];
                    $file_details = $this->uploadModel->uploadSingleFile($files[$i], $id,$this->primary_model->getTable());
                    $this->uploadModel->transactionUpload($subTransactionId,$file_details['source']);
                }
            }

            $row = $this->primary_model->with(['Image'])->findOrFail($id);

            $headers = apache_request_headers();

            if($status =="not_received"){
                setActivityLogs([
                    'log_name' => 'Sale has recorded',
                    'subject_id'=>$row['id'],
                    'subject_type' => 'Sale',
                    'causer_id'=>$user_loggedin_id,
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$user_loggedin_id,
                    'description' =>$current_user->full_name .' has recorded revenue, customer '.$request->customer_name .', receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount - $request->received_amount) . ' not received',
                    'module' => 'sales',
                    'description_ar' => trans('Logs.amount_to')." ".($request->amount - $request->received_amount).trans('Logs.not_received').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",".$request->customer_name.trans('Logs.has_recorded_revenue') .",".trans('Logs.customer'). $current_user->full_name
                ]);
                // app()->setLocale('ar');
                // session()->put('activity_log_data', [
                //     'identifier' => 'add_receivable',
                //     'subject_type' => $row,
                //     'name' => 'customer_name',
                //     'data' =>'customer ' . $request->customer_name .' receipt ID ' . $request->sys_gen_id . ' Amounted to ' . ($request->amount - $request->received_amount) . ' not received',
                //     'module' => $this->module,
                //      'data_ar' => trans('Logs.amount_to').($request->amount).trans('Logs.received').$request->sys_gen_id." ".trans('Logs.customer').$request->customer_name.", ".trans('Logs.has_recorded_revenue').",".trans('Logs.receipt_id')." ".$current_user->full_name
                // ]);
            }else{
                setActivityLogs([
                    'log_name' => 'Sale has recorded',
                    'subject_id'=>$row['id'],
                    'subject_type' => 'Sale',
                    'causer_id'=>$user_loggedin_id,
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$user_loggedin_id,
                    'description' =>$current_user->full_name .' has recorded revenue, receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount) . ' received',
                    'module' => 'sales',
                    'description_ar' => trans('Logs.amount_to')." ".($request->amount).trans('Logs.received').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",". trans('Logs.has_recorded_revenue'). $current_user->full_name
                ]);

                // app()->setLocale('ar');
                // session()->put('activity_log_data', [
                //     'identifier' => 'add_received',
                //     'subject_type' => $row,
                //     'name' => 'customer_name',
                //     'data' => 'receipt ID ' . $request->sys_gen_id .' receipt ID ' . $request->sys_gen_id . ' Amounted to ' . $request->amount . ' received',
                //     'module' => $this->module,
                //     'data_ar' => trans('Logs.amount_to').($request->amount).trans('Logs.received').$request->sys_gen_id." ".$request->customer_name.", ".trans('Logs.customer').trans('Logs.has_recorded_revenue').",".trans('Logs.receipt_id')." ".$current_user->full_name
                // ]);
            }
            $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
            app()->setLocale($local);


            return makeClientHappy($row,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function update(Request $request)
    {
        $user_loggedin_id = $request->user_id;
        $current_user = AppUser::find($request->user_id);
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

            if ($status == 'not_received') {

                $validation = Validator::make($request->all(), [
                    'received_amount' => 'required|numeric',
                    'customer_name' => 'required'
                ]);

                if ($validation->fails()) {
                    return sendErrorToClient($validation->errors()->first());
                }

                $request->merge(['remaining_amount' => $request->amount - $request->received_amount]);

            } else {
                $request->merge(['received_amount' => $request->amount,'remaining_amount' => 0]);
            }

            $this->primary_model->where('id',$request->id)->update($request->only($this->primary_model->getFillable()));

            if($request->hasFile('file') && count($request->file('file')) > 0){
                for($i = 0; $i < $total_file;$i++){
                    $data[$i]['file'] = $files[$i];
                    $this->uploadModel->uploadSingleFile($files[$i], $request->id,$this->primary_model->getTable());
                }
            }

            // if(isset($request->transaction)){
            //     $transaction = json_decode($request->transaction,true);

            //     foreach($transaction as $trans){
            //         $validation = Validator::make($trans, [
            //             'customer_id' => 'required',
            //             'customer_name' => 'required',
            //             'amount' => 'required',
            //             'note' => 'required',
            //             'date' => 'required'
            //         ]);

            //         if ($validation->fails()) {
            //             return sendErrorToClient($validation->errors()->first());
            //         }

            //         $this->transaction_model->where('id',$trans['id'])->where('type','sale')->update([
            //             'customer_id'=>$trans['customer_id'],
            //             'customer_name'=>$trans['customer_name'],
            //             'amount'=>$trans['amount'],
            //             'note'=>$trans['note'],
            //             'date'=>$trans['date'],
            //         ]);
            //     }
            // }

            // if(isset($request->date)){
            //     $this->transaction_model->where('ref_id',$request->id)->where('type','sale')->update([
            //         'date'=>$request->date,
            //     ]);
            // }

            // if(isset($request->amount)){
            //     $this->transaction_model->where('ref_id',$request->id)->where('type','sale')->update([
            //         'amount'=>$request->amount,
            //     ]);
            // }

            if(isset($request->is_settled) && $request->is_settled==1){
                $this->primary_model->where('id',$request->id)->update(['is_settled'=>1]);//,'status_id'=>6
            }


            $row = $this->primary_model->with(['Image'])->findOrFail($request->id);

            $this->transaction_model->updateOrCreate(
                [
                    'ref_id' => $request->id,
                    'user_id' => $user_id,
                ],
                [
                    'ref_id' => $request->id,
                    'user_id' => $request->user_id,
                    'type_id' => 1,
                    'type' => 'sale',
                    'customer_id' => $request->customer_id,
                    'customer_name' => $request->customer_name,
                    'amount' => $request->received_amount,
                    'date' => $request->date,
                    'recorded_by' => $recorded_by,
                ]
            );

            $headers = apache_request_headers();
            app()->setLocale('ar');

            setActivityLogs([
                'log_name' => 'Sale has updated',
                'subject_id'=>$row['id'],
                'subject_type' => 'Sale',
                'causer_id'=> $current_user->id,
                'causer_type'=>'AppUser',
                'recorded_by'=> $current_user->id,
                'description' =>$current_user->full_name .' has edited revenue, ID ' . $row['sys_gen_id'] . ', Amounted to ' . ($row['amount']),
                'module' => 'sales',
                'description_ar' => trans('Logs.amount_to')." ".($row['amount']).",".$row['sys_gen_id']." " .trans('Logs.id').",". trans('Logs.has_edited'). $current_user->full_name
            ]);
            // session()->put('activity_log_data', [
            //     'identifier' => 'updated',
            //     'subject_type' => $row,
            //     'name' => 'customer_name',
            //     'module' => $this->module,
            //     'data' => 'ID ' . $request->id . ' Amounted to ' . ($request->amount),
            //     'data_ar' => trans('Logs.has_edited'). 'ID ' . $request->id . ' Amounted to ' . ($request->amount)
            // ]);

            $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
            app()->setLocale($local);
            return makeClientHappy($row,trans('auth.success'));

        } catch (\Exception $e) {
            throw $e;
            return sendExpToClient($e);
        }
    }

    public function delete(Request $request)
    {
        $current_user = AppUser::find($request->user_id);
        $validation = Validator::make($request->all(), Rules::deleteSale());
        app()->setLocale('ar');
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        $row = $this->primary_model->findOrFail($request->id);
        $row->delete();

        $this->uploadModel->where("model_name","sales")->where('model_ref_id',$request->id)->delete();
        $this->transaction_model->where("type","sale")->where('ref_id',$request->id)->delete();

        setActivityLogs([
            'log_name' => 'Sale has deleted',
            'subject_id'=>$row['id'],
            'subject_type' => 'Sale',
            'causer_id'=>$current_user->id,
            'causer_type'=>'AppUser',
            'recorded_by'=>$current_user->id,
            'description' =>$current_user->full_name .' has deleted revenue, ID ' . $row['sys_gen_id'] . ', Amounted to ' . ($row['amount']),
            'module' => 'sales',
            'description_ar' => trans('Logs.amount_to')." ".($row['amount']).",".$row['sys_gen_id']." " .trans('Logs.id').",". trans('Logs.has_deleted'). $current_user->full_name
        ]);

        return sendMsgToClient(trans("auth.deleted"));
    }

    public function get(Request $request)
    {
        try {

            $sale = $this->primary_model->getSales($request->id);
            return makeClientHappy($sale,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function search(Request $request)
    {
        try {

            $sale = $this->primary_model->searchSales($request->all(), $this->data_limit);

            return makeClientHappy($sale,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
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


        try {

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

            $status_id = $this->status_model->getStatusID($this->module, 'not_received');

            $rules = Rules::store();

            Excel::import(new Importer($request->user_id, $status_id, $rules), request()->file('file'));

            $this->primary_model->insert($data_to_insert);

            $response = $this->primary_model->apiListing($request->user_id, $this->data_limit);

            return PagintionResponse($response,trans('auth.success'));

        } catch (ValidationException $e) {
            return sendErrorToClient(reset($e->errors()[0]));
        }
    }

    public function accountReceivable(Request $request){

        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){

            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);

        // $request->merge(['recorded_by'=>$recorded_by]);
        try {
            $response= $this->primary_model->receivables($request->all(),$this->data_limit);

            return PagintionResponse($response,trans('auth.success'));

        } catch (ValidationException $e) {
            return sendErrorToClient(reset($e->errors()[0]));
        }
    }

    public function saleCustomers(Request $request){

        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){

            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);


        $sale = $this->primary_model->where("user_id",$request->user_id)->whereNotNull("customer_name")->whereNull("deleted_at")->distinct()->get(['customer_name'])->toArray();
        $data=[];
        foreach($sale as $sa){
            $data[] = $sa['customer_name'];
        }
        return makeClientHappy($data,trans('auth.success'));
    }

    public function getSchedule(Request $request){

        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){

            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);


        $sale = $this->primary_model->getScheduleCustomers($request->user_id);
        return makeClientHappy($sale,trans('auth.success'));
    }
}

