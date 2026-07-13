<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AppUser;
use App\Models\UserSubscription;
use Illuminate\Database\Eloquent\SoftDeletes;
use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoin;
use Carbon\Carbon;
class Subscription extends Model
{
    use SoftDeletes;
    use EloquentJoin;
    protected $useTableAlias = true;
    protected $appendRelationsCount = false;
    protected $leftJoin = true;
    protected $aggregateMethod = 'MAX';

    protected $fillable = ['subscription','subscription_ar','slug', 'expiry', 'amount', 'per_user_amount', 'status', 'image', 'content', 'content_ar', 'title', 'title_ar', 'type','register_limit','duration'];
    protected $module_name = 'subscriptions';

    public function getModuleName(){
            return $this->module_name.".";
    }
    public function preventSearch(){
        return [
                'Action',
             ];
    }
    public function getStatusAttribute($value){
         return $value ? 'Active' : 'In-Active';
    }

    // public function getTypeAttribute($value){
    //     switch($value){
    //         case 1:
    //              return 'Single User';
    //         break;
    //         case 2:
    //              return 'Multiple User';
    //         break;
    //         case 3:
    //              return 'Coming Soon';

    //         break;
    //     }

    // }

    // public function setTypeAttribute($value)
    // {
    //     $this->attributes['type'] = $value;
    // }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value;
    }

    public function Subscriptions($request){
        $subscriptions = $this->get()->toArray();
        $ids=[];
        foreach($subscriptions as $subscription){
            $AppUser = AppUser::where('id',$request['user_id'])->get()->first();
            if($AppUser['package_taken'] == 1 && $subscription['id'] == 1){
                continue;
            }else if($AppUser['free_package'] == 1 && $subscription['id'] == 1){
                continue;
            }
            $ids[] = $this->getSubscriptions($subscription['id']);
        }
        return array_filter($ids);
     }

     public function SubscriptionsIds($request){
        $subscriptions = $this->get()->toArray();
        $ids=[];
        foreach($subscriptions as $subscription){
            $AppUser = AppUser::where('id',$request['user_id'])->get()->first();
            if(isset($AppUser->free_package) && $AppUser->free_package == 1 && $subscription['id'] == 1){
                continue;
            }
            $ids[] = $subscription['id'];
        }
        return implode($ids,",");
     }


     public function getSubscriptions($id)
     {
        return $this->where("id",$id)->where('status',1)->get()->first();
     }

     public function getColumnsForDataTable(){
        $data = [
                ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
                ['data' => 'title', 'name' => 'title','title' => 'Title'],
                ['data' => 'subscription', 'name' => 'subscription', 'title' => 'Subscription'],
                ['data' => 'content', 'name' => 'content', 'title' => 'Content'],
                ['data' => 'per_user_amount', 'name' => 'per_user_amount','title' => 'Per User Amount'],
                ['data' => 'duration', 'name' => 'duration','title' => 'Subscription Duration (in days)'],
                ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
                ['data' => 'action', 'name' => 'Action', 'searchable' => 'false'],
                ['data' => 'created_at', 'name' => 'created_at', 'visible' => false]
            ];
        return json_encode($data);
    }

    public function orderArray()
    {
       return [
                ['data' => 'id', 'name' => 'id', 'order' => true],
                ['data' => 'title', 'name' => 'title', 'order' => true,"search"=>true],
                ['data' => 'subscription', 'name' => 'subscription', 'order' => true,"search"=>true],
                ['data' => 'content', 'name' => 'content', 'order' => true,"search"=>true],
                ['data' => 'per_user_amount', 'name' => 'per_user_amount', 'order' => true],
                ['data' => 'duration', 'name' => 'duration', 'order' => true],
                ['data' => 'status', 'name' => 'status', 'order' => true,"search"=>true],
                ['data' => 'action', 'name' => 'Action', 'order' => false],
                ['name' => 'created_at', 'order' => false]
         ];

    }

    public function orderingColumn()
    {
        return json_encode([['5', 'desc']]);
    }

    public function soldSubscriptions($request){
        $draw = $request->draw;
        $start = $request->start;
        $row_per_page = $request->length;
        $search = $request->search;
        $sortableColumns = [
            0 => 'id',
            1 => 'subscription',
            2 => 'no',
            3 => 'no_of_users'
        ];
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');

        $orderColumn = $sortableColumns[$orderColumnIndex] ?? 'id';


        $result = [];

        $date = date('Y-m-d', strtotime(getDuration($request->duration)));
        $date = date('Y-m-d', strtotime($date));
        // $data = $this->select('subscriptions.id','subscriptions.subscription',\DB::raw("COUNT(CASE WHEN DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.subscription_id END ) AS `no`"),\DB::raw("COALESCE(SUM(CASE WHEN  DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.no_of_users END),0) AS no_of_users"),\DB::raw("COALESCE(SUM(CASE WHEN DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.total_amount END),0) AS total_amount"))->join('user_subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')->groupBy('subscriptions.subscription')->get()->toArray();
        $query = $this->select(
            'subscriptions.id',
            'subscriptions.subscription',
            \DB::raw("COUNT(CASE WHEN DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.subscription_id END) AS no"),
            \DB::raw("COALESCE(SUM(CASE WHEN DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.no_of_users END), 0) AS no_of_users"),
            \DB::raw("COALESCE(SUM(CASE WHEN DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.total_amount END), 0) AS total_amount")
        )
            ->join('user_subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('subscriptions.subscription', 'LIKE', "%{$search}%");
            })
            ->groupBy('subscriptions.subscription')
            ->orderBy($orderColumn, $orderDir);

        // Apply limit if set and not "0"
        if (isset($request->limit) && $request->limit !== "0") {
            $query->limit((int)$request->limit);
        }

        // Execute the query
        $count=$query->get()->toArray();
        $data = $query->skip($start)->take($row_per_page)->get()->toArray();
        // $data->orderBy($orderColumn, $orderDir);

        foreach($data as $d){
             $result[] =array(
                "id" => "<span>".$d['id']."</span>",
                "subscription" => "<span>".$d['subscription']."</span>",
                "no" => "<span>".$d['no']."</span>",
                "no_of_users" => (isset($d['no_of_users'])) ? $d['no_of_users'] : 0,
                "total_amount" => (isset($d['total_amount'])) ? number_format($d['total_amount'],2) : 0,
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => count($count),
            "iTotalDisplayRecords" => count($count),
            "aaData" => $result
        );
        return json_encode($response);
    }

    public function getPerUser($request){
        $draw = $request->draw;
        $start = $request->start;
        $row_per_page = $request->length;
        $sortableColumns = [
            0 => 'id',
            1 => 'full_name',
            2 => 'business_name',
            3=>'subscription'
        ];
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');

        $orderColumn = $sortableColumns[$orderColumnIndex] ?? 'id';
        $result =[];
        $isRevenue = isset($request->revenue) ? true : false;


        $date = getDuration($request->duration)->startOfDay(); // Carbon (UTC)
        $date = $date->toDateTimeString(); // Carbon (UTC)
        $to_date = \Carbon\Carbon::now()->endOfDay();

        // if duration is "yesterday", both should refer to that day only
        if ($request->duration === 'yesterday') {
            $to_date = getDuration($request->duration)->endOfDay();
        }
        $to_date   = $to_date->toDateTimeString();


        
        $query = '';
        $search = $request->searching;
        $package = $request->package;
   

        $query = AppUser::select("app_users.*","user_subscriptions.subscription_id","subscriptions.subscription",
            \DB::raw('COALESCE(SUM(subscribed_users.total_amount),0) AS total_amount '))
            ->join('user_subscriptions', 'app_users.id', '=', 'user_subscriptions.user_id')
            ->join('subscribed_users', 'user_subscriptions.user_id', '=', 'subscribed_users.user_id')
            ->join('subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')
            ->where('app_users.is_subscribed',1)
            ->where('app_users.package_taken',1)
            ->whereBetween('user_subscriptions.created_at', [$date, $to_date])
            ->where('app_users.free_package',0)
            ->where('app_users.deleted',0);

        $isRevenue ? $query->orderBy('app_users.id','DESC') : $query->orderBy($orderColumn, $orderDir);
            
        if($request->limit != "0"){
            $query->limit($request->limit);
        }

        if($search != ""){
            $query->where(function($q) use ($search){
                $q->where('full_name', 'like', "%$search%")
                ->orWhere('business_name', 'like', "%$search%");
            });
        }

        if(isset($package) && $package != ""){
            $query->where('subscriptions.id', '=', $package);
        }
                $count = $query->groupBy('app_users.id','user_subscriptions.subscription_id')->get();
        $data = $query->skip($start)->take($row_per_page)->groupBy('app_users.id','user_subscriptions.subscription_id')->get();




        // $data = AppUser::select("app_users.*","user_subscriptions.subscription_id","subscriptions.subscription",\DB::raw('COALESCE(SUM(subscribed_users.total_amount),0) AS total_amount '))
                //     ->join('user_subscriptions', 'app_users.id', '=', 'user_subscriptions.user_id')
                //     ->join('subscribed_users', 'user_subscriptions.user_id', '=', 'subscribed_users.user_id')
                //     ->join('subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')
                //     ->where('app_users.is_subscribed',1)
                //     ->where('app_users.package_taken',1)
                //     ->whereBetween('user_subscriptions.created_at', [$date, $to_date])
                //     ->where('app_users.free_package',0)
                //     ->orderBy($orderColumn, $orderDir);

                //  $query = $data->where('app_users.deleted',0);

                //  if($request->limit !="0"){
                //     $query = $data->limit($request->limit);
                //  }

                // if($search !=""){
                //     // $query = $data->where('full_name', 'like', '%' . $search . '%')->orWhere('business_name', 'like', '%' . $search . '%');
                //     $query = $data->where('full_name', 'like', '%' . $search . '%')->orWhere('business_name', 'like', '%' . $search . '%');
                // }

                // if(isset($package) && $package !="") {
                //     $query = $data->where('subscriptions.id','=',$package);
                //  }

                // $count =  $query->groupBy('app_users.id','user_subscriptions.subscription_id')
                //  ->orderBy('app_users.id','DESC')
                //  ->get()->toArray();

                //  $data = $query->skip($start)->take($row_per_page)->groupBy('app_users.id','user_subscriptions.subscription_id')
                //  ->orderBy('app_users.id','DESC')
                //  ->get()->toArray();

        foreach($data as $d){
             $result[] =array(
                "id" => "<span>".$d['id']."</span>",
                "subscriber" => "<span>".$d['full_name']."</span>",
                "organization" => "<span>".$d['business_name']."</span>",
                "package" => $d['subscription'],
                "bill_till_date" => $d['total_amount'],
            );
        }


        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => count($count),
            "iTotalDisplayRecords" => count($count),
            "aaData" => $result
        );

        return $response;
    }


    public function getPerUserChart($request){
      
        $date = getDuration($request->duration)->startOfDay(); // Carbon (UTC)
        $date = $date->toDateTimeString(); // Carbon (UTC)
        $to_date = \Carbon\Carbon::now()->endOfDay();
        if ($request->duration === 'yesterday') {
            $to_date = getDuration($request->duration)->endOfDay();
        }
        // $date = date('Y-m-d',strtotime(getDuration($request->duration)));
        // $to_date =date('Y-m-d',strtotime(Carbon::now()));
        $result =[];
        $data = AppUser::select("app_users.*","user_subscriptions.subscription_id","subscriptions.subscription",\DB::raw('COALESCE(SUM(subscribed_users.total_amount),0) AS total_amount '))
        ->join('user_subscriptions', 'app_users.id', '=', 'user_subscriptions.user_id')
        ->join('subscribed_users', 'user_subscriptions.user_id', '=', 'subscribed_users.user_id')
        ->join('subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')
        ->whereBetween('user_subscriptions.created_at', [$date, $to_date])
        ->where('app_users.is_subscribed',1)
        ->where('app_users.package_taken',1)
        ->where('app_users.free_package',0)
        ->where('app_users.deleted',0)
        ->groupBy('app_users.id','user_subscriptions.subscription_id')
        ->orderBy('app_users.id','DESC')
        ->limit($request->limit)
        ->get()->toArray();

        foreach($data as $d){
             $result[] =array(
                "name" => $d['full_name'],
                "y" => $d['total_amount'],
            );
        }

        return json_encode($result);
    }

    public function perSubscriptions($request){
        $draw = $request->draw;
        $row = $request->start;
        $start = $request->start;
        $row_per_page = $request->length;
        // $search = $request->search ;
        // Case 1: search is array with 'value'
        $search = is_array($request->search) ? ($request->search['value'] ?? '') : ($request->search ?? '');
        $isRevenue = isset($request->revenue) ? true : false;

        $result =[];
        $sortableColumns = [
            0 => 'id',
            1 => 'subscription',
            2 => 'no_of_users',
            3=>'total_amount'
        ];
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');

        $orderColumn = $sortableColumns[$orderColumnIndex] ?? 'id';
        
        $date = date('Y-m-d',strtotime(getDuration($request->duration)));
        
        $query = $this->select(
            'subscriptions.id',
            'subscriptions.subscription',
            \DB::raw("COUNT(CASE WHEN DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.subscription_id END ) AS `no`"),
            \DB::raw("COALESCE(SUM(CASE WHEN DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.no_of_users END),0) AS no_of_users"),
            \DB::raw("COALESCE(SUM(CASE WHEN DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.total_amount END),0) AS total_amount")
        )
            ->join('user_subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('subscriptions.subscription', 'LIKE', "%{$search}%");
            })
            ->groupBy('subscriptions.subscription');

        if (!$isRevenue) {
            $query->orderBy($orderColumn, $orderDir);
        }

        if($request->limit != "0"){
            $query->limit($request->limit);
        }
        $count = $query->get()->toArray();
        $data = $query->skip($start)->take($row_per_page)->get()->toArray();
                

            // $data = $this->select('subscriptions.id','subscriptions.subscription',\DB::raw("COUNT(CASE WHEN DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.subscription_id END ) AS `no`"),\DB::raw("COALESCE(SUM(CASE WHEN  DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.no_of_users END),0) AS no_of_users"),\DB::raw("COALESCE(SUM(CASE WHEN DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.total_amount END),0) AS total_amount"))->join('user_subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')->groupBy('subscriptions.subscription')->get()->toArray();

        // $data = $this->select('subscriptions.subscription',\DB::raw("COUNT(user_subscriptions.subscription_id) AS `no`"),\DB::raw("COALESCE(SUM(user_subscriptions.no_of_users),0) AS no_of_users"),\DB::raw("COALESCE(SUM(user_subscriptions.total_amount),0) AS total_amount"))->join('user_subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')->limit($request->limit)->groupBy('subscriptions.subscription')->get()->toArray();
        foreach($data as $d){
             $result[] =array(
                "id" => "<span>".$d['id']."</span>",
                "subscription" => "<span>".$d['subscription']."</span>",
                "no_of_user" => (isset($d['no_of_users'])) ? $d['no_of_users'] : 0,
                "total_amount" => (isset($d['total_amount'])) ? $d['total_amount'] : 0,
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => count($count),
            "iTotalDisplayRecords" => count($count),
            "aaData" => $result
        );
        return json_encode($response);
    }

    public function perSubscriptionChart($request){
        $draw = $request->draw;
        $row = $request->start;
        $result =[];
        $date = date('Y-m-d',strtotime(getDuration($request->duration)));
        $data = $this->select('subscriptions.subscription',\DB::raw("COUNT(CASE WHEN DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.subscription_id END ) AS `no`"),\DB::raw("COALESCE(SUM(CASE WHEN  DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.no_of_users END),0) AS no_of_users"),\DB::raw("COALESCE(SUM(CASE WHEN DATE(user_subscriptions.created_at) >= '$date' THEN user_subscriptions.total_amount END),0) AS total_amount"))->join('user_subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')->groupBy('subscriptions.subscription')->limit($request->limit)->get()->toArray();
        // $data = $this->select('subscriptions.subscription',\DB::raw("COUNT(user_subscriptions.subscription_id) AS `no`"),\DB::raw("COALESCE(SUM(user_subscriptions.no_of_users),0) AS no_of_users"),\DB::raw("COALESCE(SUM(user_subscriptions.total_amount),0) AS total_amount"))->join('user_subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')->limit($request->limit)->groupBy('subscriptions.subscription')->get()->toArray();

        foreach($data as $d){
             $result[] =array(
                "name" => $d['subscription'],
                // "no_of_user" => (isset($d['no_of_users'])) ? $d['no_of_users'] : 0,
                "y" => (isset($d['total_amount'])) ? $d['total_amount'] : 0,
            );
        }
        return json_encode($result);
    }

    public function getAbandoned($request){
         $draw = $request->draw;
        $start = $request->start;
        $row_per_page = $request->length;
        $result =[];
        $query='';
        $search = $request->searching;
        $package = $request->package;
        $search_by_date = $request->date;
        $sortableColumns = [
            0 => 'id',
            1 => 'full_name',
            2 => 'business_name',
            3=>'subscription'
        ];
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');

        $isRevenue = isset($request->revenue) ? true : false;

        $orderColumn = $sortableColumns[$orderColumnIndex] ?? 'id';
        
        // $date = date('Y-m-d',strtotime(getDuration($request->duration)));
        // $to_date =date('Y-m-d',strtotime(Carbon::now()));
        $date = getDuration($request->duration)->startOfDay(); // Carbon (UTC)
        $date = $date->toDateTimeString(); // Carbon (UTC)
        $to_date = \Carbon\Carbon::now()->endOfDay();

        $query = AppUser::select('app_users.*','user_subscriptions.subscription_id','subscriptions.subscription',\DB::raw("COALESCE(SUM(subscribed_users.total_amount),0) AS total_amount"),\DB::raw("DATE(user_subscriptions.expiry_date) AS expiry_date"))
        ->join('user_subscriptions', 'app_users.id', '=', 'user_subscriptions.user_id')
        ->join('subscribed_users', 'user_subscriptions.user_id', '=', 'subscribed_users.user_id')
        ->join('subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')
        ->where('app_users.is_subscribed',1)
        ->where('app_users.package_taken',1)
        ->where('app_users.free_package',0)
        ->where('app_users.deleted',0)
        ->whereBetween('user_subscriptions.created_at', [$date, $to_date]);
        $isRevenue ?  $query->orderBy('user_subscriptions.expiry_date','DESC') : $query->orderBy($orderColumn, $orderDir);

        //->whereRaw('DATE(user_subscriptions.expiry_date) < DATE(NOW())');

         if($search_by_date !==""){
            $query->whereDate('user_subscriptions.expiry_date','=',$search_by_date);
        }else{
            $query->whereRaw('DATE(user_subscriptions.expiry_date) < DATE(NOW())');
        }

        // if($request->limit !=="0"){
        //     $query->limit($request->limit);
        // }

        $query->groupBy('app_users.id','user_subscriptions.subscription_id','user_subscriptions.expiry_date');

        
        if (!empty($search)) {
    $query->where(function($q) use ($search) {
        $q->where('app_users.full_name', 'like', "%{$search}%")
          ->orWhere('app_users.business_name', 'like', "%{$search}%");
    });
}


        if(isset($package) && $package !="") {
            $query->where('subscriptions.id','=',$package);
         }

        $count = $query->orderBy('user_subscriptions.expiry_date','DESC')->get()->toArray();

        $data = $query->skip($start)->take($row_per_page)->orderBy('user_subscriptions.expiry_date','DESC')->get()->toArray();

        foreach($data as $d){
             $result[] =array(
                "id" => "<span>".$d['id']."</span>",
                "subscriber" => "<span>".$d['full_name']."</span>",
                "organization" => "<span>".$d['business_name']."</span>",
                "package" => $d['subscription'],
                "expiry" => $d['expiry_date'],
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => count($count),
            "iTotalDisplayRecords" => count($count),
            "aaData" => $result
        );
        return json_encode($response);
    }

    public function getAbandonedChart($request){
        $result =[];
        $date = date('Y-m-d',strtotime(getDuration($request->duration)));
        $to_date =date('Y-m-d',strtotime(Carbon::now()));
        $data = AppUser::select('app_users.*','user_subscriptions.subscription_id','subscriptions.subscription',\DB::raw("COALESCE(SUM(subscribed_users.total_amount),0) AS total_amount"),\DB::raw("DATE(user_subscriptions.expiry_date) AS expiry_date"))
        ->join('user_subscriptions', 'app_users.id', '=', 'user_subscriptions.user_id')
        ->join('subscribed_users', 'user_subscriptions.user_id', '=', 'subscribed_users.user_id')
        ->join('subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')
        ->where('app_users.is_subscribed',1)
        ->where('app_users.package_taken',1)
        ->where('app_users.free_package',0)
        ->whereBetween('user_subscriptions.created_at', [$date, $to_date])
        ->where('app_users.deleted',0)
        ->whereRaw('DATE(user_subscriptions.expiry_date) < DATE(NOW())')
        ->groupBy('app_users.id','user_subscriptions.subscription_id','user_subscriptions.expiry_date')
        ->orderBy('user_subscriptions.expiry_date','DESC')
        ->limit($request->limit)->get()->toArray();
        foreach($data as $d) {
             $result[] = array(
                "name" => $d['full_name'],
                "y" => $d['total_amount'],
            );
        }
        return json_encode($result);
    }

    public function get_details($user_id){
        return UserSubscription::select('subscription_id','start_date','expiry_date')->where('user_id',$user_id)->get()->first();
    }

    public function get_subscription($subscription_id){
        return $this->where('id',$subscription_id)->get()->first();
    }


}
