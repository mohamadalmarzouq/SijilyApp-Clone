<?php

namespace App\Http\Controllers\Panel;

use App\CancelledSubscription;
use App\Http\Requests\AppUser\UpdateAppUser;
use App\Events\CreateUser;
use App\Models\AppUser;
use App\Models\UserRole;
use App\Models\Status;
use App\Models\Country;
use App\Models\SubCategory;
use App\Models\Industry;
use App\Models\SubscribedUser;
use App\Models\Type;
use App\Http\Validation\RulesAppUser as Rules;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Events\UserSignUp;
use App\Libraries\TapPayment;
use App\Mail\SendCredentialsMail;
use App\Models\UserSubscription;
use App\Models\Subscription;
use Illuminate\Support\Facades\Mail;
use App\Models\UserSubscription as ModelsUserSubscription;

use Illuminate\Support\Facades\Storage;

class AppUserController extends Controller
{
    public function __construct()
    {
        $this->tap_payment = new TapPayment();
        $this->primary_model = new AppUser();
        $this->status_model = new Status();
        $this->country_model = new Country();
        $this->status_model = new Status();
        $this->sub_cat_model = new SubCategory();
        $this->industry_model = new Industry();
        $this->role_model = new UserRole();
        $this->type_model = new Type();
        $this->subscribed_user = new SubscribedUser();
        $this->user_subscription = new UserSubscription();
        $this->subscription = new Subscription();
        $this->cancelsubscription = new CancelledSubscription();
        $this->dataAssign['module'] = 'app_users';
        $this->dataAssign['actions'] = ['cancel', 'child', 'view', 'add', 'edit', 'delete', 'reset_package'];
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxListing';
        $this->dataAssign['ordering_column'] = $this->primary_model->orderingColumn();
        $this->dataAssign['ordering'] = true;
        $this->dataAssign['search_col'] = $this->primary_model->FindByColumns();

        $this->dataAssign['id'] = 0;
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable();
        // $this->dataAssign['sort_colum'] = $this->primary_model->sortArray();
    }

    public function add()
    {
        $this->dataAssign['countries'] = $this->country_model->get()->toArray();
        $this->dataAssign['industries'] = $this->industry_model->get()->toArray();
        $path = public_path('assets/data/countryCode.json');
        $json = file_get_contents($path);
        $this->dataAssign['countryCode'] = json_decode($json, true); // Decode as associative array

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function show()
    {
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function view($id)
    {
        $this->dataAssign['roles'] = $this->role_model->get()->toArray();
        $this->dataAssign['industries'] = $this->industry_model->get()->toArray();
        $this->dataAssign['statuses'] = $this->status_model->getStatusByModule($this->dataAssign['module']);
        $this->dataAssign['data'] = $this->primary_model->findorFail($id);
        $this->dataAssign['countries'] = $this->country_model->get()->toArray();
        if ($this->dataAssign['data']->is_child == 1) {
            return redirect('/app_users');
        }
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function resetPackage($id)
    {
        $this->primary_model->where('id', $id)->update(['is_subscribed' => 0, 'free_package' => 0, 'package_taken' => 0]);
        return redirect()->back();
    }

    public function add_child($id)
    {
        // dd("hello");
        $this->dataAssign['id'] = $id;
        $this->dataAssign['roles'] = $this->role_model->get()->toArray();

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function childUser($id)
    {
        $data = $this->primary_model->with(['status', 'Subscription.subscription'])->withCount('userCount')->where("id", $id)->first()->toArray();
        $this->dataAssign['id'] = $id;
        $this->dataAssign['parent_user'] = $data;
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable("user");
        $this->dataAssign['module_name'] = "users";
        $this->dataAssign['sort_colum'] = $this->primary_model->sortArray("user");
        $this->dataAssign['search_col'] = $this->primary_model->FindByColumns(['is_child' => 1]);
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxChildListing';
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function edit($id)
    {
        $this->dataAssign['industries'] = $this->industry_model->get()->toArray();
        $this->dataAssign['roles'] = $this->role_model->get()->toArray();
        $this->dataAssign['statuses'] = $this->status_model->getStatusByModule($this->dataAssign['module']);
        $this->dataAssign['data'] = $this->primary_model->findorFail($id);
        $this->dataAssign['countries'] = $this->country_model->get()->toArray();
        $path = public_path('assets/data/countryCode.json');
        $json = file_get_contents($path);
        $this->dataAssign['countryCode'] = json_decode($json, true);
        if ($this->dataAssign['data']->is_child == 1) {
            return redirect('/app_users');
        }
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function users()
    {
        $this->dataAssign['sort_colum'] = $this->primary_model->sortArray("user");
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable("user");
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxUserListing';
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function cancelUser($id, $subscription_id)
    {
        $data = $this->primary_model->with('latestsubscribeduser')->where("id", $id)->first()->toArray();

        $child_users = $this->primary_model->getChildUsers($id);

        if (!empty($child_users)) {
            foreach ($child_users as $user) {
                $this->primary_model->where('id', $user['id'])->update(["deleted" => 1, "permanent_delete" => 1]);
            }
        }

        $relation = $data['latestsubscribeduser'];
        if (!empty($relation)) {
            $amount = $relation[0]['total_amount'];
            $subscriptionId = $subscription_id;
            $cancelledBy = auth()->user()->id;
            $userId = $id;
            // $this->user_subscription::where('user_id', $userId)->delete();
                ModelsUserSubscription::where('user_id', $userId)->update(['no_of_users' => 0]);

            $this->primary_model->where('id', $id)->update(["free_package" => 0, "package_taken" => 0, "is_subscribed" => 0]);
            $this->primary_model->setAccessToken($id);
            $this->cancelsubscription->user_id = $userId;
            $this->cancelsubscription->cancelled_by = $cancelledBy;
            $this->cancelsubscription->amount = $amount;
            $this->cancelsubscription->subscription_id = $subscriptionId;
            $this->cancelsubscription->save();
            return redirect()->back()->with('message', 'Subscription cancelled successfully');
        } else {
            return redirect()->back()->with('failed_message', 'Subscription cancellation failed');
        }
    }

    public function store(Request $request)
    {
        // dd("hello");
        if (isset($request->country)) {
            $get_country = $this->country_model->get_country($request->country);
            $request->merge(['country_name' => $get_country]);
        }

        if (isset($request->industry_type)) {
            $get_industry = $this->industry_model->get_industry($request->industry_type);
            $request->merge(['industry_name' => $get_industry]);
        }

        $validation = Validator::make($request->all(), Rules::panelUserSignUp());

        if ($validation->fails()) {
            return sendErrorToClient($validation->messages()->all());
        }


        try {
            //$status_id = $this->status_model->getStatusID($this->module, 'block');

            $postdata = [
                'first_name' => $request->full_name,
                'last_name' => $request->full_name,
                'email' => $request->email,
                'phone' => [
                    "country_code" => $request->country_code,
                    "number" => $request->contact
                ]
            ];

            $customers = $this->tap_payment->PostRequest('customers', $postdata);
            $customers = json_decode($customers);
            if (isset($customers->id)) {
                $request->merge(['customer_id' => $customers->id]);
            }

            $code = $request->password;
            $request->merge(['status_id' => 1, 'password' => Hash::make($request->password)]);
            $request->merge(["year" => date('Y'), "is_verified" => 1]);
            $user = $this->primary_model->create($request->only($this->primary_model->getFillable()));
            $this->primary_model->setAccessToken($user->id);
            $request->merge([
                "user_id" => $user->id,
                "no_of_users" => 0,
                "total_user_amount" => 0,
                "total_amount" => 0,
                "status" => 1,
                "subscription_id" => 1,
                "start_date" => date("Y-m-d h:i:s"),
                "expiry_date" => date("Y-m-d h:i:s", strtotime('+2 weeks')),
            ]);

            $data = [
                "user_id" => $user->id,
                "no_of_users" => 0,
                "total_user_amount" => 0,
                "total_amount" => 0,
                "status" => 1,
            ];
            // $this->subscribed_user->create($data);

            // $this->user_subscription->create($request->only($this->user_subscription->getFillable()));

            // $this->primary_model->where('id', $user->id)->update(['free_package' => 1, "package_taken" => 1]);

            // dd($code);
            Mail::to($user)->send(new SendCredentialsMail($user, $code));

            //event(new UserSignUp($user));

            //set sub category
            $this->sub_cat_model->store($request->merge([
                'title' => 'Services to customers',
                'slug' => 'services_to_customers',
                'module' => 'sales',
                'user_id' => $user->id,
            ]));

            $this->sub_cat_model->store($request->merge([
                'title' => 'Sales of goods',
                'slug' => 'sales_of_goods',
                'module' => 'sales',
                'user_id' => $user->id,
            ]));

            $this->type_model->store($request->merge([
                'title' => 'Rent',
                'slug' => 'rent',
                'module' => 'expenses',
                'type' => 'fixed_expense',
                'type_id' => 6,
                'user_id' => $user->id,
            ]));

            $this->type_model->store($request->merge([
                'title' => 'Salaries',
                'slug' => 'salaries',
                'module' => 'expenses',
                'type' => 'fixed_expense',
                'type_id' => 6,
                'user_id' => $user->id,
            ]));


            $this->type_model->store($request->merge([
                'title' => 'Utility Expenses',
                'slug' => 'utility_expenses',
                'module' => 'expenses',
                'type' => 'fixed_expense',
                'type_id' => 6,
                'user_id' => $user->id,
            ]));

            $this->type_model->store($request->merge([
                'title' => 'Other Expenses',
                'slug' => 'other_expenses',
                'module' => 'expenses',
                'type' => 'fixed_expense',
                'type_id' => 6,
                'user_id' => $user->id,
            ]));

            $this->type_model->store($request->merge([
                'title' => 'Cost of Inventory',
                'slug' => 'cost_of_inventory',
                'module' => 'expenses',
                'type' => 'variable_expense',
                'type_id' => 7,
                'user_id' => $user->id,
            ]));

            $this->type_model->store($request->merge([
                'title' => 'Bonuses and commission',
                'slug' => 'bonuses_and_commission',
                'module' => 'expenses',
                'type' => 'variable_expense',
                'type_id' => 7,
                'user_id' => $user->id,
            ]));

            $this->type_model->store($request->merge([
                'title' => 'Salary Overtime',
                'slug' => 'salary_overtime',
                'module' => 'expenses',
                'type' => 'variable_expense',
                'type_id' => 7,
                'user_id' => $user->id,
            ]));

            $this->type_model->store($request->merge([
                'title' => 'Other Expenses',
                'slug' => 'other_expenses',
                'module' => 'expenses',
                'type' => 'variable_expense',
                'type_id' => 7,
                'user_id' => $user->id,
            ]));
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function createUser(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::createChildUser());

        if ($validation->fails()) {
            return sendErrorToClient($validation->messages()->all());
        }

        $password = $request->password;
        $code = $request->password;
        $hashed_random_password = Hash::make($password);

        $UserSubscription = SubscribedUser::where('user_id', $request->user_id)->sum('no_of_users');
        $total_user = $this->primary_model->where('parent', $request->user_id)->where('is_disabled', 0)->count();
        if (isset($UserSubscription->no_of_users) && $total_user == $UserSubscription) {
            return sendErrorToClient(trans("auth.can_not_add") . $UserSubscription . trans("auth.user"));
        }

        $checkUser = $this->primary_model->where('email', $request->email)->first();
        if ($request->has('recover')) {
            if ($request->recover !== 'yes') {
                return sendErrorToClient('This account unable to recover. If you want to recover please send recover option yes');
            }
        } else {
            if ($checkUser) {
                if ($checkUser->deleted === 1) {
                    if ($checkUser->parent == $request->user_id) {
                        return sendErrorToClient('This e-mail is already register but deleted do you want recover this account.');
                    } else {
                        return sendErrorToClient('This e-mail is already register with other company');
                    }
                } else {
                    return sendErrorToClient('E-mail is already been taken');
                }
            }
        }
        $request->merge(['parent' => $request->user_id]);
        $request->merge(['password' => $hashed_random_password]);
        $request->merge(['is_verified' => 1]);
        $request->merge(['status_id' => 1]);
        $request->merge(['is_child' => 1]);
        $request->merge(['user_name' => explode(" ", $request->full_name)[0]]);
        $user = $this->primary_model->create($request->only($this->primary_model->getFillable()));
        Mail::to($user)->send(new SendCredentialsMail($user, $code));

        if ($request->hasFile('image')) {
            $files = $request->file('image');
            // $total_file = count($files);
        }

        $requestArray = $request->all();

        if (isset($files)) {
            $this->primary_model->uploadSingleFile($files, $user->id, 'update');
        }

        $this->primary_model->setAccessToken($user->id);
        $user = $this->primary_model->getUser($user->id);
        $user->email_password = $password;
        event(new CreateUser($user));
        return makeClientHappy($user, trans('auth.success'));
    }

    public function update(Request $request)
    {

        
        if (isset($request->country)) {
            $get_country = $this->country_model->get_country($request->country);
            $request->merge(['country_name' => $get_country]);
        }

        if (isset($request->industry_type)) {
            $get_industry = $this->industry_model->get_industry($request->industry_type);
            $request->merge(['industry_name' => $get_industry]);
        }
        $validation = Validator::make($request->all(), Rules::panelEditUserSignUp($request));

        if ($validation->fails()) {
            return sendErrorToClient($validation->messages()->all());
        }



        $user = $this->primary_model->find($request->id);
        $user->update($request->only($this->primary_model->getFillable()));

        $blocked_status_id = $this->status_model->getStatusID($this->dataAssign['module'], 'block');
        if ($request->all()['status_id'] == $blocked_status_id) {
            $user->token()->delete();
        }

        return redirect($this->dataAssign['module']);
    }

    public function delete(Request $request)
    {
        $this->primary_model->where('id', $request->id)->where('deleted', 0)->update(['deleted' => 1, 'is_disabled' => 1]);
        return sendMsgToClient(trans('auth.deleted_successfully'));
    }

    protected function ajaxListing()
    {
        $data = $this->primary_model->with(['status', 'Subscription.subscription'])->withCount('userCount')->where('is_child', 0)->where("deleted", 0);
        $actions = $this->dataAssign['actions'];
        $module = $this->dataAssign['module'];
        $this->dataAssign['module_name'] = "subscriber";
        $this->dataAssign['search_col'] = $this->primary_model->FindByColumns(['is_child' => 0]);
        $this->dataAssign['sort_colum'] = $this->primary_model->sortArray("subscriber");
        $ordering = true;
        return $this->makeDataTable($data, $actions, $module, $ordering, $this->dataAssign['module_name']);
    }

    protected function ajaxUserListing()
    {

        $data = $this->primary_model->with(['status', 'role', 'parentUser'])->where('is_child', 1)->where("deleted", 0);
        $this->dataAssign['actions'] = ['view', 'add', 'edit', 'delete'];
        $actions = $this->dataAssign['actions'];
        $module = $this->dataAssign['module'];
        $this->dataAssign['module_name'] = "users";
        $this->dataAssign['sort_colum'] = $this->primary_model->sortArray("user");
        $ordering = true;
        return $this->makeDataTable($data, $actions, $module, $ordering, $this->dataAssign['module_name']);
    }

    protected function ajaxChildListing()
    {
        $id = request()->all()['id'];
        $this->dataAssign['id'] = $id;
        $data = $this->primary_model->with(['status', 'parentUser', 'role'])->where('app_users.parent', $id)->where("app_users.deleted", 0);
        $this->dataAssign['actions'] = ['view', 'add', 'edit', 'delete'];
        $actions = $this->dataAssign['actions'];
        $module = $this->dataAssign['module'];
        $this->dataAssign['module_name'] = "users";
        $this->dataAssign['sort_colum'] = $this->primary_model->sortArray("user");
        $ordering = true;
        return $this->makeDataTable($data, $actions, $module, $ordering, $this->dataAssign['module_name']);
    }
}
