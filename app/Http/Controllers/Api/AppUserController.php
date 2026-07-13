<?php

namespace App\Http\Controllers\Api;

use App\Events\UserSignUp;
use App\Libraries\TapPayment;
use App\Events\CreateUser;
use App\Http\Validation\RulesAppUser as Rules;
use App\Models\AppUser;
use App\Models\Status;
use App\Models\SubCategory;
use App\Models\UserSubscription;
use App\Models\Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use App\Models\SubscribedUser;

class AppUserController extends Controller
{
    function __construct()
    {
        $this->primary_model = new AppUser();
        $this->status_model = new Status();
        $this->sub_cat_model = new SubCategory();
        $this->type_model = new Type();
        $this->module = $this->primary_model->getTable();
        $this->tap_payment = new TapPayment();
    }

    public function signUp(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::userSignUp());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",", $validation->messages()->all()));
        }

        try {

            $status_id = $this->status_model->getStatusID($this->module, 'block');

            $postdata = [
                'first_name'=>$request->full_name,
                'last_name'=>$request->full_name,
                'email'=>$request->email,
                'phone' => [
                    "country_code" => $request->country_code,
                    "number" => $request->phone
                ]
            ];
            $customers = $this->tap_payment->PostRequest('customers',$postdata);
            $customers = json_decode($customers);
            if(isset($customers->id)){
                $request->merge(['customer_id' => $customers->id]);
            }
            $request->merge(['status_id' => $status_id, 'password' => Hash::make($request->password)]);
            $request->merge(["year" => date('Y')]);
            $user = $this->primary_model->create($request->only($this->primary_model->getFillable()));

            $this->primary_model->setAccessToken($user->id);


            event(new UserSignUp($user));

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

            $user = $this->primary_model->getUser($user->id);

            return makeClientHappy($user, trans('auth.success'));

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function verifyCode(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::verifyCode());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        try {
            return $this->primary_model->verifyUserCode($request->all());

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function resendCode(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::Resend());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        return $this->primary_model->resendCode($request->all());
    }

    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::userLogin(),["email.exists"=>"This email is not registered."]);
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        try {

            $response = $this->primary_model->login($request->all());

            if (is_array($response) && isset($response['mess'])) {
                return sendMsgToClient($response['mess']);
            }
            if ($response->status() == 200) {
                $request->merge(['user_id' => $response->original['data']->id]);
                $this->primary_model->where('id',$response->original['data']->id)->update(['last_login'=>date('Y-m-d h:i:s')]);
            }
            return $response;

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function forgotPassword(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::forgotPassword());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        try {
            return $this->primary_model->forgotPassword($request->all());
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function resetPassword(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::resetPassword());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        try {
            $response = $this->primary_model->resetPassword($request);

            // if ($response->status() == 200) {
            //     session()->put('activity_log_data', [
            //         'identifier' => 'password_changed',
            //         'subject_type' => $response->original['data'],
            //         'name' => 'full_name',
            //         'module' => $this->module,
            //     ]);
            // }

            return $response;

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function getUserData(Request $request)
    {
        try {
            return $this->primary_model->getUser($request->user_id);

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function updateProfile(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::updateProfile($request));

        if ($validation->fails()) {
            return sendErrorToClient(implode(",", $validation->messages()->all()));
        }

        try {

            if ($request->hasFile('file')) {
                $files = $request->file('file');
                // $total_file = count($files);
            }

            $requestArray = $request->all();

            if (isset($files)) {
                $this->primary_model->uploadSingleFile($files, $request->user_id, 'update');
            }

            $user = $this->primary_model->find($request->user_id);

            $user->update($request->only($this->primary_model->getFillable()));

            $user = $this->primary_model->getUser($user->id);
            $headers = apache_request_headers();
            app()->setLocale('ar');
            session()->put('activity_log_data', [
                'identifier' => 'profile_updated',
                'subject_type' => $user,
                'name' => 'full_name',
                'module' => $this->module,
                'data_ar' => trans('Logs.upload_profile') . " " . $user->full_name
            ]);
            $local = (isset($headers['Local'])) ? $headers['Local'] : 'en';
            app()->setLocale($local);

            return makeClientHappy($user, trans('auth.success'));
        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function changeLanguage(Request $request)
    {

        $validation = Validator::make($request->all(), Rules::changeLanguage($this->primary_model->languages()));

        if ($validation->fails()) {
            return sendErrorToClient('Selected language must be in ar or en');
        }

        try {

            $response = $this->primary_model->changeLanguage($request);

            if ($response->status() == 200) {
                session()->put('activity_log_data', [
                    'identifier' => 'language_changed',
                    'subject_type' => $response->original['data'],
                    'name' => 'language',
                    'module' => $this->module,
                ]);
            }

            return $response;

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function changePassword(Request $request)
    {

        $validation = Validator::make($request->all(), Rules::changePassword());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        try {

            $response = $this->primary_model->changePassword($request);

            if ($response->status() == 200) {
                session()->put('activity_log_data', [
                    'identifier' => 'password_changed',
                    'subject_type' => $response->original['data'],
                    'name' => 'full_name',
                    'module' => $this->module,
                ]);
            }

            return $response;

        } catch (\Exception $e) {
            return sendExpToClient($e);
        }
    }

    public function CheckEmailExist(Request $request)
    {

        $validation = Validator::make($request->all(), Rules::CheckEmail());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        $email = $this->primary_model->where("email", $request->email)->exists();
        if ($email) {
            return sendErrorToClient(trans("auth.already_exist"));
        } else {
            return sendMsgToClient(trans("auth.not_exist"));
        }
    }

    public function CheckBusinessExist(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::CheckBusiness());
        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        $email = $this->primary_model->where("business_name", $request->business_name)->exists();

        if ($email) {
            return sendErrorToClient(trans("auth.business_already_exist"));
        } else {
            return sendMsgToClient(trans("auth.not_exist"));
        }
    }

    public function signOut(Request $request)
    {

        $user = $this->primary_model->setAccessToken($request->user_id);
        return sendMsgToClient(trans("auth.logout"));

    }

    public function addSubCategory(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::storeSubCategory());

        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        $sub_cat = $this->sub_cat_model->store($request);

        return makeClientHappy($sub_cat, trans('auth.success'));

    }

    public function deleteSubCategory(Request $request)
    {

        $validation = Validator::make($request->all(), Rules::deleteSubCategory());

        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        $sub_cat = $this->sub_cat_model->where('id', $request->id)->delete();

        return sendMsgToClient(trans('auth.deleted'));

    }

    public function GetUserId()
    {
        $users = $this->primary_model->select('id')->whereNotIn('id', [56, 50, 64, 65])->get()->toArray();
        foreach ($users as $user) {

            $this->sub_cat_model->create([
                'title' => 'Services to customers',
                'slug' => 'services_to_customers',
                'module' => 'sales',
                'user_id' => $user['id'],
            ]);

            $this->sub_cat_model->create([
                'title' => 'Sales of goods',
                'slug' => 'sales_of_goods',
                'module' => 'sales',
                'user_id' => $user['id'],
            ]);

            $this->type_model->create([
                'title' => 'Rent',
                'slug' => 'rent',
                'module' => 'expenses',
                'type' => 'fixed_expense',
                'type_id' => 6,
                'user_id' => $user['id'],
            ]);

            $this->type_model->create([
                'title' => 'Salaries',
                'slug' => 'salaries',
                'module' => 'expenses',
                'type' => 'fixed_expense',
                'type_id' => 6,
                'user_id' => $user['id'],
            ]);


            $this->type_model->create([
                'title' => 'Utility Expenses',
                'slug' => 'utility_expenses',
                'module' => 'expenses',
                'type' => 'fixed_expense',
                'type_id' => 6,
                'user_id' => $user['id'],
            ]);

            $this->type_model->create([
                'title' => 'Other Expenses',
                'slug' => 'other_expenses',
                'module' => 'expenses',
                'type' => 'fixed_expense',
                'type_id' => 6,
                'user_id' => $user['id'],
            ]);

            $this->type_model->create([
                'title' => 'Cost of Inventory',
                'slug' => 'cost_of_inventory',
                'module' => 'expenses',
                'type' => 'variable_expense',
                'type_id' => 7,
                'user_id' => $user['id'],
            ]);

            $this->type_model->create([
                'title' => 'Bonuses and commission',
                'slug' => 'bonuses_and_commission',
                'module' => 'expenses',
                'type' => 'variable_expense',
                'type_id' => 7,
                'user_id' => $user['id'],
            ]);

            $this->type_model->create([
                'title' => 'Salary Overtime',
                'slug' => 'salary_overtime',
                'module' => 'expenses',
                'type' => 'variable_expense',
                'type_id' => 7,
                'user_id' => $user['id'],
            ]);

            $this->type_model->create([
                'title' => 'Other Expenses',
                'slug' => 'other_expenses',
                'module' => 'expenses',
                'type' => 'variable_expense',
                'type_id' => 7,
                'user_id' => $user['id'],
            ]);
        }
    }

    public function createUser(Request $request)
    {

        $validation = Validator::make($request->all(), Rules::createUser());

        if ($validation->fails()) {
            return sendErrorToClient(implode(",", $validation->messages()->all()));
        }

        $password = str_random(8);
        $hashed_random_password = Hash::make($password);

        $UserSubscription = SubscribedUser::where('user_id', $request->user_id)->sum('no_of_users');
        $total_user = $this->primary_model->where('parent', $request->user_id)->where('is_disabled', 0)->count();
        if (isset($UserSubscription->no_of_users) && $total_user == $UserSubscription) {
            return sendErrorToClient(trans("auth.can_not_add") . $UserSubscription . trans("auth.user"));
        }

        $checkUser = $this->primary_model->where('email', $request->email)->first();
        $getId = $checkUser['id'];
        $getEmail = time()."_".$checkUser['email'];
        if($checkUser['permanent_delete']==1){
            $this->primary_model->where("id",$getId)->update(["email"=>$getEmail]);
            $request->merge(['role_id' => $checkUser['role_id']]);
        }else{
            if($request->has('recover')){
                if($request->recover !== 'yes'){
                    return sendErrorToClient('This account unable to recover. If you want to recover please send recover option yes');
                }
            }else{
                if($checkUser){
                    if($checkUser->deleted === 1){
                        if($checkUser->parent == $request->user_id){
                            return sendErrorToClient('This e-mail is already register but deleted do you want recover this account.');
                        }else{
                            return sendErrorToClient('This e-mail is already register with other company');
                        }
                    }else{
                        return sendErrorToClient('E-mail is already been taken');
                    }
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

    public function UserListing(Request $request)
    {
        $user = $this->primary_model->select('id', 'parent','role_id', 'full_name', 'user_name', 'email', 'image', 'is_disabled')->with(['role'])->where('parent', $request->user_id)->where("is_child", 1)->where('deleted', 0)->get()->toArray();

        $UserSubscription = UserSubscription::where('user_id', $request->user_id)->get()->first();
        $total_no_of_users = (isset($UserSubscription->no_of_users)) ? $UserSubscription->no_of_users : 0;
        //get total amount
        $remainning =$total_no_of_users - ($this->primary_model->where('parent', $request->user_id)->where('deleted', 0)->count());

        $registered_users = $this->primary_model->where('parent', $request->user_id)->where('is_disabled', 0)->where('deleted', 0)->count();
        $info['total'] = $total_no_of_users;
        $info['registered'] = $registered_users;
        // $info['remaining'] = abs($total_no_of_users - $registered_users);
        $info['remaining'] = abs($remainning);

        return makeClientHappy($user, trans('auth.success'), 'info', $info);
    }

    public function deleteUser(Request $request)
    {
        $delete = $this->primary_model->where('is_child', 1)->where('id', $request->id)->where('deleted', 0)->update(['deleted' => 1, 'is_disabled' => 1]);
        // if ($delete)
        //     UserSubscription::where('user_id', $request->user_id)->increment('no_of_users');

        return sendMsgToClient(trans('auth.deleted_successfully'));
    }


    public function UserAction(Request $request)
    {
        $user = $this->primary_model->where("id", $request->id)->update(['is_disabled' => $request->disabled]);
        $this->primary_model->setAccessToken($request->id);
        return makeClientHappy($user, trans('auth.success'));
    }

    public function GetUserInfo(Request $request)
    {
        $userObj = $this->primary_model->with(['token'])->where('id', $request->user_id)->first();
        if ($userObj->parent !== 0) {
            $user = $this->primary_model->getUser($userObj->id, ['id','role_id', 'parent', 'full_name', 'user_name', 'email']);
            $user->parent_info = $this->primary_model->getParentUser($user->parent);
            $user_id = $user->parent;
        } else {
            $user = $this->primary_model->getUser($userObj->id);
            $user_id = $user->id;
        }

//        $user = $this->primary_model->with(['token','status', 'subCategories', 'subscriptions'])->where("id",$request->user_id)->get()->toArray();
        return makeClientHappy($user, trans('auth.success'));
    }

    public function getUserRecordedBy(Request $request)
    {
        $parent_id = getParentId('app_users', 'id', $request->user_id);

        if ($parent_id != 0) {
            $user_id = $parent_id;
        } else {
            $user_id = $request->user_id;
        }

        $request->merge(['user_id' => $user_id]);

        $parent_users = $this->primary_model->select('id', 'full_name')->where('id', $request->user_id)->where('is_disabled', 0)->get()->toArray();
        $child_users = $this->primary_model->select('id', 'full_name')->where('parent', $request->user_id)->where('is_disabled', 0)->get()->toArray();
        $user = array_merge($parent_users, $child_users);
        return makeClientHappy($user, trans('auth.success'));
    }
}

