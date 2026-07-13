<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Storage;
use Request;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryImages extends Model
{
    use SoftDeletes;


    protected $fillable = ['tran_id','source','stock_id','image_ref_id'];
    // protected $visible = ['id', 'source','stock_id','image_ref_id'];

    public function uploadSingleFile($uploadFile,$id,$stock_id){
        $file_path = uploadCustomFile($uploadFile);
            $this->insert([
                'tran_id'=> $id,
                'source' => $file_path,
                'stock_id'=> $stock_id
            ]);
    }
}
