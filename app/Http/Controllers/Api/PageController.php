<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Validation\RulesPage as Rules;
use App\Models\Page;

class PageController extends Controller
{

    function __construct()
    {
        $this->primary_model = new Page();
    }

    public function list(Request $request){

       $validation = Validator::make($request->all(), Rules::list());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",", $validation->messages()->all()));
        }
        $faq =  $this->primary_model->page($request->page_id);
        return makeClientHappy($faq);
    }
}
