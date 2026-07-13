<?php

namespace App\Http\Controllers\Panel;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CountryController extends Controller
{
    function __construct(){
        $this->primary_model = new Country();
    }

    public function Country(){
        return $this->primary_model->orderBy('name_en','ASC')->get()->toArray();
    }


}
