<?php

namespace App\Http\Controllers\Api;

use App\Http\Validation\RulesIdentity as Rules;
use App\Http\Validation\RulesIdentityChild as RulesChild;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\OwnerAccount;
use App\Models\Pending;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Subscription;
use App\Models\AppUser;
use App\Models\StockTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class IdentityGeneratorController extends Controller
{
    function __construct()
    {
        $this->stock_model = new StockTransaction();
        $this->pending_model = new Pending();
        $this->sale_model = new Sale();
        $this->purchase_model = new Purchase();
        $this->inventory_model = new Inventory();
        $this->expense_model = new Expense();
        $this->owner_model = new OwnerAccount();
        $this->subscription_model = new Subscription();
        $this->transaction = new Transaction();
        // $this->pending_model = new Dashboard();
    }

    public function getId(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::get());

        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        $getUserDetail = AppUser::where('id', $request->user_id)->first();

        $userID = $getUserDetail->parent != 0 ? $getUserDetail->parent : $getUserDetail->id;
        if($request->id == 1){
            $id = "PEN" . date('y') . "-" .getId('pendings',$userID);
        }else if($request->id == 3){
            $id = 'REV' . date('y') . "-" . getId('sales',$userID);
        }else if($request->id == 7){
            $id = 'CAP' . date('y') . "-" . getId('purchases',$userID);
        }else if($request->id == 8){
            $id = 'OWN' . date('y') . "-" . getId('owner_accounts',$userID);
        }else if($request->id == 9){
            $id = 'STK' . date('y') . "-" . getId('inventories',$userID);
        }else if($request->id == 10){
            $id = 'BRE' . date('y') . "-" . getId('bank_reconciles',$userID);
        }else if($request->id == 4){
            $id = 'EXP' . date('y') . "-" . getId('expenses',$userID);
        }

        $data = ['id'=>$id];

        return makeClientHappy($data,trans('auth.success'));
    }

    public function getChildId(Request $request){
        $validation = Validator::make($request->all(), RulesChild::get());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        $child_sys_gen_id = NULL;
        $id = $request->id;
        if($request->type_id == 9){
            $sysIdFromTransaction = $this->stock_model::where(['stock_id' => $id])->latest('id')->first();
        }else{
            $sysIdFromTransaction = $this->transaction::where(['ref_id' => $id, 'type_id' => $request->type_id])->latest('id')->first();
        }

        if(isset($sysIdFromTransaction->child_sys_gen_id)){
            $getSysGeneratedId = $sysIdFromTransaction->child_sys_gen_id;
            $explodeSysGenId = explode('-', $getSysGeneratedId);
            if(count($explodeSysGenId) > 2){
                $explodeSysGenId[2] = (int) end($explodeSysGenId) + 1;
                $child_sys_gen_id = implode('-', $explodeSysGenId);
            }else{
                $child_sys_gen_id = $getSysGeneratedId.'-1';
            }
        }else{
            $child_sys_gen_id = $request->parent_sys_id.'-1';
        }
        $data = ['id'=>$child_sys_gen_id];
        return makeClientHappy($data,trans('auth.success'));
    }
}
