<?php   namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageTransaction extends Model
{
    protected $fillable = ["transaction_id","image_ref_id", "source"];
    protected $table = "image_transaction";
}
