<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Inventory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_name',
        'sys_gen_id',
        'desc',
        'status_id',
        'user_id',
        'date',
        'emp_incharge',
        'recorded_by'
    ];

    public function Image()
    {
        return $this->hasMany(InventoryImages::class, 'tran_id', 'id');
    }

    public function file()
    {
        return $this->hasMany(Upload::class, 'model_ref_id', 'id')->where('model_name','inventories')->whereNull('deleted_at');
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function getInventory($id)
    {
        return $this->with(['status', 'Image','file'])->findOrFail($id);
    }

    public function stockTransactionCurrent()
    {
        return $this->hasOne(StockTransaction::class,'stock_id')->where('deleted',0)->orderBy('date','DESC');
    }

    public function apiListing($request,$user_id, $limit)
    {
        $data=[];

        $results = $this->with(['Image','file','stockTransactionCurrent'])
            ->whereNull('deleted_at');

        $total_amount= $this->whereNull('deleted_at');
        $in_stock= $this->whereNull('deleted_at')->where('status_id',5);
        $out_of_stock= $this->whereNull('deleted_at')->where('status_id',12);
        $nearly_out_of_stock= $this->whereNull('deleted_at')->where('status_id',15);


        if(isset($request['start_date']) && isset($request['end_date'])){
            $results->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $total_amount->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $in_stock->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $out_of_stock->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
            $nearly_out_of_stock->whereBetween("date",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
        }else if(isset($request['start_date'])){
            $results->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $total_amount->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $in_stock->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $out_of_stock->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
            $nearly_out_of_stock->whereDate("date",">=",date('Y-m-d',strtotime($request['start_date'])));
        }else if(isset($request['end_date'])){
            $results->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $total_amount->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $in_stock->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $out_of_stock->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
            $nearly_out_of_stock->whereDate("date","<=",date('Y-m-d',strtotime($request['end_date'])));
        }
        
        if(isset($request['search'])){

            $results->where(function ($query) use ($request){
                $query->where("desc","LIKE",'%'.$request['search'].'%')
                ->orWhere("item_name","LIKE",'%'.$request['search'].'%');
            });

            $total_amount->where(function ($query) use ($request){
                $query->where("desc","LIKE",'%'.$request['search'].'%')
                ->orWhere("item_name","LIKE",'%'.$request['search'].'%');
            });

            $in_stock->where(function ($query) use ($request){
                $query->where("desc","LIKE",'%'.$request['search'].'%')
                ->orWhere("item_name","LIKE",'%'.$request['search'].'%');
            });

            $out_of_stock->where(function ($query) use ($request){
                $query->where("desc","LIKE",'%'.$request['search'].'%')
                ->orWhere("item_name","LIKE",'%'.$request['search'].'%');
            });

            $nearly_out_of_stock->where(function ($query) use ($request){
                $query->where("desc","LIKE",'%'.$request['search'].'%')
                ->orWhere("item_name","LIKE",'%'.$request['search'].'%');
            });


            //$results->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("item_name","LIKE",'%'.$request['search'].'%');
            //$total_amount->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("item_name","LIKE",'%'.$request['search'].'%');
            //$in_stock->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("item_name","LIKE",'%'.$request['search'].'%');
            //$out_of_stock->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("item_name","LIKE",'%'.$request['search'].'%');
            //$nearly_out_of_stock->where("desc","LIKE",'%'.$request['search'].'%')->orWhere("item_name","LIKE",'%'.$request['search'].'%');
        }
        
        if(isset($request['recorded_by'])){
            $results->where("recorded_by",$request['recorded_by']);
            $total_amount->where("recorded_by",$request['recorded_by']);
            $in_stock->where("recorded_by",$request['recorded_by']);
            $out_of_stock->where("recorded_by",$request['recorded_by']);
            $nearly_out_of_stock->where("recorded_by",$request['recorded_by']);
        }

        if(isset($request['status_id'])){
            $results->where("status_id",$request['status_id']);
            $total_amount->where("status_id",$request['status_id']);
            $in_stock->where("status_id",$request['status_id']);
            $out_of_stock->where("status_id",$request['status_id']);
            $nearly_out_of_stock->where("status_id",$request['status_id']);
        }
        
        $result = $results->where('user_id', $user_id)->orderBy('id','desc')->paginate($limit)->toArray();
        $fulldata['data']  = $result['data'];
        //dd($in_stock->where('user_id',$user_id)->get()->toArray(), 'asdasdasd111132', $nearly_out_of_stock->where('user_id',$user_id)->get()->toArray());

        //get total amount
        $widgets['total'] =  $total_amount->where('user_id',$user_id)->count();
        $widgets['in_stock'] =  $in_stock->where('user_id',$user_id)->count();
        $widgets['out_of_stock'] =  $out_of_stock->where('user_id',$user_id)->count();
        $widgets['nearly_out_of_stock'] =  $nearly_out_of_stock->where('user_id',$user_id)->count();
        
        $fulldata['info'] = $widgets;
        $data['page'] =  $result;
        unset($data['page']['data']);
        unset($fulldata['data']['message']);
        $new_row = array_merge($fulldata,$data);

        return $new_row;
    }

    public function searchInventories($request, $limit)
    {
        $inventory = $this->with(['status', 'gallery', 'user'])->where('user_id', $request['user_id']);

        if (!empty($request['date'])) {

            $inventory = $inventory->whereRaw('DATE(created_at) = "' . $request['date'] . '"');
        }

        if (!empty($request['keyword'])) {

            $inventory = $inventory->where(function ($q) use ($request) {

                $q->orWhere('title', 'LIKE', '%' . $request['keyword'] . '%');

                $q->orWhere('description', 'LIKE', '%' . $request['keyword'] . '%');

                $q->orWhere('price', 'LIKE', '%' . $request['keyword'] . '%');

                $q->orWhere('quantity', 'LIKE', '%' . $request['keyword'] . '%');

            });
        }

        if (!empty($request['recorded_by_id'])) {
            $inventory = $inventory->where('user_id', $request['recorded_by_id']);
        }

        return $inventory->paginate($limit)->toArray();
    }

    public function pending()
    {
        return $this->morphOne(Pending::class, 'draftable');
    }
}
