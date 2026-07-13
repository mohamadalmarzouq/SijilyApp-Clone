<?php

namespace App\Http\Controllers\Api;

use App\Imports\Importer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Validation\RulesBank as Rules;
use App\Http\Controllers\Controller;
use App\Models\BankReconcile;
use App\Models\Purchase;
use App\Models\Pending;
use App\Models\Sale;
use App\Models\Upload;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;


class BankReconciliation extends Controller
{
    private $saleModel;
    private $purchaseModel;
    public function __construct()
    {
        $this->uploadModel = new Upload();
        $this->saleModel = new Sale();
        $this->purchaseModel = new Purchase();
        $this->primary_model = new BankReconcile();
        $this->module = $this->primary_model->getTable();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

        $validator = \Validator::make($request->all(), Rules::store());
        if ($validator->fails()) {
            // return \Response::json(implode(",",$validation->messages()->all()), 400);
            return sendErrorToClient(implode(",",$validator->messages()->all()));
        }
        try {
            if($request->hasFile('file')){
                $files = $request->file('file');
                $total_file = count($files);
            }

            $requestArray = $request->all();

            $BankReconcile_id = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;

            if(isset($request->pending_delete_ids)){
                $ids = explode(",",$request->pending_delete_ids);
                foreach($ids as $d)
                    $this->uploadModel->where('id',$d)->where("model_name","pendings")->delete();
            }

            if(isset($request->pending_id)){
                Pending::where("id",$request->pending_id)->update(["status"=>0]);
                $this->uploadModel->where("model_ref_id",$request->pending_id)->where("model_name","pendings")->update(["model_ref_id"=>$BankReconcile_id,"model_name" => $this->primary_model->getTable()]);

              //  Upload::where("model_ref_id",$request->pending_id)->where("model_name","pendings")->update(["model_ref_id"=>$BankReconcile_id,"model_name" => $this->primary_model->getTable()]);
            }

            if($request->hasFile('file') && $total_file > 0){
                for($i = 0; $i < $total_file;$i++){
                    unset($requestArray['file']);
                    $data[$i]['file'] = $files[$i];
                    $this->uploadModel->uploadSingleFile($files[$i], $BankReconcile_id,$this->primary_model->getTable());
                }
            } else if($request->hasFile('file') && $total_file > 1){
                for($i = 0; $i < $total_file;$i++){
                    unset($requestArray['file']);
                    $data[$i]['file'] = $files[$i];
                    $this->uploadModel->uploadFiles($files[$i], $BankReconcile_id,$this->primary_model->getTable());
                }
            }

            $row = $this->primary_model->with(['Image'])->findOrFail($BankReconcile_id);

            return makeClientHappy($row,trans('auth.success'));
          //  return Response::json(['success' => 'Bank Reconciliation added'], 200);
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

        if(!isset($request->id)){
            $request->merge(['id' => $request->idd]);
        }

        $validation = Validator::make($request->all(), Rules::update());
        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
            //$validation->errors()->first()
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

            $BankReconcile = $this->primary_model->findOrFail($request->id);
            $BankReconcile->update($request->only($this->primary_model->getFillable()));

            if($request->hasFile('file') && $total_file > 0){
                for($i = 0; $i < $total_file;$i++){
                    unset($requestArray['file']);
                    $data[$i]['file'] = $files[$i];
                    $this->uploadModel->uploadSingleFile($files[$i], $request->id,$this->primary_model->getTable());
                }
            } else if($request->hasFile('file') && $total_file > 1){
                for($i = 0; $i < $total_file;$i++){
                    unset($requestArray['file']);
                    $data[$i]['file'] = $files[$i];
                    $this->uploadModel->uploadFiles($files[$i], $request->id,$this->primary_model->getTable());
                }
            }


            $row = $this->primary_model->with(['Image'])->findOrFail($request->id);
            // if ($request->is_pending) {

            //     $BankReconcile->pending()->delete();

            //     $BankReconcile->pending()->create(['user_id' => $request->user_id]);
            // } else {
            //     $BankReconcile->pending()->delete();
            // }

            return makeClientHappy($row, 'success');
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function delete(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::delete());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        try {
            $BankReconcile = $this->primary_model->findOrFail($request->id);
            $BankReconcile->delete();

            $upload = $this->uploadModel->where('model_name','bank_reconciles')->where('model_ref_id',$request->id);
            $upload->delete();
            return sendMsgToClient('Deleted Successfully');
        } catch (\Exception $e) {
            return sendErrorToClient("The selected id is invalid.");
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

            $response = $this->primary_model->apiListing($request->all(),$request->user_id, $this->data_limit);

            return PagintionResponse($response);

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function ReconciliationData(Request $request)
    {
        $validation = Validator::make($request->all(), ['id' => 'required']);
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        $result = $this->primary_model->SelectBankData($request['id']);
        if ($result) {
            return makeClientHappy($result);
        } else {
            return sendErrorToClient('No record found');
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

    public function import(Request $request)
    {
        $validation = Validator::make($request->all(), ['file' => 'required|file']);

        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        try {
            global $data_to_insert;

            $rules = Rules::store();

            Excel::import(new Importer($request->user_id, '', $rules), request()->file('file'));

            $this->primary_model->insert($data_to_insert);

            $response = $this->primary_model->apiListing($request->user_id, $this->data_limit);

            return PagintionResponse($response);

        } catch (ValidationException $e) {
            return sendErrorToClient(reset($e->errors()[0]));
        }
    }

    public function GetCashInOut(Request $request){
       $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){
            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;
        }
        $request->merge(['user_id'=>$user_id]);


        $data['cash_in'] = $this->saleModel->getCashIn($request->all());
        $data['cash_out'] = $this->purchaseModel->getCashOut($request->all());


        return makeClientHappy($data);
        // $data['cash_in'] = $this->purchaseModel->getCashIn($request->all());
    }

}
