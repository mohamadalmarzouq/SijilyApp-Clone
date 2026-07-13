<?php

namespace App\Http\Controllers\Api;

use App\Http\Validation\RulesSales as Rules;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class AgingReportController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Sale();
        $this->expense_model = new Expense();
        $this->purchase_model = new Purchase();
        $this->module = $this->primary_model->getTable();

    }

    public function agingReport(Request $request)
    {
        $parent_id = getParentId('app_users', 'id', $request->user_id);

        if ($parent_id != 0) {

            $user_id = $parent_id;
        } else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id' => $user_id]);

        $report = $this->primary_model->report($request->all());
        return makeClientHappy($report, trans('auth.success'));
    }


    public function agingReportPayable(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'type' => 'required|in:expenses,purchases,all',
        ]);

        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        $parent_id = getParentId('app_users', 'id', $request->user_id);

        if ($parent_id != 0) {

            $user_id = $parent_id;
        } else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id' => $user_id]);

        if ($request->type == "expenses"){
            $report = $this->expense_model->report($request->all());
        }elseif($request->type == "purchases"){
            $report = $this->purchase_model->report($request->all());
        }else{
            $report = $this->expense_model->report($request->all());
            // $report2 = $this->purchase_model->report($request->all());
            // $report = array_merge($report2, $report1);
            // dd($report, $report1, $report2);
        }
        return makeClientHappy($report, trans('auth.success'));
    }


}
