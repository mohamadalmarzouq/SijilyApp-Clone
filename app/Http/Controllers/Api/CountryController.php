<?php

namespace App\Http\Controllers\Api;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CountryController extends Controller
{
    function __construct(){
        $this->primary_model = new Country();
    }

    public function Country(Request $request){
        $gcc = $this->primary_model->select('id','name_en','name_ar','flag')->where('is_gcc', 1)->orderBy('name_en','ASC')->get()->toArray();
        $non_gcc = $this->primary_model->select('id','name_en','name_ar','flag')->where('is_gcc', 0)->orderBy('name_en','ASC')->get()->toArray();
        $data = array_merge($gcc, $non_gcc);
        $i = 0;
        foreach ($data as $datum){
            $data[$i]['flag'] = asset('country_flags') . '/' . $data[$i]['flag'];

            $i++;
        }
        return makeClientHappy($data,trans('auth.success'));
    }
}
