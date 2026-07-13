<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Storage;
use Request;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ImageTransaction;

class Upload extends Model
{
    use SoftDeletes;


    protected $fillable = ['id', 'model_name','model_ref_id','source'];
    protected $visible = ['id', 'source'];

    public function fileUpload($file_name, $user_id=null){
        if (Request::hasFile($file_name)) {
            $file = Request::file($file_name);
            $file_data = uploadFile($file, $user_id);
            return $file_data['src'];
            //dd($image);
            //return $this->updateOrCreate(['id' => $user_id], ['image'=>$file_data['src']);
        }
    }
    public function uploadFiles($files,$inventory_id,$model) {
        foreach ($files as $file) {
            $file_path = uploadCustomFile($file);
            $this->insert([
                'model_name'=> $model,
                'model_ref_id'=> $inventory_id,
                'source' => $file_path,
            ]);
        }
    }

    public function transactionUpload($transaction_id,$file_path){
        return ImageTransaction::create([
            'transaction_id' => $transaction_id,
            'source' => $file_path
        ]);
    }

    public function uploadSingleFile($uploadFile,$id,$model, $child = false, $transaction_id=null){
        $file_path = uploadCustomFile($uploadFile);
        if($child == true){
            return ImageTransaction::create([
                'transaction_id' => $transaction_id,
                'source' => $file_path
            ]);
        }else{
            return $this->create([
                'model_name'=> $model,
                'model_ref_id'=> $id,
                'source' => $file_path,
            ]);
        }
    }

    public function uploadFile($uploadFile){
        return uploadCustomFile($uploadFile);

    }
}
