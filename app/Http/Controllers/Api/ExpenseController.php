<?php

namespace App\Http\Controllers\Api;

use App\Http\Validation\RulesExpense as Rules;
use App\Http\Validation\RulesType as TypeRule;

use App\Imports\Importer;
use App\Models\Expense;
use App\Models\Status;
use App\Models\SubCategory;
use App\Models\Type;
use App\Models\AppUser;
use App\Models\Transaction;
use App\Models\Upload;
use App\Models\ExpenseCategory;
use App\Models\Pending;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ExpenseController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Expense();
        $this->status_model = new Status();
        $this->exp_cat_model = new ExpenseCategory();
        $this->type_model = new Type();
        $this->uploadModel = new Upload();
        $this->module = $this->primary_model->getTable();
        $this->sub_cat = new SubCategory();
        $this->transaction_model = new Transaction();
    }

    public function store(Request $request)
    {
        $headers = apache_request_headers();
        $user_loggedin_id = $request->user_id;
        $parent_id = getParentId('app_users','id',$request->user_id);
         $current_user = AppUser::find($request->user_id);
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

            $requestArray = $request->all();
            //check status
            $status = $this->status_model->getStatusSlug($this->module, $request->status_id);
            if ($status == 'un_paid') {
                $validation = Validator::make($request->all(), [
                    'amount_paid' => 'required',
                    // 'customer_name' => 'required'
                ]);

                if ($validation->fails()) {
                    return sendErrorToClient($validation->errors()->first());
                }
                $request->merge(['remaining_amount' => $request->amount - $request->amount_paid]);
            } else {
                $request->merge(['amount_paid' => $request->amount,"remaining_amount"=> 0 ]);
            }

            if(isset($request->sub_cat_type)){
                app()->setLocale('ar');
                $cate_type= $this->exp_cat_model->where("id",$request->sub_cat_type)->first();
                $request->merge(['sub_cat_name_ar' =>  trans('categories.'.$cate_type['slug'])]);
            }

            if(isset($request->sub_cat_id)){
                app()->setLocale('ar');
                $cate_type= $this->type_model->where("type_id",$request->sub_cat_id)->where("user_id",$request->user_id)->first();
                $request->merge(['sub_cat_fixed_name_ar' => trans('categories.'.$cate_type['slug'])]);
            }


            $id = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;

            $prefix_id = ($request->amount_paid == 0 || $request->amount_paid == '0') ? '-0' : '-1';
            $subTransactionId = $this->transaction_model->create([
                'ref_id'=> $id,
                'user_id'=> $request->user_id,
                'type_id'=> 2,
                'type'=>'expense',
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
                //Upload::where("model_ref_id",$request->pending_id)->where("model_name","pendings")->update(["model_ref_id"=>$id,"model_name" => $this->primary_model->getTable()]);
                $this->uploadModel->where("model_ref_id",$request->pending_id)->where("model_name","pendings")->update(["model_ref_id"=>$id,"model_name" => $this->primary_model->getTable()]);
            }

            if($request->hasFile('file') && $total_file > 0){
                for($i = 0; $i < $total_file;$i++){
                    unset($requestArray['file']);
                    $data[$i]['file'] = $files[$i];
                    $file_details =  $this->uploadModel->uploadSingleFile($files[$i], $id,$this->primary_model->getTable());
                    $this->uploadModel->transactionUpload($subTransactionId,$file_details['source']);
                }
            }else if($request->hasFile('file') && $total_file > 1){
                for($i = 0; $i < $total_file;$i++){
                    unset($requestArray['file']);
                    $data[$i]['file'] = $files[$i];
                    $file_details = $this->uploadModel->uploadFiles($files[$i], $id,$this->primary_model->getTable());
                    $this->uploadModel->transactionUpload($subTransactionId,$file_details['source']);
                }
            }

            $row = $this->primary_model->with(['Image'])->findOrFail($id);
            $headers = apache_request_headers();
            if ($status == 'un_paid') {
                app()->setLocale('ar');
                setActivityLogs([
                    'log_name' => 'Expense has recorded',
                    'subject_id'=>$row['id'],
                    'subject_type' => 'Expense',
                    'causer_id'=>$current_user->id,
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$current_user->id,
                    'description' =>$current_user->full_name .' has recorded expense, supplier '.$request->vendor_name .', receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount - $request->amount_paid) . ' not paid',
                    'module' => 'expenses',
                    'description_ar' => trans('Logs.amount_to')." ".($request->amount - $request->amount_paid).trans('Logs.not_paid').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",".$request->vendor_name.trans('Logs.has_recorded_expense') .",".trans('Logs.suppliers'). $current_user->full_name
                ]);
                // session()->put('activity_log_data', [
                //     'identifier' => 'add_payable',
                //     'subject_type' => $row,
                //     'name' => 'title',
                //     'data' => 'supplier ' . $request->vendor_name . ' receipt ID ' . $request->sys_gen_id . ' Amounted to ' . ($request->amount - $request->amount_paid). ' unpaid',
                //     'module' => $this->module,
                //     'data_ar'=> trans('Logs.amount_to').($request->amount - $request->amount_paid).trans('Logs.not_paid'). $request->vendor_name.trans('Logs.supplier').$request->sys_gen_id.trans('Logs.has_recorded_expense').",".trans('Logs.receipt_id')." ".$current_user->full_name
                // ]);
            }else{
                app()->setLocale('ar');

                setActivityLogs([
                    'log_name' => 'Expense has recorded',
                    'subject_id'=>$row['id'],
                    'subject_type' => 'Expense',
                    'causer_id'=>$current_user->id,
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$current_user->id,
                    'description' =>$current_user->full_name .' has recorded expense, receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount) . ' paid',
                    'module' => 'expenses',
                    'description_ar' => trans('Logs.amount_to')." ".($request->amount).trans('Logs.paid').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",". trans('Logs.has_recorded_revenue'). $current_user->full_name
                ]);

                // session()->put('activity_log_data', [
                //     'identifier' => 'added',
                //     'subject_type' => $row,
                //     'name' => 'title',
                //     'data' => 'receipt ID ' . $request->sys_gen_id . ' Amounted to ' . $request->amount . ' paid',
                //     'module' => $this->module,
                //     'data_ar'=>trans('Logs.amount_to').($request->amount).trans('Logs.paid').$request->sys_gen_id.trans('Logs.has_recorded_expense').",".trans('Logs.receipt_id')." ".$current_user->full_name
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
        $parent_id = getParentId('app_users','id',$request->user_id);
         $current_user = AppUser::find($user_loggedin_id);
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

            $requestArray = $request->all();

            $status = $this->status_model->getStatusSlug($this->module, $request->status_id);

            if ($status == 'un_paid') {
                $validation = Validator::make($request->all(), [
                    'amount_paid' => 'required',
                    // 'customer_name' => 'required'
                ]);

                if ($validation->fails()) {
                    return sendErrorToClient($validation->errors()->first());
                }

                $request->merge(['remaining_amount' => $request->amount - $request->amount_paid]);

            } else {
                $request->merge(['amount_paid' => $request->amount,"remaining_amount"=> 0 ]);
            }

            $expense = $this->primary_model->findOrFail($request->id);

            $expense->update($request->only($this->primary_model->getFillable()));

            // $expense = $this->primary_model->getExpenses($expense->id);


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

            // if(isset($request->transaction)){
            //     $transaction = json_decode($request->transaction,true);

            //     foreach($transaction as $trans){
            //         $validation = Validator::make($trans, [
            //             'vendor_id' => 'required',
            //             'vendor_name' => 'required',
            //             'amount' => 'required',
            //             'note' => 'required',
            //             'date' => 'required'
            //         ]);

            //         if ($validation->fails()) {
            //             return sendErrorToClient($validation->errors()->first());
            //         }
            //         $this->transaction_model->where('id',$trans['id'])->where('type','expense')->update([
            //             'customer_id'=>$trans['vendor_id'],
            //             'customer_name'=>$trans['vendor_name'],
            //             'amount'=>$trans['amount'],
            //             'note'=>$trans['note'],
            //             'date'=>$trans['date'],
            //         ]);
            //     }
            // }

            // if(isset($request->date)){
            //     $this->transaction_model->where('ref_id',$request->id)->where('type','expense')->update([
            //         'date'=>$request->date,
            //     ]);
            // }

            // if(isset($request->amount)){
            //     $this->transaction_model->where('ref_id',$request->id)->where('type','expense')->update([
            //         'amount'=>$request->amount,
            //     ]);
            // }

            if(isset($request->is_settled) && $request->is_settled==1){
                $this->primary_model->where('id',$request->id)->update(['is_settled'=>1]);//,'status_id'=>7
            }

            $row = $this->primary_model->with(['Image'])->findOrFail($request->id);

            $this->transaction_model->updateOrCreate(
                [
                    'ref_id' => $request->id,
                    'user_id' => $user_id,
                ],
                [
                    'ref_id'=> $request->id,
                    'user_id'=> $user_id,
                    'type_id'=> 2,
                    'type'=>'expense',
                    'customer_id'=> $request->vendor_id,
                    'customer_name'=> $request->vendor_name,
                    'amount'=> $request->amount_paid,
                    'date'=> $request->date,
                    'recorded_by'=> $recorded_by,
                    'child_sys_gen_id' => $request->sys_gen_id
                ]
            );


            $headers = apache_request_headers();
            app()->setLocale('ar');
            setActivityLogs([
                'log_name' => 'Expense has updated',
                'subject_id'=>$row['id'],
                'subject_type' => 'Expense',
                'causer_id'=>$user_loggedin_id,
                'causer_type'=>'AppUser',
                'recorded_by'=>$user_loggedin_id,
                'description' =>$current_user->full_name .' has edited expense, ID ' . $row['sys_gen_id'] . ', Amounted to ' . ($row['amount']),
                'module' => 'expenses',
                'description_ar' => trans('Logs.amount_to')." ".($row['amount']).",".$row['sys_gen_id']." " .trans('Logs.id').",". trans('Logs.has_edited'). $current_user->full_name
            ]);
            // session()->put('activity_log_data', [
            //     'identifier' => 'updated',
            //     'subject_type' => $row,
            //     'name' => 'title',
            //     'module' => $this->module,
            //     'data' => 'ID ' . $request->id . ' Amounted to ' . ($row->amount),
            //     'data_ar' => trans('Logs.has_edited'). 'ID ' . $request->id . ' Amounted to ' . ($row->amount)
            // ]);
            $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
            app()->setLocale($local);
            return authyResponse($row ,trans('auth.success'));
            // return makeClientHappy($expense);
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
            $row = $this->primary_model->findOrFail($request->id);
            $row->delete();
            $this->uploadModel->where('model_name','expenses')->where('model_ref_id',$request->id)->delete();
            $this->transaction_model->where("type","expense")->where('ref_id',$request->id)->delete();

            setActivityLogs([
                'log_name' => 'Expense has deleted',
                'subject_id'=>$row['id'],
                'subject_type' => 'Expense',
                'causer_id'=>$current_user->id,
                'causer_type'=>'AppUser',
                'recorded_by'=>$current_user->id,
                'description' =>$current_user->full_name .' has deleted revenue, ID ' . $row['sys_gen_id'] . ', Amounted to ' . ($row['amount']),
                'module' => 'expenses',
                'description_ar' => trans('Logs.amount_to')." ".($row['amount']).",".$row['sys_gen_id']." " .trans('Logs.id').",". trans('Logs.has_deleted'). $current_user->full_name
            ]);
            // session()->put('activity_log_data', [
            //     'identifier' => 'deleted',
            //     'subject_type' => $row,
            //     'name' => 'customer_name',
            //     'module' => $this->module,
            //     'data' => 'ID ' . $request->id . ' Amounted to ' . ($row->amount),
            //     'data_ar' => trans('Logs.has_edited'). 'ID ' . $request->id . ' Amounted to ' . ($row->amount)
            // ]);
            return sendMsgToClient(trans('auth.deleted_successfully'));
        } catch (\Exception $e) {
           //return sendExpToClient($e);
           return sendErrorToClient("The selected id is invalid.");
        }
    }

    public function get(Request $request)
    {
        $id = $request->id;
        $validation = Validator::make($request->all(), Rules::get());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        $data = Expense::with(['Image','status'])->findOrFail($id);
        return authyResponse($data ,trans('auth.success'));
    }

    public function search(Request $request)
    {
        try {

            $expense = $this->primary_model->searchExpenses($request->all(), $this->data_limit);

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

            $rules = Rules::import();

            Excel::import(new Importer($request->user_id, $status_id, $rules), request()->file('file'));

            $this->primary_model->insert($data_to_insert);

            $response = $this->primary_model->apiListing($request->user_id, $this->data_limit);

            return PagintionResponse($response,trans('auth.success'));

        } catch (ValidationException $e) {
            return sendErrorToClient(reset($e->errors()[0]));
        }
    }

    public function subCategoryListing(Request $request){


        //->with(['category'])
       $categories=[];
       $subcat =  $this->exp_cat_model->select('id','title','slug')->get();
       foreach($subcat as $cat){
            if(trans("categories.".$cat->slug) == "categories.".$cat->slug){
                $title_ar ='';
            }else{
                $title_ar =trans("categories.".$cat->slug);
            }
            $categories[]=['id' => $cat->id,'title'=>$cat->title,'title_ar'=>$title_ar,'slug'=>$cat->slug];
       }
       return authyResponse($categories ,trans('auth.success'));
    }

    public function fixedCategoryListing(Request $request){
         $headers = apache_request_headers();

         $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
         app()->setLocale($local);

        $fixed_cat =  $this->type_model->select('id','title','slug')->whereIn('id',[8,9,10,11])->get();

        foreach($fixed_cat as $cat){
            if(trans("categories.".$cat->slug) == "categories.".$cat->slug){
                $title_ar ='';
            }else{
                $title_ar =trans("categories.".$cat->slug);
            }
            $categories[]=['id' => $cat->id,'title'=>$cat->title,'title_ar'=>$title_ar,'slug'=>$cat->slug];
       }
        return authyResponse($categories ,trans('auth.success'));
      //  return authyResponse($fixed_cat ,'success');
    }

    public function Categories(Request $request){
        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){

            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);

        $response=[];
        if(isset($request->cat_type) && $request->cat_type == 6){
            $response = $this->type_model->where("type",'fixed_expense')->where('user_id',$request->user_id)->whereNull('deleted_at')->get()->toArray();
        }else if(isset($request->cat_type) && $request->cat_type == 7){
            $response = $this->type_model->where("type",'variable_expense')->where('user_id',$request->user_id)->whereNull('deleted_at')->get()->toArray();
        }

        return authyResponse($response ,trans('auth.success'));
    }
    public function addSubCategory(Request $request){

        $validation = Validator::make($request->all(), TypeRule::store());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        $slug = strtolower(str_replace(" ","_",$request->title));
        $request->merge(['slug'=>$slug]);
        if($request->type_id==6)
            $request->merge(['type'=>'fixed_expense']);
        else if($request->type_id==7)
            $request->merge(['type'=>'variable_expense']);

        $sub_cat =  $this->type_model->store($request);
        return makeClientHappy($sub_cat,trans('auth.success'));
    }

    public function deleteSubCategory(Request $request)
    {
        $validation = Validator::make($request->all(), TypeRule::delete());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
       // $sub_cat = $this->type_model->where('id',$request->id)->delete();
        $sub_cat = $this->type_model->where('id',$request->id)->update(['status' => 0]);
        return sendMsgToClient(trans('auth.deleted'));
    }

    public function CategoryListing(Request $request)
    {
        $categories = [];
        $sub_cat = $this->type_model->select('id','title','type','type_id');

        if(isset($request->type)){
            if($request->type == 6)
                $request->merge(['type'=>'fixed_expense']);
            else if($request->type == 7)
                $request->merge(['type'=>'variable_expense']);
            $sub_cat->where('type',$request->type);
        }

        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){

            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);


        $result = $sub_cat->where('user_id',$request->user_id)->where('status',1)->get();

        foreach($result as $cat){
            if(trans("categories.".strtolower(str_replace(" ","_",$cat->title))) == "categories.".strtolower(str_replace(" ","_",$cat->title))){
                $title_ar ='';
            }else{
                $title_ar =trans("categories.".strtolower(str_replace(" ","_",$cat->title)));
            }
            $categories[]=['id' => $cat->id,'title'=>$cat->title,'title_ar'=>$title_ar,'type'=>$cat->type,'type_id'=>$cat->type_id];
        }
        return pageResponse($categories,trans('auth.success'));
    }
}
