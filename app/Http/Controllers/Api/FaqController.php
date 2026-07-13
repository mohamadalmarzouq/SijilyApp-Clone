<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Faq;

class FaqController extends Controller
{
     public function __construct()
    {
        $this->primary_model = new Faq();
    }
    public function list(){

        
        $faq =  $this->primary_model->faqs();
        return makeClientHappy($faq);
    }
}
