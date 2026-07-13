<?php

namespace App\Http\Controllers\Panel;


use App\Models\User;
use App\Models\Role;
use App\Http\Validation\RulesUsers as Rules;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\ResetPassword;
use App\Models\AppUser;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->primary_model = new User();
        $this->role_model = new UserRole();
        $this->dataAssign['module'] = 'admin';
        $this->dataAssign['actions'] = ['view','add','edit','delete'];
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxListing';
        $this->dataAssign['ordering_column'] = $this->primary_model->orderingColumn();
        $this->dataAssign['ordering'] = true;
        $this->dataAssign['id'] = 0;
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable();
    }

    public function add()
    {
        $this->dataAssign['roles'] = $this->role_model->get()->toArray();
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function changePassword(){
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function change(Request $request){
            $validation = Validator::make($request->all(), Rules::updatePassword());

            if ($validation->fails()) {
                return sendErrorToClient($validation->messages()->all());
            }
            $current_user = Auth()->user();
            $email = $current_user['email'];
            $user = $this->primary_model->where('email', $email)->first();

            if (!Hash::check($request['old_password'], $user['password'])) {
                 return sendErrorToClient(trans("auth.invalid_password"));
            }
            $hashed_password =  Hash::make($request['password']);
            $this->primary_model->where("id",$user['id'])->update(["password"=>$hashed_password]);
    }

    public function show()
    {
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function view($id)
    {
        $this->dataAssign['roles'] = $this->role_model->get()->toArray();
        $this->dataAssign['data'] = $this->primary_model->findorFail($id);
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function edit($id)
    {
        $this->dataAssign['data'] = $this->primary_model->findorFail($id);
        $this->dataAssign['roles'] = $this->role_model->get()->toArray();
        $this->dataAssign['id'] = $id;
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::store());

        if ($validation->fails()) {
            return sendErrorToClient($validation->messages()->all());
        }

       $request->merge(['password' => Hash::make($request->password)]);

        $this->primary_model->create($request->only($this->primary_model->getFillable()));

    }

    public function update(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::update($request));

        if ($validation->fails()) {
            return sendErrorToClient($validation->messages()->all());
        }

        if($request->password ==""){
            unset($request['password']);
        }else{
              $request->merge(['password' => Hash::make($request->password)]);
        }

        $user = $this->primary_model->find($request->id);
        $user->update($request->only($this->primary_model->getFillable()));

        // $blocked_status_id = $this->status_model->getStatusID($this->dataAssign['module'], 'block');
        // if ($request->all()['status_id'] == $blocked_status_id) {
        //     $user->token()->delete();
        // }

        return redirect($this->dataAssign['module']);
    }

    public function delete(Request $request){
        $this->primary_model->where('id', $request->id)->delete();
        return sendMsgToClient(trans('auth.deleted_successfully'));
    }

    protected function ajaxListing()
    {
        $data = $this->primary_model->whereNotNull("created_at")->where("id","!=",1);
        $actions = $this->dataAssign['actions'];
        $module = $this->dataAssign['module'];
        $this->dataAssign['module_name'] = "admin";
        $ordering = true;
        return $this->makeDataTable($data, $actions, $module, $ordering,$this->dataAssign['module_name']);
    }


    public function password_resetmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = $this->primary_model->where('email', $request->email)->first();

        if($user != null){

        $token = Crypt::encryptString($user->id);

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
            $sentmail = \Mail::to($request->email)->send(new ResetPassword($user, $token));

        }else{
            return redirect()->back()->withErrors(['email' => 'Invalid Email']);
        }

        return redirect()->back()->with("message", "Email sent");
    }

    public function ShowPasswordResetForm($token)
    {
        $tokendata = DB::table('password_resets')->where('token',$token)->first();

        $status = session('passcodeChanged');
        if($status){
            // session()->forget('passcodeChanged');
            DB::table('password_resets')->where('token',$token)->delete();
        }

        if($tokendata){

            return view('auth.passwords.reset',['token' => $token]);
        }

        return redirect('forgot_password')->withErrors(['email' => 'link expired please resend email!']);
    }

    public function update_password(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $id = Crypt::decryptString($request->token);

        $user = $this->primary_model->where('id', $id)->update(['password' => Hash::make($request->password)]);

        if($user){
            return redirect()->back()->with(['message' => 'password changed successful', 'passcodeChanged' => true]);
        }
    }
}
