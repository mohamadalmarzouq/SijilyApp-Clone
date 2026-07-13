<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OwnerAccount extends Model
{
    use SoftDeletes;

    protected $fillable = ['sys_gen_id','amount', 'date', 'user_id','owner_name','owner_id','status_id','desc','recorded_by'];

    public function apiListing($request,$limit, $user_id)
    {
        $results = $this->with(['Status','Image','recordedBy'])
        ->whereNull('deleted_at');

        $total = $this->whereNull('deleted_at');
        $in_flow = $this->whereNull('deleted_at')->where('status_id','13');
        $out_flow = $this->whereNull('deleted_at')->where('status_id','14');

        if(isset($request['start_date']) && isset($request['end_date'])){
            $results->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $total->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $in_flow->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $out_flow->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
        }else if(isset($request['start_date'])){
            $results->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $total->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $in_flow->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $out_flow->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
        }else if(isset($request['end_date'])){
            $results->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $total->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $in_flow->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $out_flow->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
        }

        if(isset($request['search'])){
            $results->where("desc","LIKE",'%'.$request['search'].'%');
            $total->where("desc","LIKE",'%'.$request['search'].'%');
            $in_flow->where("desc","LIKE",'%'.$request['search'].'%');
            $out_flow->where("desc","LIKE",'%'.$request['search'].'%');
        }

        if(isset($request['recorded_by'])){
            $results->where("recorded_by",$request['recorded_by']);
            $total->where("recorded_by",$request['recorded_by']);
            $in_flow->where("recorded_by",$request['recorded_by']);
            $out_flow->where("recorded_by",$request['recorded_by']);
        }

        if(isset($request['owner_name'])){
            $results->where("owner_name","LIKE",'%'.$request['owner_name'].'%');
            $total->where("owner_name","LIKE",'%'.$request['owner_name'].'%');
            $in_flow->where("owner_name","LIKE",'%'.$request['owner_name'].'%');
            $out_flow->where("owner_name","LIKE",'%'.$request['owner_name'].'%');
        }

        if(isset($request['status_id'])){
            $results->where("status_id",$request['status_id']);
            $total->where("status_id",$request['status_id']);
            $in_flow->where("status_id",$request['status_id']);
            $out_flow->where("status_id",$request['status_id']);
        }

        $result = $results->where('user_id', $user_id)->orderBy('id','desc')->paginate($limit)->toArray();
        $fulldata['data']  = $result['data'];



        //get total amount
        // $widgets['total'] =  $total->sum('amount');
        $widgets['total'] = ($in_flow->where('user_id',$user_id)->sum('amount') - $out_flow->where('user_id',$user_id)->sum('amount'));
        $widgets['in_flow'] =  $in_flow->where('user_id',$user_id)->sum('amount');
        $widgets['out_flow'] = $out_flow->where('user_id',$user_id)->sum('amount');
        $customers=  getOwnerAccount($request['user_id']);
        $fulldata['info'] = $widgets;
        // $fulldata['owner_accounts'] = $customers;
        // $fulldata['total'] = $this->where('user_id',$user_id)->whereNotNull('owner_name')->whereNull('deleted_at')->sum('amount');
        $data['page'] =  $result;
        unset($data['page']['data']);
        unset($fulldata['data']['message']);
        $new_row = array_merge($fulldata,$data);
        return $new_row;
    }

    public function getOwnerAccount($id)
    {
        return $this->with(['Image'])->where('id',$id)->get();
    }

    public function pending()
    {
        return $this->morphOne(Pending::class, 'draftable');
    }

    public function Image(){
        return $this->hasMany(Upload::class, 'model_ref_id', 'id')->where('model_name','owner_accounts');
    }

    public function recordedBy(){
        return $this->hasOne(AppUser::class, 'id', 'recorded_by')->select('id','full_name');
    }


    public function Status(){
        return $this->hasOne(Status::class, 'id', 'status_id')->select('id','title');
    }

    public function getScheduleCustomers($user_id){
        $data=[];
        $owner_name=[];
        $total_amount = [];
        $owners = getOwnerAccount($user_id);
        foreach($owners as $key=>$owner){
            $owner_name[] = ["owner_name" =>$owner->owners_name,'amount'=>$owner->inflow - $owner->outflow];
            $total_amount[] = $owner->inflow - $owner->outflow;
        }
        $data['owner_accounts'] = $owner_name ;
        $data['total'] = array_sum($total_amount) ;//$this->where('user_id',$user_id)->where('status_id','13')->whereNull('deleted_at')->sum('amount') -$this->where('user_id',$user_id)->where('status_id','14')->whereNull('deleted_at')->sum('amount');
        return $data;
    }
}
