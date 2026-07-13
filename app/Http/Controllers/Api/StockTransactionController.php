<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StockTransaction;
use App\Models\Inventory;
use App\Models\AppUser;
use App\Models\StockLog;
use App\Models\InventoryImages;
use Illuminate\Support\Facades\Validator;
use App\Http\Validation\RulesInventory as Rules;
use App\Models\Status;

class StockTransactionController extends Controller
{
    function __construct()
    {
        $this->primary_model = new StockTransaction();
        $this->stock_model = new Inventory();
        $this->status_model = new Status();
        $this->inventoryModel = new InventoryImages();
        $this->stock_log = new StockLog();
        $this->module = $this->primary_model->getTable();
    }

    public function store(Request $request){
        $headers = apache_request_headers();

        $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
        app()->setLocale($local);

        $parent_id = getParentId('app_users','id',$request->user_id);
        $total_image=0;
        $images='';

        if($request->hasFile('file')){
            $files = $request->file('file');
            $total_file = count($files);
        }
 
        if($parent_id !=0){
            $recorded_by = $request->user_id;
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;
            $recorded_by = $request->user_id;
        }
        
        $request->merge(['user_id'=>$user_id]);
        $request->merge(['recorded_by'=>$recorded_by]);

        $current_user = AppUser::find($request->user_id);

        $validation = Validator::make($request->all(), Rules::storeUpdates());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

        try{
            $request->merge(['date_timestamp'=>date('Y-m-d h:i:s')]);
            $stock_trans_id = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;

            if($request->hasFile('file') && $total_file > 0){
                for($i = 0; $i < $total_file;$i++){
                    $data[$i]['file'] = $files[$i];
                    $this->inventoryModel->uploadSingleFile($files[$i], $stock_trans_id,$request->stock_id,$this->inventoryModel->getTable());
                }
            }

            $stock = $this->primary_model->getStockTransaction($stock_trans_id);
            $stockCount = $this->stock_model->getInventory($request->stock_id);

            $sys_gen_id = $stockCount->sys_gen_id;
            $full_name = $current_user['full_name'];

            if($stockCount->date < $request->date){
                $this->stock_model->where('id',$request->stock_id)->update(['status_id'=>$request->status_id,'date' => $request->date]);
            }
            $status_name = $this->status_model->getStatusName('inventories',$request->status_id);
            $log = $full_name. " has recorded new item for stock count is ".$status_name.", ID " .$sys_gen_id;
            app()->setLocale('ar');
            $logArabic =  $full_name. trans('labels.module_labels.stock_add_log') .$sys_gen_id;
            app()->setLocale($local);

            setActivityLogs([
             'log_name' => 'Stock has recorded',
             'subject_id'=>$stock_trans_id,
             'subject_type' => 'Inventory',
             'causer_id'=>$current_user->id,
             'causer_type'=>'Inventory',
             'recorded_by'=>$current_user->id,
             'description' =>$current_user->full_name .' has edited stock as '.$status_name .' , ID ' . $sys_gen_id,
             'module' => 'inventories',
             'description_ar' => $sys_gen_id." " .trans('Logs.receipt_id').",".trans('Logs.stock')." ". trans('Logs.has_edited'). $current_user->full_name
           ]);

            $request->merge(['log'=>$log]);
            $request->merge(['log_ar'=>$logArabic]);

           StockLog::create($request->only($this->stock_log->getFillable()));

            return makeClientHappy($stock,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function update(Request $request){
        $headers = apache_request_headers();
        $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
        app()->setLocale($local);

        $parent_id = getParentId('app_users','id',$request->user_id);
        $total_image=0;
        $images='';

        if($request->hasFile('file')){
            $files = $request->file('file');
            $total_file = count($files);
        }

        if($parent_id !=0){
            $recorded_by = $request->user_id;
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;
            $recorded_by = $request->user_id;
        }
        $request->merge(['user_id'=>$user_id]);
        $request->merge(['recorded_by'=>$recorded_by]);

        $current_user = AppUser::find($request->user_id);

        $validation = Validator::make($request->all(), Rules::Updates());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

        try{
            // $this->primary_model->where('user_id',$request->user_id)->where('stock_id',$request->stock_id)->update(['is_deletable'=>0]);

            // $request->merge(['is_deletable'=>1]);

            if(isset($request->delete_files) && !empty($request->delete_files)){
                $id = explode(",",$request->delete_files);
                $this->inventoryModel->whereIn('id',$id)->delete();
            }

           // $request->merge(['date'=>date('Y-m-d')]);
            $stock_trans_id = $request->id;

            $request->merge(['date_timestamp'=>date('Y-m-d h:i:s',strtotime($request->date))]);

            $this->primary_model->where('id',$stock_trans_id)->update($request->only($this->primary_model->getFillable()));

            // $this->stock_model->where('id',$request->stock_id)->update(['status_id'=>$request->status_id,'date' => date('Y-m-d')]);

            if($request->hasFile('file') && $total_file > 0){
                for($i = 0; $i < $total_file;$i++){
                    $data[$i]['file'] = $files[$i];
                    $this->inventoryModel->uploadSingleFile($files[$i], $stock_trans_id,$request->stock_id,$this->inventoryModel->getTable());
                }
            }

            $stock = $this->primary_model->getStockTransaction($request->id);
            // dd($stock->stock_id);
            $stockCount = $this->stock_model->getInventory($stock->stock_id);

            $sys_gen_id = $stockCount->sys_gen_id;
            $full_name = $current_user['full_name'];

            // $stock_tran = $this->primary_model->where('date_timestamp','>',$request->date_timestamp)->where('date_timestamp','>',$request->date_timestamp)->where('is_deletable','1')->get()->toArray();
            $lastStatusId = $this->primary_model->where('stock_id',$stock->stock_id)->where('deleted',0)->orderBy('date', 'desc')->first();
            if($lastStatusId){
                $this->stock_model->where('id',$stock->stock_id)->update(['status_id' => $lastStatusId->status_id]);
            }
            //  app()->setLocale('ar');
            // app()->setLocale('en');
            $log = $full_name. " has edited item for stock count, ID" .$sys_gen_id;
            app()->setLocale('ar');
            $logArabic =  $full_name. trans('labels.module_labels.stock_update_log') .$sys_gen_id;
            app()->setLocale($local);

            $request->merge([
                'stock_id' => $stock->stock_id,
                'log' => $log,
                'log_ar' => $logArabic
            ]);

           StockLog::create($request->only($this->stock_log->getFillable()));
           $status_name = $this->status_model->getStatusName('inventories', $request->status_id);
           setActivityLogs([
            'log_name' => 'Stock has updated',
            'subject_id'=>$stock->stock_id,
            'subject_type' => 'Inventory',
            'causer_id'=>$current_user->id,
            'causer_type'=>'Inventory',
            'recorded_by'=>$current_user->id,
            'description' =>$current_user->full_name .' has edited stock as '.$status_name .' , ID ' . $sys_gen_id,
            'module' => 'inventories',
            'description_ar' => $sys_gen_id." " .trans('Logs.receipt_id').",".trans('Logs.stock')." ". trans('Logs.has_edited'). $current_user->full_name
          ]);

            return makeClientHappy($stock,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function list(Request $request){
        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){
            $recorded_by = $request->user_id;
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;
            $recorded_by = $request->user_id;
        }
        $request->merge(['user_id'=>$user_id]);
       $stock_transaction =  $this->primary_model->listing($request->all());
       return makeClientHappy($stock_transaction,trans('auth.success'));
    }

    public function delete(Request $request){
        $headers = apache_request_headers();

        $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
        app()->setLocale($local);

        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){

            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);


        $validation = Validator::make($request->all(), Rules::StockTransDelete());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

        $current_user = AppUser::find($request->user_id);

        $stock = $this->primary_model->where('id',$request->id)->first();

        $stock->update(['deleted'=>1]);

        $this->inventoryModel->where('tran_id',$request->id)->delete();
        $stockCount = $this->stock_model->getInventory($stock->stock_id);
        $this->stock_model->where('id',$stock->stock_id)->update(['status_id' => $this->primary_model->where('stock_id',$stock->stock_id)->where('deleted',0)->orderBy('date', 'desc')->first()->status_id]);

        $sys_gen_id = $stockCount->sys_gen_id;
        // app()->setLocale('en');
        $log = $current_user['full_name']."has deleted item for stock count, ID".$sys_gen_id;
        app()->setLocale('ar');
        $logArabic =  trans('labels.module_labels.stock_delete_log') .$current_user['full_name'];
        app()->setLocale($local);

        $request->merge(['log'=>$log]);
        $request->merge(['log_ar'=>$logArabic]);
        $request->merge(['stock_id'=>$stock->stock_id]);

        setActivityLogs([
            'log_name' => 'Stock has deleted',
            'subject_id'=>$stockCount->id,
            'subject_type' => 'Inventory',
            'causer_id'=>$current_user->id,
            'causer_type'=>'Inventory',
            'recorded_by'=>$current_user->id,
            'description' =>$current_user->full_name .' has delete stock, receipt ID ' . $sys_gen_id,
            'module' => 'inventories',
            'description_ar' => $sys_gen_id." " .trans('Logs.receipt_id').",". trans('Logs.has_recorded_stock_count'). $current_user->full_name
        ]);

        StockLog::create($request->only($this->stock_log->getFillable()));

        return sendMsgToClient(trans('auth.deleted_successfully'));
    }
}
