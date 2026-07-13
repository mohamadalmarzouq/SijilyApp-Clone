<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $timestamps = false;

    public function get_country($id){

       $country =  $this->where('id',$id)->orderBy('name_en','ASC')->first();
       return json_encode($country);
    }
}
