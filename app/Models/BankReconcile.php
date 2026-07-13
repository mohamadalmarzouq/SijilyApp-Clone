<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankReconcile extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'date_from',
        'date_to',
        'opening_balance',
        'actual_balance',
        'cash_in',
        'cash_out',
        'ending_balance',
        'variance',
        'recorded_by',
        'sys_gen_id'
    ];

    public function apiListing($request,$user_id, $limit)
    {

        $data=[];

        $results = $this->with(['Image'])
            ->whereNull('deleted_at');


        if(isset($request['start_date']) && isset($request['end_date'])){
            $results->whereDate("date_from",">=",date('Y-m-d',strtotime($request['start_date'])));
            $results->whereDate("date_to","<=",date('Y-m-d',strtotime($request['end_date'])));
            //$results->whereBetween("date_from",[date('Y-m-d',strtotime($request['start_date'])),date('Y-m-d',strtotime($request['end_date']))]);
        }else if(isset($request['start_date'])){
            $results->whereDate("date_from",">=",date('Y-m-d',strtotime($request['start_date'])));
        }else if(isset($request['end_date'])){
            $results->whereDate("date_to","<=",date('Y-m-d',strtotime($request['end_date'])));
        }

        if(isset($request['search'])){
            // $results->where("opening_balance","LIKE",'%'.$request['search'].'%')
            // ->orWhere("actual_balance","LIKE",'%'.$request['search'].'%')
            // ->orWhere("cash_in","LIKE",'%'.$request['search'].'%')
            // ->orWhere("cash_out","LIKE",'%'.$request['search'].'%')
            // ->orWhere("ending_balance","LIKE",'%'.$request['search'].'%')
            // ->orWhere("variance","LIKE",'%'.$request['search'].'%');
            $results->where(function ($query) use ($request){
                $query->where("opening_balance","LIKE",'%'.$request['search'].'%')
                ->orWhere("actual_balance","LIKE",'%'.$request['search'].'%')
                ->orWhere("cash_in","LIKE",'%'.$request['search'].'%')
                ->orWhere("cash_out","LIKE",'%'.$request['search'].'%')
                ->orWhere("ending_balance","LIKE",'%'.$request['search'].'%')
                ->orWhere("variance","LIKE",'%'.$request['search'].'%');
            });
        }

        if(isset($request['recorded_by'])){
            $results->where("recorded_by",$request['recorded_by']);
        }

        if(isset($request['status_id'])){
            $results->where("status_id",$request['status_id']);
        }

        $result = $results->where('user_id', $user_id)->orderBy('id','desc')->paginate($limit)->toArray(); //->paginate($limit)->toArray();

        $fulldata['data']  = $result['data'];


        //get total amount
        // $widgets['total_amount'] =  $this->where('user_id',$user_id)->whereNull('deleted_at')->sum('amount');
        // $widgets['amount_paid'] =  $this->where('user_id',$user_id)->whereNull('deleted_at')->sum('amount_paid');
        // $widgets['not_paid_amount'] = $this->where('user_id',$user_id)->whereNull('deleted_at')->sum('remaining_amount');

        // $fulldata['info'] = $widgets;
        $data['page'] =  $result;
        unset($data['page']['data']);
        unset($fulldata['data']['message']);
        $new_row = array_merge($fulldata,$data);
        return $new_row;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function SelectBankData($id)
    {
        return $this->with(['Image'])->findOrFail($id);
    }

    public function pending()
    {
        return $this->morphOne(Pending::class, 'draftable');
    }

    public function Image(){
        return $this->hasMany(Upload::class, 'model_ref_id', 'id')->where('model_name','bank_reconciles');
    }
}
