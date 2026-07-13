<?php

namespace App\Http\Controllers\Api;

use App\Models\Expense;
use App\Models\Inventory;
use App\Models\OwnerAccount;
use App\Models\Pending;
use App\Models\AppUser;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Validation\RulesPending as Rules;
use Illuminate\Support\Facades\Input;
use App\Models\Upload;
class PendingController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Pending();
        $this->sale_model = new Sale();
        $this->purchase_model = new Purchase();
        $this->owner_account_model = new OwnerAccount();
        $this->expense_model = new Expense();
        $this->inventory_model = new Inventory();
        $this->uploadModel = new Upload();
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
            $response = $this->primary_model->apiListing($request->all(),$request->user_id,$this->data_limit);
            return PagintionResponse($response,trans('auth.success'));
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function store(Request $request)
    {
        $data = [];
        $row = [];
        app()->setLocale('ar');
        if($request->hasFile('file')){
            $files = $request->file('file');
            $total_file = count($files);
        }

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

        try {
             $validation = Validator::make($request->all(), Rules::Pending());
        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

         $id = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;

         if($request->hasFile('file') && $total_file > 0){
            for($i = 0; $i < $total_file;$i++){
                $data[$i]['file'] = $files[$i];
                $this->uploadModel->uploadSingleFile($files[$i], $id,$this->primary_model->getTable());
            }
         }
         $row = Pending::with(['Image'])->findOrFail($id);

         setActivityLogs([
            'log_name' => 'Pending has recorded',
            'subject_id'=>$row['id'],
            'subject_type' => 'Pending',
            'causer_id'=>$row['user_id'],
            'causer_type'=>'AppUser',
            'recorded_by'=>$row['user_id'],
            'description' =>$current_user->full_name .' has recorded pending, receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount) . ' received',
            'module' => 'pending',
            'description_ar' => trans('Logs.amount_to')." ".($request->amount).trans('Logs.received').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",". trans('Logs.has_recorded_pending'). $current_user->full_name
        ]);

         return authyResponse($row,trans('auth.success'));
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function update(Request $request)
    {
        $data = [];
        $row = [];
        app()->setLocale('ar');
        if($request->hasFile('file')){
            $files = $request->file('file');
            $total_file = count($files);
        }
           $current_user = AppUser::find($request->user_id);

           if(!isset($request->id)){
                $request->merge(['id' => $request->idd]);
            }
        try {
            $validation = Validator::make($request->all(), Rules::updatePending());
            if ($validation->fails()) {
                return sendErrorToClient(implode(",",$validation->messages()->all()));
            }

         $this->primary_model->where('id',$request->id)->update($request->only($this->primary_model->getFillable()));

         if($request->hasFile('file') && $total_file > 0){
            for($i = 0; $i < $total_file;$i++){
                $data[$i]['file'] = $files[$i];
                $this->uploadModel->uploadSingleFile($files[$i], $request->id,$this->primary_model->getTable());
            }
         }

         $row = Pending::with(['Image'])->findOrFail($request->id);

         setActivityLogs([
                'log_name' => 'Pending has updated',
                'subject_id'=>$row['id'],
                'subject_type' => 'Sale',
                'causer_id'=>$row['user_id'],
                'causer_type'=>'AppUser',
                'recorded_by'=>$row['user_id'],
                'description' =>$current_user->full_name .' has edited pending, ID ' . $row['sys_gen_id'] . ', Amounted to ' . ($row['amount']),
                'module' => 'pending',
                'description_ar' => trans('Logs.amount_to')." ".($row['amount']).",".$row['sys_gen_id']." " .trans('Logs.id').",". trans('Logs.has_edited'). $current_user->full_name
         ]);

         return authyResponse($row,trans('auth.success'));
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function getPending(Request $request){
        $id = $request->id;
        $validation = Validator::make($request->all(), Rules::getPending());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        $data = Pending::with(['Image'])->findOrFail($id);
       return authyResponse($data ,'success');
    }

    public function deletePending(Request $request){
        app()->setLocale('ar');
        $validation = Validator::make($request->all(), Rules::deletePending());
        $current_user = AppUser::find($request->user_id);
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        $row = Pending::with(['Image'])->findOrFail($request->id);
        $this->primary_model->where('id',$request->id)->delete();
        $this->uploadModel->where("model_name","pendings")->where('model_ref_id',$request->id)->delete(); //used soft deletes in model
        setActivityLogs([
            'log_name' => 'Pending has deleted',
            'subject_id'=>$row['id'],
            'subject_type' => 'Sale',
            'causer_id'=>$row['user_id'],
            'causer_type'=>'AppUser',
            'recorded_by'=>$row['user_id'],
            'description' =>$current_user->full_name .' has deleted pending, ID ' . $row['sys_gen_id'] . ', Amounted to ' . ($row['amount']),
            'module' => 'sales',
            'description_ar' => trans('Logs.amount_to')." ".($row['amount']).",".$row['sys_gen_id']." " .trans('Logs.id').",". trans('Logs.has_deleted'). $current_user->full_name
        ]);
        return sendMsgToClient(trans('auth.deleted_successfully'));
    }
}
