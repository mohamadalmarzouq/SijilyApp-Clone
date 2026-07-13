<?php

namespace App\Http\Controllers\Api;

use App\Http\Validation\RulesAppUser as Rules;
use App\Models\AppUser;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class IndustryController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Industry();
        $this->module = $this->primary_model->getTable();
    }

    public function listing(Request $request)
    {
        app()->setLocale('ar');

        try {
            $response = $this->primary_model->apiListing($this->data_limit);
            return PagintionResponse($response,trans('auth.success'));
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }
}
