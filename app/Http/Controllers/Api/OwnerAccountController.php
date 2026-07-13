<?php

namespace App\Http\Controllers\Api;

use App\Http\Validation\RulesOwnerAccount as Rules;
use App\Imports\Importer;
use App\Models\OwnerAccount;
use App\Models\Type;
use Illuminate\Http\Request;
use App\Models\Upload;
use App\Models\Status;
use App\Models\Pending;
use App\Models\AppUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class OwnerAccountController extends Controller
{
    function __construct()
    {
        $this->primary_model = new OwnerAccount();
        $this->status_model = new Status();
        $this->type_model = new Type();
        $this->uploadModel = new Upload();
        $this->module = $this->primary_model->getTable();
    }

    public function store(Request $request)
    {
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

        app()->setLocale('ar');
        $current_user = AppUser::find($request->user_id);

        $validation = Validator::make($request->all(), Rules::store());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

        try {
            if($request->hasFile('file')){
                $files = $request->file('file');
                $total_file = count($files);
            }
            $requestArray = $request->all();
            $id = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;

            if(isset($request->pending_delete_ids)){
                $ids = explode(",",$request->pending_delete_ids);
                foreach($ids as $d)
                    $this->uploadModel->where('id',$d)->where("model_name","pendings")->delete();
            }

            if(isset($request->pending_id)){
                Pending::where("id",$request->pending_id)->update(["status"=>0]);
                $this->uploadModel->where("model_ref_id",$request->pending_id)->whereNotNull('deleted_at')->where("model_name","pendings")->update(["model_ref_id"=>$id,"model_name" => $this->primary_model->getTable()]);

                //Upload::where("model_ref_id",$request->pending_id)->where("model_name","pendings")->update(["model_ref_id"=>$id,"model_name" => $this->primary_model->getTable()]);
            }

            if($request->hasFile('file') && $total_file > 0){
                for($i = 0; $i < $total_file;$i++){
                    unset($requestArray['file']);
                    $data[$i]['file'] = $files[$i];
                    $this->uploadModel->uploadSingleFile($files[$i], $id,$this->primary_model->getTable());
                }
            }else if($request->hasFile('file') && $total_file > 1){
                for($i = 0; $i < $total_file;$i++){
                    unset($requestArray['file']);
                    $data[$i]['file'] = $files[$i];
                    $this->uploadModel->uploadFiles($files[$i], $id,$this->primary_model->getTable());
                }
            }

            $account = $this->primary_model->with(['Image'])->findOrFail($id);

            $status = $this->status_model->getStatusSlug($this->module, $request->status_id);
            $headers = apache_request_headers();

            if($status =="out_flow"){
                app()->setLocale('ar');
                setActivityLogs([
                    'log_name' => 'Owner Account has recorded',
                    'subject_id'=>$account['id'],
                    'subject_type' => 'OwnerAccount',
                    'causer_id'=>$account['user_id'],
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$account['user_id'],
                    'description' =>$current_user->full_name .' has recorded revenue, owner '.$request->owner_name .', receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount),
                    'module' => 'owner_accounts',
                    'description_ar' => trans('Logs.amount_to')." ".($request->amount).",".$request->sys_gen_id." " .trans('Logs.receipt_id').",".$request->owner_name.trans('Logs.has_recorded_owners_withdrawal') .",".trans('Logs.owners'). $current_user->full_name
                ]);
            }else{
                app()->setLocale('ar');
                setActivityLogs([
                    'log_name' => 'Owner Account has recorded',
                    'subject_id'=>$account['id'],
                    'subject_type' => 'OwnerAccount',
                    'causer_id'=>$account['user_id'],
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$account['user_id'],
                    'description' =>$current_user->full_name .' has recorded revenue, owner '.$request->owner_name .', receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount),
                    'module' => 'owner_accounts',
                    'description_ar' => trans('Logs.amount_to')." ".($request->amount).",".$request->sys_gen_id." " .trans('Logs.receipt_id').",".$request->owner_name.trans('Logs.has_recorded_owners_injection') .",".trans('Logs.owners'). $current_user->full_name
                ]);
            }
            $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
            app()->setLocale($local);
            return makeClientHappy($account,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function update(Request $request)
    {
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

        if(!isset($request->id)){
            $request->merge(['id' => $request->idd]);
        }

        $validation = Validator::make($request->all(), Rules::update());

        if ($validation->fails()) {
            return sendErrorToClient(['error' => $validation->messages()]);
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

            $requestArray = $request->all();

            $account = $this->primary_model->findOrFail($request->id);

            $account->update($request->only($this->primary_model->getFillable()));

            // if ($request->is_pending) {
            //     $account->pending()->delete();

            //     $account->pending()->create(['user_id' => $request->user_id]);
            // } else {
            //     $account->pending()->delete();
            // }

            $row = $this->primary_model->findOrFail($account->id);

            if($request->hasFile('file') && $total_file > 0){
                for($i = 0; $i < $total_file;$i++){
                    unset($requestArray['file']);
                    $data[$i]['file'] = $files[$i];
                    $this->uploadModel->uploadSingleFile($files[$i], $request->id,$this->primary_model->getTable());
                }
            }else if($request->hasFile('file') && $total_file > 1){
                for($i = 0; $i < $total_file;$i++){
                    unset($requestArray['file']);
                    $data[$i]['file'] = $files[$i];
                    $this->uploadModel->uploadFiles($files[$i], $request->id,$this->primary_model->getTable());
                }
            }
            $headers = apache_request_headers();
            app()->setLocale('ar');
            $account = $this->primary_model->getOwnerAccount($account->id);
             $current_user = AppUser::find($request->user_id);
            setActivityLogs([
                'log_name' => 'Owner Account has updated',
                'subject_id'=>$row['id'],
                'subject_type' => 'OwnerAccount',
                'causer_id'=>$row['user_id'],
                'causer_type'=>'AppUser',
                'recorded_by'=>$row['user_id'],
               'description' =>$current_user->full_name .' has recorded revenue, owner '.$request->owner_name .', receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount),
                'module' => 'owner_accounts',
                'description_ar' => trans('Logs.amount_to')." ".($request->amount).",".$request->sys_gen_id." " .trans('Logs.receipt_id').",".$request->owner_name.trans('Logs.has_edited') .",".trans('Logs.owners'). $current_user->full_name
            ]);

            $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
            app()->setLocale($local);

            return makeClientHappy($account,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function delete(Request $request)
    {
          $current_user = AppUser::find($request->user_id);
        $validation = Validator::make($request->all(), Rules::delete());

        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        try {

            $account = $this->primary_model->findOrFail($request->id);

            $account->delete();

            $this->uploadModel->where('model_name','owner_accounts')->where('model_ref_id',$request->id)->delete();

            setActivityLogs([
                'log_name' => 'Owner Account has deleted',
                'subject_id'=>$account['id'],
                'subject_type' => 'OwnerAccount',
                'causer_id'=>$account['user_id'],
                'causer_type'=>'AppUser',
                'recorded_by'=>$account['user_id'],
                'description' =>$current_user->full_name .' has deleted owner account, ID ' . $account['sys_gen_id'] . ', Amounted to ' . ($account['amount']),
                'module' => 'owner_accounts',
                'description_ar' => trans('Logs.amount_to')." ".($account['amount']).",".$request->sys_gen_id." " .trans('Logs.receipt_id').",". trans('Logs.has_deleted'). $current_user->full_name
            ]);


            // session()->put('activity_log_data', [
            //                'identifier' => 'deleted',
            //                'subject_type' => $account,
            //                'name' => 'customer_name',
            //                'module' => $this->module,
            //                'data' => 'ID ' . $request->id . ' Amounted to ' . ($account->amount),
            //                'data_ar' => trans('Logs.has_edited'). 'ID ' . $request->id . ' Amounted to ' . ($account->amount)
            //            ]);
            return sendMsgToClient(trans('auth.deleted_successfully'));

        } catch (\Exception $e) {
            // return sendExpToClient($e);
            return sendErrorToClient("The selected id is invalid.");
        }
    }

    public function get(Request $request)
    {
        try {
            $account = $this->primary_model->getOwnerAccount($request->id);
            return makeClientHappy($account,trans('auth.success'));
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

        // $request->merge(['recorded_by'=>$recorded_by]);

        try {

            $response = $this->primary_model->apiListing($request->all(),$this->data_limit, $request->user_id);

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

            $rules = Rules::import();

            Excel::import(new Importer($request->user_id, '', $rules), request()->file('file'));

            $this->primary_model->insert($data_to_insert);

            $response = $this->primary_model->apiListing($request->user_id, $this->data_limit);

            return PagintionResponse($response,trans('auth.success'));

        } catch (ValidationException $e) {
            return sendErrorToClient(reset($e->errors()[0]));
        }
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

    public function OwnerName(Request $request){

        $app_user = AppUser::where('id',$request->user_id)->exists();
        if(!$app_user){
        return makeClientHappy([],trans('auth.success'));
        }

        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){

            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);


        $sale = $this->primary_model->where("user_id",$request->user_id)->whereNotNull("owner_name")->whereNull("deleted_at")->distinct()->get(['owner_name'])->toArray();
        $data=[];
        foreach($sale as $sa){
            $data[] = $sa['owner_name'];
        }
        return makeClientHappy($data,trans('auth.success'));
    }

}
