<?php

namespace App\Http\Controllers\Api;

use App\Http\Validation\RulesStatus as Rules;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StatusController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Status();
        $this->module = $this->primary_model->getTable();
    }

    public function get(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::get());

        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        try {
            $account = $this->primary_model->getStatusByModule($request->module);

            return makeClientHappy($account,trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }
}
