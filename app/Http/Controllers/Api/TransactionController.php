<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Upload;
use App\Models\Transaction;
use App\Models\AppUser;
use App\Models\ImageTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Transaction();
        $this->purchase_model = new Purchase();
        $this->sale_model = new Sale();
        $this->expense_model = new Expense();
        $this->upload_model = new Upload();
    }

    public function store(Request $request){

        $validation = Validator::make($request->all(), [
            'amount' => 'required',
            'note' => 'required',
            'date' => 'required',
            'ref_id'=>'required',
            'type'=>'required',
            'child_sys_gen_id' => 'required',
            ]);

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }

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

        $module = ["","sale","expense","purchase"];
        $request->merge(["type_id"=>$request->type]);
        $request->type = $module[$request->type];
        $request->merge(["type"=>$request->type]);

        if($request->type == "sale"){
            $row = $this->SaleStore($request);
        }else if($request->type == "expense"){
            $row = $this->ExpenseStore($request);
        }else if($request->type == "purchase"){
            $row = $this->PurchaseStore($request);
        }

        return makeClientHappy($row,trans('auth.success'));
    }

    public function SaleStore($request){
        $sale = $this->sale_model->getSales($request->ref_id);
        $request->merge(['customer_id'=>$sale->customer_id]);
        $request->merge(['customer_name'=>$sale->customer_name]);
        $id = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;
        if($request->hasFile('file')){
            $files = $request->file('file');
            $total_file = count($files);
        }

        if($request->hasFile('file') && $total_file > 0){
            for($i = 0; $i < $total_file;$i++){
                $data[$i]['file'] = $files[$i];
                $this->upload_model->uploadSingleFile($files[$i], $request->ref_id,$this->sale_model->getTable(), true, $id);
            }
        }

        $row = $this->primary_model->with('Image')->findOrFail($id);
         $current_user = AppUser::find($request->user_id);
        if($sale->remaining_amount != 0){
            $remaining_amount = $sale->remaining_amount - $request->amount;
            $request->merge(['remaining_amount'=> $remaining_amount]);
            $request->merge(['received_amount'=> $sale->received_amount + $request->amount]);
        }else{
            $remaining_amount = $request->amount;
            $request->merge(['remaining_amount' =>0 ]);
            $request->merge(['received_amount' => $request->amount ]);
            $request->merge(['status_id' => 6]);
        }

        $request->merge(['amount'=>$sale->amount]);
        unset($request['date']);
        $this->sale_model->where('id',$request->ref_id)->update($request->only($this->sale_model->getFillable()));

        $get_remaining_amount = $this->sale_model->getRemainingAmount($request->ref_id);
        if($get_remaining_amount == 0){
            $this->sale_model->where('id',$request->ref_id)->update(["status_id"=>6]);
        }
        $headers = apache_request_headers();
        app()->setLocale('ar');


        if($remaining_amount > 0){
            setActivityLogs([
                    'log_name' => 'Sale has recorded',
                    'subject_id'=>$row['id'],
                    'subject_type' => 'Sale',
                    'causer_id'=>$row['user_id'],
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$row['user_id'],
                    'description' =>$current_user->full_name .' has recorded revenue, customer '.$row['customer_name'] .', receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($remaining_amount) . ' not received',
                    'module' => 'sales',
                    'description_ar' => trans('Logs.amount_to')." ".($remaining_amount).trans('Logs.not_received').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",".$row['customer_name'].trans('Logs.has_recorded_revenue') .",".trans('Logs.customer'). $current_user->full_name
                ]);
        }else{
            setActivityLogs([
                'log_name' => 'Sale has recorded',
                'subject_id'=>$row['id'],
                'subject_type' => 'Sale',
                'causer_id'=>$row['user_id'],
                'causer_type'=>'AppUser',
                'recorded_by'=>$row['user_id'],
                'description' =>$current_user->full_name .' has recorded revenue, receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount) . ' received',
                'module' => 'sales',
                'description_ar' => trans('Logs.amount_to')." ".($request->amount).trans('Logs.received').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",". trans('Logs.has_recorded_revenue'). $current_user->full_name
            ]);
        }

        $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
            app()->setLocale($local);
        return $row;
    }

    public function ExpenseStore($request){
         $current_user = AppUser::find($request->user_id);
        $expense = $this->expense_model->getExpenses($request->ref_id);
        $request->merge(['customer_id'=>$expense->vendor_id]);
        $request->merge(['customer_name'=>$expense->vendor_name]);
        $id = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;

        if($request->hasFile('file')){
            $files = $request->file('file');
            $total_file = count($files);
        }

        if($request->hasFile('file') && $total_file > 0){
            for($i = 0; $i < $total_file;$i++){
                $data[$i]['file'] = $files[$i];
                $this->upload_model->uploadSingleFile($files[$i], $request->ref_id,$this->expense_model->getTable(), true, $id);
            }
        }

        $row = $this->primary_model->with('Image')->findOrFail($id);

        if($expense->remaining_amount != 0){
            $remaining_amount = $expense->remaining_amount - $request->amount;
            $request->merge(['remaining_amount'=> $remaining_amount]);
            $request->merge(['amount_paid'=> $expense->amount_paid + $request->amount]);
        }else{
            $remaining_amount = $request->amount;
            $request->merge(['remaining_amount' =>0 ]);
            $request->merge(['amount_paid' => $request->amount ]);
            $request->merge(['status_id' => 7]);
        }

        $request->merge(['amount'=>$expense->amount]);
        unset($request['date']);
        $this->expense_model->where('id',$request->ref_id)->update($request->only($this->expense_model->getFillable()));

        $get_remaining_amount = $this->expense_model->getRemainingAmount($request->ref_id);
        if($get_remaining_amount == 0){
            $this->expense_model->where('id',$request->ref_id)->update(["status_id"=>7]);
        }
        $headers = apache_request_headers();
        app()->setLocale('ar');

        if($remaining_amount > 0){
            setActivityLogs([
                'log_name' => 'Expense has recorded',
                'subject_id'=>$row['id'],
                'subject_type' => 'Expense',
                'causer_id'=>$row['user_id'],
                'causer_type'=>'AppUser',
                'recorded_by'=>$row['user_id'],
                'description' =>$current_user->full_name .' has recorded expense, supplier '.$row['customer_name'] .', receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($remaining_amount) . ' not paid',
                'module' => 'expenses',
                'description_ar' => trans('Logs.amount_to')." ".($remaining_amount).trans('Logs.not_paid').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",".$row['customer_name'].trans('Logs.has_recorded_expense') .",".trans('Logs.suppliers'). $current_user->full_name
            ]);

        }else{
            setActivityLogs([
                'log_name' => 'Expense has recorded',
                'subject_id'=>$row['id'],
                'subject_type' => 'Expense',
                'causer_id'=>$row['user_id'],
                'causer_type'=>'AppUser',
                'recorded_by'=>$row['user_id'],
                'description' =>$current_user->full_name .' has recorded expense, receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount) . ' paid',
                'module' => 'expenses',
                'description_ar' => trans('Logs.amount_to')." ".($request->amount).trans('Logs.paid').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",". trans('Logs.has_recorded_revenue'). $current_user->full_name
            ]);
        }
        // session()->put('activity_log_data', [
        //     'identifier' => 'payable',
        //     'subject_type' => $row,
        //     'name' => 'title',
        //     'data' => 'supplier ' . $expense->vendor_name . ' receipt ID ' . $request->sys_gen_id . ' Amounted to ' . ($request->amount),
        //     'module' => 'account_payable',
        //     'data_ar'=>trans('Logs.amount_to').($request->amount).$expense->vendor_name.trans('Logs.supplier').$request->sys_gen_id.trans('Logs.paid').",".trans('Logs.receipt_id')
        // ]);
        $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
        app()->setLocale($local);
        return $row;
    }

    public function PurchaseStore($request){
         $current_user = AppUser::find($request->user_id);
        $purchase = $this->purchase_model->getPurchase($request->ref_id);
        $request->merge(['customer_id'=>$purchase->vendor_id]);
        $request->merge(['customer_name'=>$purchase->vendor_name]);
        $id = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;

        if($request->hasFile('file')){
            $files = $request->file('file');
            $total_file = count($files);
        }

        if($request->hasFile('file') && $total_file > 0){
            for($i = 0; $i < $total_file;$i++){
                $data[$i]['file'] = $files[$i];
                $this->upload_model->uploadSingleFile($files[$i], $request->ref_id,$this->purchase_model->getTable(), true, $id);
            }
        }

        $row = $this->primary_model->with('Image')->findOrFail($id);

        if($purchase->remaining_amount != 0){
            $remaining_amount = $purchase->remaining_amount - $request->amount;
            $request->merge(['remaining_amount'=> $remaining_amount]);
            $request->merge(['amount_paid'=> $purchase->amount_paid + $request->amount]);
        }else{
            $remaining_amount = $request->amount;
            $request->merge(['remaining_amount' => 0]);
            $request->merge(['amount_paid' => $request->amount ]);
            $request->merge(['status_id' => 9]);
        }

        $request->merge(['amount'=>$purchase->amount]);
        unset($request['date']);
        $this->purchase_model->where('id',$request->ref_id)->update($request->only($this->purchase_model->getFillable()));

        $get_remaining_amount = $this->purchase_model->getRemainingAmount($request->ref_id);

        if($get_remaining_amount == 0){
            $this->purchase_model->where('id',$request->ref_id)->update(["status_id"=>9]);
        }
        $headers = apache_request_headers();
        app()->setLocale('ar');

        if($remaining_amount > 0){
            setActivityLogs([
                    'log_name' => 'capital expenditures has recorded',
                    'subject_id'=>$row['id'],
                    'subject_type' => 'Purchase',
                    'causer_id'=>$row['user_id'],
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$row['user_id'],
                    'description' =>$current_user->full_name .' has recorded capital expenditures, Supplier '.$row['customer_name'] .', receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($remaining_amount) . ' not paid',
                    'module' => 'purchases',
                    'description_ar' => trans('Logs.amount_to')." ".($remaining_amount).trans('Logs.not_paid').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",".$row['customer_name'].trans('Logs.has_recorded_capital_expenditures') .",".trans('Logs.supplier'). $current_user->full_name
                ]);

        }else{
            setActivityLogs([
                    'log_name' => 'Capital Expenditure has recorded',
                    'subject_id'=>$row['id'],
                    'subject_type' => 'Purchase',
                    'causer_id'=>$row['user_id'],
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$row['user_id'],
                    'description' =>$current_user->full_name .' has recorded capital expenditures, receipt ID ' . $request->sys_gen_id . ', Amounted to ' . ($request->amount) . ' paid',
                    'module' => 'purchases',
                    'description_ar' => trans('Logs.amount_to')." ".($request->amount).trans('Logs.paid').",".$request->sys_gen_id." " .trans('Logs.receipt_id').",". trans('Logs.has_recorded_capital_expenditures'). $current_user->full_name
                ]);

        }
        // session()->put('activity_log_data', [
        //     'identifier' => 'payable',
        //     'subject_type' => $row,
        //     'name' => 'title',
        //     'data' => 'supplier ' . $purchase->vendor_name . ' receipt ID ' . $request->sys_gen_id . ' Amounted to ' . ($request->amount),
        //     'module' => 'account_payable',
        //     'data_ar'=>trans('Logs.amount_to').($request->amount).$purchase->vendor_name.trans('Logs.supplier').$request->sys_gen_id.trans('Logs.paid').",".trans('Logs.receipt_id')
        // ]);
        $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
            app()->setLocale($local);
        return $row;
    }

    public function UpdateTransaction(Request $request){
         app()->setLocale('ar');
         $current_user = AppUser::find($request->user_id);
        $module = ["","sale","expense","purchase"];
            $validation = Validator::make($request->all(), [
                'id' => 'required',
                'ref_model'=>'required',
                'amount' => 'required',
                'note' => 'required',
                'date' => 'required',
                'ref_id'=>'required'
            ]);

            if ($validation->fails()) {
                return sendErrorToClient(implode(",",$validation->messages()->all()));
            }

            $this->primary_model->where('id',$request->id)->update([
                'amount'=>$request->amount,
                'note'=>$request->note,
                'date'=>$request->date,
            ]);

            $trans = $this->primary_model->getChildTransaction($request->id);
            // $trans = $this->primary_model->getTransaction($request->id);


            if($request->hasFile('file')){
                $files = $request->file('file');
                $total_file = count($files);
            }

            if(isset($request->delete_files) && !empty($request->delete_files)){
                $id = explode(",",$request->delete_files);
                ImageTransaction::whereIn('id',$id)->delete();
            }

            if($module[$request->ref_model]=="sale"){
                $received_amount = $request->received_amount;
                $remaining_amount = $request->remaining_amount;
                $this->sale_model->where('id',$request->ref_id)->update(["received_amount"=>$received_amount,"remaining_amount"=>$remaining_amount]);
                $get_remaining_amount = $this->sale_model->getRemainingAmount($request->ref_id);
                if($get_remaining_amount == 0){
                    $status = 6;
                }else{
                    $status = 10;
                }
                $this->sale_model->where('id',$request->ref_id)->update(["status_id"=>$status,'last_trans_update'=>date('Y-m-d h:i:s')]);
                setActivityLogs([
                    'log_name' => 'Sale has updated',
                    'subject_id'=>$trans['id'],
                    'subject_type' => 'Sale',
                    'causer_id'=>$trans['user_id'],
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$trans['user_id'],
                    'description' =>$current_user->full_name .' has edited revenue, ID ' . $trans['id'] . ', Amounted to ' . ($trans['amount']),
                    'module' => 'sales',
                    'description_ar' => trans('Logs.amount_to')." ".($trans['amount']).",".$trans['id']." " .trans('Logs.id').",". trans('Logs.has_edited'). $current_user->full_name
                ]);

                if($request->hasFile('file') && $total_file > 0){
                    for($i = 0; $i < $total_file;$i++){
                        $data[$i]['file'] = $files[$i];
                        $this->upload_model->uploadSingleFile($files[$i], $request->ref_id,$this->sale_model->getTable(), true, $request->id);
                        // $this->upload_model->transactionUpload($request->id,$files[$i]);
                    }
                }

            }else if($module[$request->ref_model]=="expense"){
                $amount_paid = $request->received_amount;
                $remaining_amount = $request->remaining_amount;
                $this->expense_model->where('id',$request->ref_id)->update(["amount_paid"=>$amount_paid,"remaining_amount"=>$remaining_amount]);
                $get_remaining_amount = $this->expense_model->getRemainingAmount($request->ref_id);

                if($get_remaining_amount == 0){
                    $status = 7;
                }else{
                    $status = 11;
                }

                if($request->hasFile('file') && $total_file > 0){
                    for($i = 0; $i < $total_file;$i++){
                        $data[$i]['file'] = $files[$i];
                        $this->upload_model->uploadSingleFile($files[$i], $request->ref_id,$this->expense_model->getTable(), true, $request->id);
                        // $this->upload_model->transactionUpload($request->id,$files[$i]);
                    }
                }


                $this->expense_model->where('id',$request->ref_id)->update(["status_id"=>$status,'last_trans_update'=>date('Y-m-d h:i:s')]);
                setActivityLogs([
                    'log_name' => 'Expense has updated',
                    'subject_id'=>$trans['id'],
                    'subject_type' => 'Expense',
                    'causer_id'=>$trans['user_id'],
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$trans['user_id'],
                    'description' =>$current_user->full_name .' has edited expense, ID ' . $trans['id'] . ', Amounted to ' . ($trans['amount']),
                    'module' => 'expenses',
                    'description_ar' => trans('Logs.amount_to')." ".($trans['amount']).",".$trans['id']." " .trans('Logs.id').",". trans('Logs.has_edited'). $current_user->full_name
                ]);
            }else if($module[$request->ref_model]=="purchase"){
                $amount_paid = $request->received_amount;
                $remaining_amount = $request->remaining_amount;
                $this->purchase_model->where('id',$request->ref_id)->update(["amount_paid"=>$amount_paid,"remaining_amount"=>$remaining_amount]);
                $get_remaining_amount = $this->purchase_model->getRemainingAmount($request->ref_id);

                if($get_remaining_amount == 0){
                    $status = 9;
                }else{
                    $status = 8;
                }

                $this->purchase_model->where('id',$request->ref_id)->update(["status_id"=>$status,'last_trans_update'=>date('Y-m-d h:i:s')]);
                setActivityLogs([
                    'log_name' => 'Capital Expenditure has updated',
                    'subject_id'=>$trans['id'],
                    'subject_type' => 'Purchase',
                    'causer_id'=>$trans['user_id'],
                    'causer_type'=>'AppUser',
                    'recorded_by'=>$trans['user_id'],
                    'description' =>$current_user->full_name .' has edited revenue, ID ' . $trans['id'] . ', Amounted to ' . ($trans['amount']),
                    'module' => 'purchases',
                    'description_ar' => trans('Logs.amount_to')." ".($trans['amount']).",".$trans['id']." " .trans('Logs.id').",". trans('Logs.has_edited'). $current_user->full_name
                ]);

                if($request->hasFile('file') && $total_file > 0){
                    for($i = 0; $i < $total_file;$i++){
                        $data[$i]['file'] = $files[$i];
                        $this->upload_model->uploadSingleFile($files[$i], $request->ref_id,$this->purchase_model->getTable(), true,$request->id);
                        // $this->upload_model->transactionUpload($request->id,$files[$i]);
                    }
                }
            }

            $transaction_images = ImageTransaction::where('transaction_id',$trans['id'])->get();

            $images = [];

            foreach($transaction_images as $image){
                $images[] = ['id' => $image->id ,'source'=>$image->source];
            }


            $trans['image'] = $images;
            return makeClientHappy($trans,trans('auth.success'));

    }

    public function updateSale($ref_id,$TransactionAmount){
        $sale = $this->sale_model->getSales($ref_id);
        $received_amount = $sale->received_amount - $TransactionAmount;
        $remaining_amount = $sale->remaining_amount + $TransactionAmount;
        $this->sale_model->where('id',$ref_id)->update(['received_amount' => $received_amount,'remaining_amount' => $remaining_amount]);
        $get_remaining_amount = $this->sale_model->getRemainingAmount($ref_id);
        if($get_remaining_amount == 0){
            $status = 6;
        }else{
            $status = 10;
        }
        $this->sale_model->where('id',$ref_id)->update(["status_id"=>$status]);
    }

    public function updateExpense($ref_id,$TransactionAmount){
        $getExpenses = $this->expense_model->getExpenses($ref_id);
        $amount_paid = $getExpenses->amount_paid - $TransactionAmount;
        $remaining_amount = $getExpenses->remaining_amount + $TransactionAmount;
        $this->expense_model->where('id',$ref_id)->update(['amount_paid' => $amount_paid,'remaining_amount' => $remaining_amount]);

        $get_remaining_amount = $this->expense_model->getRemainingAmount($ref_id);

        if($get_remaining_amount == 0){
            $status = 7;
        }else{
            $status = 11;
        }
        $this->expense_model->where('id',$ref_id)->update(["status_id"=>$status]);

    }

    public function updatePurchase($ref_id,$TransactionAmount){
        $sale = $this->purchase_model->getPurchase($ref_id);
        $amount_paid = $sale->amount_paid - $TransactionAmount;
        $remaining_amount = $sale->remaining_amount + $TransactionAmount;
        $this->purchase_model->where('id',$ref_id)->update(['amount_paid' => $amount_paid,'remaining_amount' => $remaining_amount]);

        $get_remaining_amount = $this->purchase_model->getRemainingAmount($ref_id);

        if($get_remaining_amount == 0){
            $status = 9;
        }else{
            $status = 8;
        }

        $this->purchase_model->where('id',$ref_id)->update(["status_id"=>$status]);

    }

    public function deleteTransaction(Request $request){

         $current_user = AppUser::find($request->user_id);
        $module = ["","sale","expense","purchase"];
        $validation = Validator::make($request->all(), ['id' => 'required','ref_model'=>'required']);

        if ($validation->fails()) {
            return sendErrorToClient(implode(",",$validation->messages()->all()));
        }
         app()->setLocale('ar');
        $transaction = $this->primary_model->getTransaction($request->id);
        $ref_id = $transaction->ref_id;
        $TransactionAmount = $transaction->amount;
        if($module[$request->ref_model]=="sale"){
            $this->updateSale($ref_id,$TransactionAmount);
            setActivityLogs([
                'log_name' => 'Sale has deleted',
                'subject_id'=>$transaction['id'],
                'subject_type' => 'Sale',
                'causer_id'=>$transaction['user_id'],
                'causer_type'=>'AppUser',
                'recorded_by'=>$transaction['user_id'],
                'description' =>$current_user->full_name .' has deleted revenue, ID ' . $transaction['id'] . ', Amounted to ' . ($transaction['amount']),
                'module' => 'sales',
                'description_ar' => trans('Logs.amount_to')." ".($transaction['amount']).",".$transaction['id']." " .trans('Logs.id').",". trans('Logs.has_deleted'). $current_user->full_name
            ]);
        }else if($module[$request->ref_model]=="expense"){
            $this->updateExpense($ref_id,$TransactionAmount);
            setActivityLogs([
                'log_name' => 'Expense has deleted',
                'subject_id'=>$transaction['id'],
                'subject_type' => 'Sale',
                'causer_id'=>$transaction['user_id'],
                'causer_type'=>'AppUser',
                'recorded_by'=>$transaction['user_id'],
                'description' =>$current_user->full_name .' has deleted expense, ID ' . $transaction['id'] . ', Amounted to ' . ($transaction['amount']),
                'module' => 'expenses',
                'description_ar' => trans('Logs.amount_to')." ".($transaction['amount']).",".$transaction['id']." " .trans('Logs.id').",". trans('Logs.has_deleted'). $current_user->full_name
            ]);
        }else if($module[$request->ref_model]=="purchase"){
            $this->updatePurchase($ref_id,$TransactionAmount);
            setActivityLogs([
                'log_name' => 'Capital Expenditure has deleted',
                'subject_id'=>$transaction['id'],
                'subject_type' => 'Purchase',
                'causer_id'=>$transaction['user_id'],
                'causer_type'=>'AppUser',
                'recorded_by'=>$transaction['user_id'],
                'description' =>$current_user->full_name .' has deleted capital expenditure, ID ' . $transaction['id'] . ', Amounted to ' . ($transaction['amount']),
                'module' => 'purchases',
                'description_ar' => trans('Logs.amount_to')." ".($transaction['amount']).",".$transaction['id']." " .trans('Logs.id').",". trans('Logs.has_deleted'). $current_user->full_name
            ]);
        }

        $this->primary_model->where('id',$request->id)->delete();
        return sendMsgToClient(trans('auth.deleted'));
    }
}

