<?php

namespace App\Models;
use App\Models\Upload;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pending extends Model
{
    use SoftDeletes;

    protected $fillable = ['sys_gen_id', 'user_id', 'date', 'desc', 'amount','recorded_by'];

    protected $appends = ['module'];

    protected $guarded = [];

    public function draftable()
    {
        return $this->morphTo();
    }

    public function getModuleAttribute()
    {
        //return $this->draftable->getTable();
    }

    public function apiListing($request,$user_id, $limit)
    {
        $data=[];
        $results = $this->with(['Image'])->where('user_id', $user_id)
            ->where('status',1)
            ->whereNull('deleted_at');
            if(isset($request['start_date']) && isset($request['end_date'])){
                // $results->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
                // $results->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
                 $results->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            }else if(isset($request['start_date'])){
                $results->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            }else if(isset($request['end_date'])){
                $results->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            }
            if(isset($request['search'])){
                $results->where("desc","LIKE",'%'.$request['search'].'%');
            }

            if(isset($request['recorded_by'])){
                $results->where("recorded_by",$request['recorded_by']);
            }

            $result = $results->orderBy('id','desc')->paginate($limit)->toArray();
            $fulldata['data']  = $result['data'];
            $data['page'] =  $result;
            unset($data['page']['data']);
            unset($fulldata['data']['message']);
            $new_row = array_merge($fulldata,$data);
            return $new_row;
    }

    public function pending($id){
        return $this->with(['Image:id,source'])->findOrFail($id);
    }

    public function Image(){
        return $this->hasMany(Upload::class, 'model_ref_id', 'id')->where('model_name','pendings');
    }
}

