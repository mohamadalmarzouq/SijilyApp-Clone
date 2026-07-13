<?php

namespace App\Models;

use App\Events\UserSignUp;
use App\Events\ForgotPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\AccessToken;
use App\Models\UserSubscription;
use App\Models\UserRole;
use App\Models\Industry;
use Carbon\Carbon;
use App\Models\Subscription;
use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoin;
class AppUser extends Model
{
    use EloquentJoin;
    protected $useTableAlias = true;
    protected $appendRelationsCount = false;
    protected $leftJoin = true;
    protected $module_name = 'app_users';
    protected $aggregateMethod = 'MAX';
    protected $fillable = ['full_name','role_id', 'parent', 'user_name', 'email', 'password', 'business_name', 'industry_type', 'industry_name',
        'address', 'postal_code', 'city', 'country', 'country_name', 'accounting_method', 'company_year_end_date', 'reset_token',
        'status_id', 'is_verified', 'device_token', 'package_taken', 'contact', 'language', 'image', 'other_type', 'currency', 'is_child','default_card_id','customer_id','is_recur','country_code','phone','payment_agreement_id'];

    protected $hidden = [
        'password',
    ];

    public function getModuleName(){
            return $this->module_name.".";
    }

    public function getColumnsForDataTable($columns = "subscriber")
    {
        switch($columns){
             case 'subscriber':
                return json_encode([
                    ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
                    ['data' => 'full_name', 'name' => 'full_name', 'title' => 'Subscriber'],
                    ['data' => 'business_name', 'name' => 'business_name','title' => 'Organization'],
                    ['data' => 'email', 'name' => 'email', 'title' => 'Email Address'],
                    ['data' => 'is_subscribed', 'name' => 'is_subscribed','title' => 'Is Subscribed','searchable' => false,'visible' => false],

                    ['data' => 'user_count_count', 'name' => 'user_count_count', 'title' => 'No. Of Users','searchable' => false],
                    ['data' => 'subscription.start_date', 'name' => 'subscription.start_date','title' => 'Join Date', 'searchable' => true],
                    ['data' => 'subscription.expiry_date', 'name' => 'subscription.expiry_date','title' => 'Renewal Date','searchable' => true],
                    ['data' => 'subscription.subscription.expiry', 'name' => 'subscription.subscription.expiry','title' => 'Tenure','searchable' => true],
                    ['data' => 'subscription.subscription.subscription', 'name' => 'subscription.subscription.subscription','title' => 'Package'],
                    ['data' => 'status.title', 'name' => 'status.title', 'title' => 'Status'],
                    ['data' => 'action', 'name' => 'Action', 'searchable' => false],
                    ['data' => 'created_at', 'name' => 'created_at', 'visible' => false]
                ]);
             break;
            default:
                return json_encode([
                    ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
                    ['data' => 'full_name', 'name' => 'full_name', 'title' => 'User'],
                    ['data' => 'role.name', 'name' => 'role.name', 'title' => 'Role','searchable' => true],
                    ['data' => 'parent_user.business_name', 'name' => 'parent_user.business_name','title' => 'Organization','searchable' => true],
                    ['data' => 'email', 'name' => 'email', 'title' => 'Email Address'],
                    ['data' => 'last_login', 'name' => 'last_login', 'title' => 'Last Login'],
                    ['data' => 'status.title', 'name' => 'status.title', 'title' => 'Status'],
                    ['data' => 'action', 'name' => 'Action', 'searchable' => 'false'],
                ]);
        }
    }

    public function preventSearch(){
        return [
                'user_count_count',
                'subscription.start_date',
                'subscription.expiry_date',
                'subscription.subscription.expiry',
                'subscription.subscription.subscription',
                'status.title',
                'Action',
             ];
    }

    public function FindByColumns($value=[]){
        return $value;
    }

    public function orderArray($columns = "subscriber")
    {
        switch($columns){
            case 'subscriber':
                return [
                ['data' => 'id', 'name' => 'id', 'order' => true],
                ['data' => 'full_name', 'name' => 'full_name', 'order' => true,'search'=>true],
                ['data' => 'business_name', 'name' => 'business_name', 'order' => true,'search'=>true],
                ['data' => 'email', 'name' => 'email', 'order' => true,'search'=>true],
                ['data' => 'user_count_count', 'name' => 'user_count_count', 'order' => true],
                ['data' => 'subscription.start_date', 'name' => 'subscription.start_date','relation'=>true, 'order' => true,'relationship'=>['module_name'=>'user_subscriptions', 'column_name' => 'start_date']],
                ['data' => 'subscription.expiry_date', 'name' => 'subscription.expiry_date','relation'=>true, 'order' => true,'relationship'=>['module_name'=>'user_subscriptions', 'column_name' => 'expiry_date']],
                ['data' => 'subscription.subscription.expiry', 'name' => 'subscription.subscription.expiry','relation'=>true, 'order' => false,'relationship'=>['module_name'=>'user_subscriptions', 'column_name' => 'expiry']],
                ['data' => 'subscription.subscription.subscription', 'name' => 'subscription.subscription.subscription','relation'=>true, 'order' => true,'relationship'=>['module_name'=>'subscriptions', 'column_name' => 'subscription']],
                ['data' => 'status.title', 'name' => 'status.title','relation'=>true, 'order' => true,'relationship'=>['module_name'=>'statuses', 'column_name' => 'title']],
                ['data' => 'action', 'name' => 'Action', 'order' => false],
             ];
            break;
            default:
                 return [
                    ['data' => 'id', 'name' => 'id', 'order' => true],
                    ['data' => 'full_name', 'name' => 'full_name', 'order' => true,'search'=>true],
                    ['data' => 'role.name','search'=>true, 'name' => 'role.name', 'order' => true,'relation'=>true,'relationship'=>['module_name'=>'user_roles', 'column_name' => 'name']],
                    ['data' => 'parent_user.business_name', 'name' => 'parent_user.business_name','relation'=> true, 'order' => true,'relationship'=>['module_name'=>'app_users', 'column_name' => 'name'],'search'=>true],
                    ['data' => 'email', 'name' => 'email', 'order' => true,'search'=>true],
                    ['data' => 'last_login', 'name' => 'last_login', 'order' => true],
                    ['data' => 'status.title', 'name' => 'status.title', 'order' => true,'relation'=>true,'relationship'=>['module_name'=>'statuses', 'column_name' => 'title']],
                    ['data' => 'action', 'name' => 'Action', 'order' => false],
                 ];
        }
    }

    public function sortArray($columns = "subscriber"){

         switch($columns){
             case 'subscriber':
                return json_encode([
                        ['orderable' => true,'targets'=>0],
                        ['orderable' => true,'targets'=>1],
                        ['orderable' => true,'targets'=>2],
                        ['orderable' => true,'targets'=>3],
                        ['orderable' => true,'targets'=>4],
                        ['orderable' => true,'targets'=>5],
                        ['orderable' => true,'targets'=>6],
                        ['orderable' => false,'targets'=>7],
                        ['orderable' => false,'targets'=>8],
                        ['orderable' => false,'targets'=>9],
                        ['orderable' => false,'targets'=>10]
                ]);
            break;
            default :
                return json_encode([
                        ['orderable' => true,'targets'=>0],
                        ['orderable' => true,'targets'=>1],
                        ['orderable' => true,'targets'=>2],
                        ['orderable' => true,'targets'=>3],
                        ['orderable' => true,'targets'=>4],
                        ['orderable' => false,'targets'=>5],
                        ['orderable' => false,'targets'=>6],
                        ['orderable' => false,'targets'=>7]
                ]);
         }
    }


    public function getRetainUsers($request){
        $draw = $request->draw;
        $start = $request->start;
        $row_per_page = $request->length;
        $result =[];
        $search = $request->search;
        $search_by_date = $request->date;
        $date = getDuration($request->duration);
        $sortableColumns = [
            0 => 'id',
            1 => 'full_name',
            2 => 'business_name',
            3=>'created_at'
        ];
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');

        $orderColumn = $sortableColumns[$orderColumnIndex] ?? 'id';

        $query='';
        $data = $this->select("app_users.*")->with(['Subscription.subscription','sumAmount'])
        ->join('user_subscriptions', 'app_users.id', '=', 'user_subscriptions.user_id');
        //->skip($request->start)->take($request->length);
        $data->orderBy($orderColumn, $orderDir);

        if($request->limit !="0"){
            $query = $data->limit($request->limit);
        }

        $query= $data->where("app_users.free_package",0);

        if($search !=""){
            $query = $data->where('full_name', 'like', '%' . $search . '%');
        }

        if($search_by_date !=""){
            $query = $data->whereDate('app_users.created_at','=',$search_by_date );
        }else{
            $query = $data->where('app_users.created_at', '>=', $date);
        }

        // ->orderBy("app_users.id","DESC")
        $counter = $query->where("app_users.package_taken",1)->groupBy("id")->get()->toArray();

        // $query = $data->toSql();
        // ->orderBy("app_users.id","DESC")
       $data =  $query->skip($start)->take($row_per_page)->where("app_users.package_taken",1)->groupBy("id")->get()->toArray();
    //    dd($data);
        foreach($data as $key=> $d){
             $price = array();
             foreach($d['sum_amount'] as $pr){
                 $price[] = $pr['total_amount'];
             }
             $result[] =array(
                "id" => "<span>".$d['id']."</span>",
                "subscriber" => "<span>".$d['full_name']."</span>",
                "organization" => "<span>".$d['business_name']."</span>",
                "no_of_user" => (isset($d['subscription'])) ? $d['subscription']['no_of_users'] : 0,
                "subscribe_since" => date('Y-m-d',strtotime($d['created_at'])),
                "values"=> array_sum($price)
            );
        }
        $amount = array_column($result, 'values');
        // array_multisort($amount, SORT_DESC, $result);
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => count($counter),
            "iTotalDisplayRecords" =>count($counter),
            "aaData" => $result
        );

        return $response;
    }

    public function getNewSubscriptions($request){
        $result=[];
        $draw = $request->draw;
        $start = $request->start;
        $row_per_page = $request->length;
        $search = $request->searching;
        $search_by_date = $request->date;
        $date = getDuration($request->duration);

            // Define sortable columns (indexed like DataTables order[0][column])
    $sortableColumns = [
        0 => 'id',
        1 => 'full_name',
        2 => 'business_name',
        3=>'created_at'
    ];

    // Determine sorting
    $orderColumnIndex = $request->input('order.0.column');
    $orderDir = $request->input('order.0.dir', 'asc');

    $orderColumn = $sortableColumns[$orderColumnIndex] ?? 'id';


        $data = $this->with(['Subscription.subscription'])->where("parent",0)->where('package_taken','!=',0);

    // Apply sorting
    $data->orderBy($orderColumn, $orderDir);

        $query = '';

        if($request->limit !="0"){
            $query = $data->limit($request->limit);
        }

        if($search !=""){
            $query = $data->where('full_name', 'like', '%' . $search . '%');
        }

        if($search_by_date !=""){
            $query = $data->whereDate('created_at','=',$search_by_date );
        }else{
            $query = $data->where('created_at', '>=', $date);
        }

        // orderBy('id','DESC')->
        $counter = $query->get()->toArray();

        // $query = $data->toSql();
        // orderBy('id','DESC')->
        $query = $data->skip($start)->take($row_per_page)->get()->toArray();

         foreach($query as $d){
                $result[] = array(
                    "id" => "<span>".$d['id']."</span>",
                    "full_name" => "<span>".$d['full_name']."</span>",
                    "date" => date('Y-m-d',strtotime($d['created_at'])),
                    "business_name" => $d['business_name'],
                     "subscription" => isset($d['subscription']['subscription']) ? $d['subscription']['subscription']['subscription']:"",
                    "no_of_user" => isset($d['subscription']) ? $d['subscription']['no_of_users']:0
                );
        }
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => count($counter),
            "iTotalDisplayRecords" => count($counter),
            "aaData" => $result
        );

        return $response;
    }

    public function getRenewalUser($request){
        $draw = $request->draw;
        $start = $request->start;
        $row_per_page = $request->length;
        $search = $request->search;
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');
        $sortableColumns = [
            0 => 'full_name',
            1 => 'business_name',
        ];
        $orderColumn = $sortableColumns[$orderColumnIndex] ?? 'full_name';

        $result = [];
        $startDate = \Carbon\Carbon::today();
        $date = getExpirationDuration($request->duration);
        $query = '';
        $data = $this->select("app_users.*")->with(['Subscription.subscription'])
            ->join('user_subscriptions', 'app_users.id', '=', 'user_subscriptions.user_id');
        $data->orderBy($orderColumn, $orderDir);


        if (!empty($request->duration) && $request->duration != "years") {
            $data->whereBetween('user_subscriptions.expiry_date', [
                $startDate,
                $date
            ]);
        }
        // Case 2: no duration → expiry >= start date
        else {
            $data->where('user_subscriptions.expiry_date', '>=', $startDate);
        }

        if ($search != "") {
            $data->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                    ->orWhere('business_name', 'like', "%$search%");
            });
        }

        // Filter active subscriptions before pagination
        $data->whereHas('Subscription.subscription', function ($q) {
            $q->whereDate('expiry_date', '>', now()); // strictly greater than today
        });
        $counter  = $data->count();


        $results = $data->skip($start)->take($row_per_page)->get()->toArray();

        foreach ($results as $d) {

            $subscription = $d['subscription'] ?? null;
            $daysLeft = $subscription ? daysLeft(date('Y-m-d', strtotime($subscription['expiry_date']))) : 0;

            $result[] = [
                "subscriber"   => "<span>{$d['full_name']}</span>",
                "organization" => "<span>{$d['business_name']}</span>",
                "no_of_user"   => $subscription['no_of_users'] ?? 0,
                "days_left"    => $daysLeft + 1,
            ];
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $counter,
            "iTotalDisplayRecords" => $counter,
            "aaData" => $result
        );

        return $response;
    }
    public function orderingColumn()
    {
        return json_encode([['5', 'desc']]);
    }

    public function token()
    {
        return $this->hasOne(AccessToken::class, 'user_id', 'id');
    }

    public function Subscription()
    {
        return $this->hasOne(UserSubscription::class, 'user_id', 'id');
    }

    public function role()
    {
        return $this->hasOne(UserRole::class, 'id', 'role_id');
    }

    public function parentUser()
    {
        return $this->hasOne(AppUser::class, 'id', 'parent');
    }

    public function parent_user()
    {
        return $this->hasOne(AppUser::class, 'id', 'parent');
    }

    public function selectedSubscription()
    {
        return $this->hasOneThrough(UserSubscription::class,Subscription::class,'subscription_id','id');
    }

    public function Industries()
    {
        return $this->hasOne(Industry::class, 'id', 'industry_type');
    }

    public function packageUsers(){

    }

    public function getChildUsers($id){
        return $this->where("parent",$id)->get()->toArray();
    }

    public function userCount()
    {
       return $this->hasMany(AppUser::class,'parent','id')->where('is_child',1)->where('deleted',0);
    }

    public function sumAmount()
    {
       return $this->hasMany(UserSubscription::class,'user_id','id');
    }


    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id')->where('module', $this->getTable());
    }

    public function verifyUserCode($request)
    {
        $user = $this->getUser($request['user_id']);

        if (!empty($request['resend'])) {

            event(new UserSignUp($user));

            return makeClientHappy($user, trans("auth.code_has_been_send_to_mail"));
        }

        return $this->verifyUserEmail($user, $request['code']);
    }


    public function resendCode($request)
    {
        $user = $this->getUserByEmail($request['email']);
        event(new UserSignUp($user));
        return sendMsgToClient(trans("auth.code_has_been_send_to_mail"));
    }

    public function getUser($id, $columns = ['*'])
    {
        $data = $this->select($columns)->with(['status', 'subCategories', 'token', 'subscriptions.subscription','userRole.Permission'])->findOrFail($id);
        if(!empty($data->userRole) && $data->userRole !=null){
            foreach($data->userRole->permission as $key => $permission){
                    $ref_id = $permission->modules->module_ref_id;
                    $data->userRole->permission[$key]->module_ref_id = $ref_id;
            }
        }

        //translate subcategories
        $i = 0;
        foreach ($data->subCategories as $subCategory) {
            if ($subCategory->slug == 'services_to_customers')
                $data->subCategories[$i]->title = trans('categories.services_to_customers');
            else
                $data->subCategories[$i]->title = trans('categories.sales_of_goods');
            $i++;
        }
        if ($data->status)
            $data->status->title = trans('categories.status');

        return $data;

    }

    public function getParentUser($id)
    {
        return $this->with(['status', 'subCategories', 'token', 'subscriptions'])->findOrFail($id);
    }

    public function userRole(){
        return $this->belongsTo(UserRole::class,'role_id','id');
    }

    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function verifyUserEmail($user_data, $code)
    {
        $response = $code != $user_data->reset_token ? 0 : 1;

        if ($response) {

            $status_model = new Status();

            $user_data->is_verified = 1;

            $status_id = $status_model->getStatusID($this->getTable(), 'active');

            $user_data->status_id = $status_id;

            $user_data->reset_token = Null;

            $user_data->save();

            return makeClientHappy($user_data, trans('auth.success'));
        }

        return sendErrorToClient(trans("auth.invalid_token"));
    }

    public function login($request)
    {

        $user = $this->with(['token'])->where('email', $request['email'])->where('deleted',0)->first();

        if (empty($user)) {
            return sendErrorToClient(trans("auth.invalid_login"));
        }

        $status_model = new Status();


        $is_child = $this->where("is_child",1)->where('id',$user->id)->pluck('is_child')->first();

        $status_id = $status_model->getStatusID($this->getTable(), 'active');

        if (!Hash::check($request['password'], $user->password)) {
            $response = sendErrorToClient(trans("auth.invalid_password"));
        } elseif (!$user->is_verified) {
            $response = sendMsgErrorToClient(trans("auth.inactive_user"), $user);
        } elseif ($user->status_id != $status_id || $user->is_disabled == 1) {

            $response = sendErrorToClient(trans("auth.account_suspended"));//422
        }
        // else if (!$user->is_subscribed && $is_child == 0 ) {

        //     // $user->subscr
        //     $response = sendMsgErrorToClient(trans("auth.package_expired"), $user);
        // }
         else {

            $device_token = !empty($request->device_token) ? $request->device_token : 'no_token';
            $user->device_token = $device_token;
            $this->setAccessToken($user->id);
            $user->save();

            if ($user->parent !== 0) {
                $user = $this->getUser($user->id, ['id','role_id','image', 'parent', 'full_name', 'user_name', 'email']);
                $user->parent_info = $this->getParentUser($user->parent);
                $user_id = $user->parent;
            } else {
                $user = $this->getUser($user->id);
                $user_id = $user->id;
            }


            $subscription = UserSubscription::where("user_id", $user_id)->get()->first();
            if (!empty($subscription) && $is_child == 0) {
                $current_date = date('Y-m-d');
                $expire_date = date('Y-m-d', strtotime($subscription->expiry_date));
                if ($current_date >= $expire_date) {
                    $this->where('id',$user_id)->update(['is_subscribed'=>0,'free_package'=>0,'package_taken'=>0]);
                    $response = makeClientHappy($user, trans("auth.package_expired"));
                    return $response;
                }
            }

            if(!empty($user->userRole) && $user->userRole !=null){
                foreach($user->userRole->permission as $key => $permission){
                      $ref_id = $permission->modules->module_ref_id;
                      $user->userRole->permission[$key]->module_ref_id = $ref_id;
                }
            }


            $response = makeClientHappy($user, trans('auth.success'));
        }

        return $response;
    }

    public function setAccessToken($user_id)
    {
        $token = Str::random() . time();
        $exp_time = time() + (365 * 24 * 60 * 60);  // +1 Year

        $data = AccessToken::updateOrCreate(
            ['user_id' => $user_id],
            ['user_id' => $user_id, 'access_token' => $token, 'expiry_time' => $exp_time]
        );

        return $data;
    }

    public function forgotPassword($request)
    {
        $user = $this->where('email', $request['email'])->first();
        $data = $this->setAccessToken($user->id);
        $access_token=$data['access_token'];
        // $access_token = AccessToken::where('user_id', $user->id)->get()->first()->access_token;
        $token = '';
        if (!empty($user)) {
            event(new ForgotPassword($user));
            return pageResponse(["token" => ['access_token' => $access_token]], trans("auth.send_to_email"));
        }
        return sendErrorToClient('User Not Found.');
    }

    public function resetPassword($request)
    {

        $user_data = $this->getUser($request['user_id']);

        if (!empty($request['password'])) {

            $user_data->password = Hash::make($request['password']);

            $user_data->reset_token = null;

            $user_data->save();

            return sendMsgToClient(trans("auth.password_change"));
        }

        return sendErrorToClient(trans("auth.try_again"));
    }

    public function changeLanguage($request)
    {
        $user = $this->getUser($request['user_id']);

        $user->language = $request['language'];

        $user->save();

        return makeClientHappy($user, trans("auth.language_changed"));
    }

    public function languages()
    {
        return 'ar,en';
    }

    public function changePassword($request)
    {
        $user = $this->checkPassword($request['user_id'], $request['old_password']);

        if (!empty($user)) {
            $user->password = Hash::make($request['password']);

            $user->save();

            return makeClientHappy($user, trans("auth.password_change"));
        }

        return sendErrorToClient(trans("auth.old_password"));
    }

    public function checkPassword($user_id, $old_password)
    {
        $user_data = $this->find($user_id);

        if (Hash::check($old_password, $user_data->password))

            return $user_data;

        return NULL;
    }

    public function subscriptions()
    {
        return $this->hasOne(UserSubscription::class, 'user_id');
    }

    public function latestsubscribeduser()
    {
        return $this->hasMany(SubscribedUser::class, 'user_id')->orderBy('created_at', 'DESC')->skip(0)->take(1);
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'user_id');
    }

    public function uploadSingleFile($uploadFile, $id, $type)
    {

        $file_path = uploadCustomFile($uploadFile);

        if ($type == "update") {
            $this->where('id', $id)->update(['image' => $file_path]);
        } else {
            // $this->insert([
            //     'model_name'=> $model,
            //     'model_ref_id'=> $id,
            //     'source' => $file_path,
            // ]);
        }

    }

    public function getUserFiscalYear($user_id)
    {
        return $this->where('id', $user_id)->get()->first()->company_year_end_date;
    }


}
