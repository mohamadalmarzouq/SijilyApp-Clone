<?php

namespace App\Http\Controllers\Api;

use App\Imports\InventoryImport;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Validation\RulesInventory as Rules;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\AppUser;
use App\Models\Upload;
use App\Models\Pending;
use App\Models\StockTransaction;
use App\Models\StockLog;
use App\Models\InventoryImages;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Importer;
use Maatwebsite\Excel\Validators\ValidationException;

class InventoryController extends Controller
{

    public function __construct()
    {
        $this->primary_model = new Inventory();
        $this->status_model = new Status();
        $this->uploadModel = new Upload();
        $this->stock_transaction = new StockTransaction();
        $this->stock_log = new StockLog();
        $this->inventoryModel = new InventoryImages();
        $this->module = $this->primary_model->getTable();
        $this->stock_transaction_module = $this->stock_transaction->getTable();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
        // $current_user = AppUser::find($request->user_id);

        $validation = Validator::make($request->all(), Rules::store());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

        try {
            $total_image=0;
            $images='';

            if($request->hasFile('file')){
                $files = $request->file('file');
                $total_file = count($files);
            }

            $status = $this->status_model->getStatusSlug($this->module, $request->status_id);
            $status_name = $this->status_model->getStatusName($this->module, $request->status_id);
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

                $request->merge(['asset_life' => $request->asset_life,'depreciated_value'=> 15]);

            } else {
                $request->merge(['asset_life' => 0,'depreciated_value'=> 0]);
            }

        //        $request->merge(['date' => date('Y-m-d')]);

            $inventory = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;


            //Stock Transaction
            $request->merge(['description'=>$request->desc]);
            $request->merge(['stock_id'=>$inventory]);
            $request->merge(['child_sys_gen_id'=> $request->sys_gen_id."-1"]);

            $stock_transaction = $this->stock_transaction->create($request->only($this->stock_transaction->getFillable()));
            $log = $current_user['full_name']." have updated for Stock Count to ".$status_name.", receipt ID ".$request->sys_gen_id;
            $logArabic =  $current_user['full_name']."رقم السجل".", حالة تحديث عدد المخزون ".$request->sys_gen_id;
            $stock_trans_id = $stock_transaction->id;
            $stock_id = $stock_transaction->stock_id;
            $request->merge(['log'=>$log]);
            $request->merge(['log_ar'=>$logArabic]);

            StockLog::create($request->only($this->stock_log->getFillable()));

            if(isset($request->pending_delete_ids)){
                $ids = explode(",",$request->pending_delete_ids);
                foreach($ids as $d)
                    $this->uploadModel->where('id',$d)->where("model_name","pendings")->delete();
            }

            if(isset($request->pending_id)){
                Pending::where("id",$request->pending_id)->update(["status"=>0]);
                $this->uploadModel->where("model_ref_id",$request->pending_id)->where("model_name","pendings")->update(["model_ref_id"=>$inventory,"model_name" => $this->primary_model->getTable()]);
                //Upload::where("model_ref_id",$request->pending_id)->where("model_name","pendings")->update(["model_ref_id"=>$inventory,"model_name" => $this->primary_model->getTable()]);
            }

            if($request->hasFile('file') && $total_file > 0){
                for($i = 0; $i < $total_file;$i++){
                    $data[$i]['file'] = $files[$i];
                    $imageFileUpload = $this->uploadModel->uploadSingleFile($files[$i], $inventory,$this->primary_model->getTable());
                    $this->inventoryModel->insert([
                        'tran_id'=> $stock_trans_id,
                        'source' => $imageFileUpload->source,
                        'image_ref_id'=> $imageFileUpload->id,
                        'stock_id' => $stock_id
                    ]);
                }
            }

            if($request->hasFile('image')){
                $images = $request->file('image');
                $total_image = count($images);
            }

            if($request->hasFile('image') && $total_image > 0){
                for($i = 0; $i < $total_image;$i++){
                    $data[$i]['file'] = $images[$i];
                    $this->inventoryModel->uploadSingleFile($images[$i], $inventory,$this->primary_model->getTable());
                }
            }
            $stockCount = $this->primary_model->getInventory($inventory);
          
            $headers = apache_request_headers();
            app()->setLocale('ar');
            setActivityLogs([
                'log_name' => 'Stock has recorded',
                'subject_id'=>$stockCount['id'],
                'subject_type' => 'Inventory',
                'causer_id'=>$current_user->id,
                'causer_type'=>'AppUser',
                'recorded_by'=>$current_user->id,
                'description' =>$current_user->full_name .' has recorded new item for stock count is '.$status_name.', receipt ID ' . $request->sys_gen_id,
                'module' => 'inventories',
                'description_ar' => $request->sys_gen_id." " .trans('Logs.receipt_id').",". trans('Logs.has_recorded_stock_count'). $current_user->full_name
            ]);

            // stockActivityLogs([
            //     'log_name' => 'Sale has recorded',
            //     'subject_id'=>$stockCount['id'],
            //     'subject_type' => 'Sale',
            //     'causer_id'=>$stockCount['user_id'],
            //     'causer_type'=>'AppUser',
            //     'recorded_by'=>$stockCount['user_id'],
            //     'description' =>$current_user->full_name .' has deleted stock, ID ' . $stockCount['sys_gen_id'],
            //     'module' => 'sales',
            //     'description_ar' => $stockCount['sys_gen_id']." " .trans('Logs.receipt_id').",".trans('Logs.stock'). trans('Logs.has_deleted'). $current_user->full_name
            // ]);

            app()->setLocale($headers['Local']);
            return makeClientHappy($stockCount,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
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

        app()->setLocale('ar');
        $current_user = AppUser::find($request->user_id);

        if(!isset($request->id)){
            $request->merge(['id' => $request->idd]);
        }

        $validation = Validator::make($request->all(), Rules::update());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

        try {

            $files=[];
            $total_file=0;

            if(isset($request->delete_files) && !empty($request->delete_files)){
                $id = explode(",",$request->delete_files);
                $this->uploadModel->whereIn('id',$id)->delete();
                $this->inventoryModel->whereIn("image_ref_id",$id)->delete();
            }

            // if(isset($request->delete_file) && !empty($request->delete_file)){
            //     $id = explode(",",$request->delete_file);
            //     $this->uploadModel->whereIn('id',$id)->delete();
            // }

            $inventory = $this->primary_model->findOrFail($request->id);

            $inventory->update($request->only($this->primary_model->getFillable()));

            if($request->hasFile('file')){
                $files = $request->file('file');
                $total_file = count($files);
            }

            $stock_id = $this->stock_transaction->where('stock_id',$request->id)->first();

            if($request->hasFile('file') && $total_file > 0){
                for($i = 0; $i < $total_file;$i++){
                    $data[$i]['file'] = $files[$i];
                    $imageFileUpload = $this->uploadModel->uploadSingleFile($files[$i], $request->id,$this->primary_model->getTable());
                    $this->inventoryModel->create([
                        'tran_id'=> $request->id,
                        'source' => $imageFileUpload->source,
                        'image_ref_id'=> $imageFileUpload->id,
                        'stock_id'=> $stock_id->id,
                    ]);
                }
            }

            $total_image=0;
            $image=[];

            if($request->hasFile('image')){
                $image = $request->file('image');
                $total_image = count($image);
            }

            if($request->hasFile('image') && $total_image > 0){
                for($i = 0; $i < $total_image;$i++){
                    $data[$i]['file'] = $image[$i];
                    $this->inventoryModel->uploadSingleFile($image[$i], $request->id,$this->primary_model->getTable());
                }
            }


            $inven = $this->primary_model->getInventory($request->id);
            $headers = apache_request_headers();
            app()->setLocale('ar');

            setActivityLogs([
                'log_name' => 'Stock has updated',
                'subject_id'=>$inven['id'],
                'subject_type' => 'Sale',
                'causer_id'=>$current_user->id,
                'causer_type'=>'AppUser',
                'recorded_by'=>$current_user->id,
                'description' =>$current_user->full_name .' has edited stock, ID ' . $inven['sys_gen_id'],
                'module' => 'sales',
                'description_ar' => $request->sys_gen_id." " .trans('Logs.receipt_id').",".trans('Logs.stock')." ". trans('Logs.has_edited'). $current_user->full_name
            ]);

            app()->setLocale($headers['Local']);
            
            return makeClientHappy($inven,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function delete(Request $request)
    {
         $current_user = AppUser::find($request->user_id);
        $validation = Validator::make($request->all(), Rules::delete());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

        try {
            $row = $this->primary_model->findOrFail($request->id);
            $row->delete();
            $this->uploadModel->where('model_name','inventories')->where('model_ref_id',$request->id)->delete();
            $this->inventoryModel->where('tran_id',$request->id)->delete();
            //$purchase = $this->primary_model->getPurchase($request->id);
            app()->setLocale('ar');
            setActivityLogs([
                'log_name' => 'Sale has deleted',
                'subject_id'=>$row['id'],
                'subject_type' => 'Sale',
                'causer_id'=>$current_user->id,
                'causer_type'=>'AppUser',
                'recorded_by'=>$current_user->id,
                'description' =>$current_user->full_name .' has deleted stock, ID ' . $row['sys_gen_id'],
                'module' => 'sales',
                'description_ar' => $row['sys_gen_id']." " .trans('Logs.receipt_id').",".trans('Logs.stock'). trans('Logs.has_deleted'). $current_user->full_name
            ]);

            return sendMsgToClient(trans('auth.deleted_successfully'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function inventory(Request $request)
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

    public function getInventory(Request $request)
    {

        $id = $request->id;
        $validation = Validator::make($request->all(), Rules::get());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        $data =  $this->primary_model->getInventory($id);
        return authyResponse($data ,trans('auth.success'));
    }

    public function search(Request $request)
    {
        try {

            $expense = $this->primary_model->searchInventories($request->all(), $this->data_limit);

            return makeClientHappy($expense,trans('auth.success'));

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

            $status_id = $this->status_model->getStatusID($this->module, 'in_stock');

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
