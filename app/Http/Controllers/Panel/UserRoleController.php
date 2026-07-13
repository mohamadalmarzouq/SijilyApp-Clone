<?php

namespace App\Http\Controllers\Panel;
use App\Models\UserRole;
use App\Models\AppUser;
use App\Models\UserPermission;
use App\Models\UserModule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Validation\RulesUserRole as Rules;

class UserRoleController extends Controller
{
    function __construct(){
        $this->primary_model = new UserRole();
        $this->permission_model = new UserPermission();
        $this->module_model = new UserModule();
        $this->appUser_model = new AppUser();
        $this->dataAssign['module'] = 'user_role';
        $this->dataAssign['actions'] = ['edit','delete'];
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxChildListing';
        $this->dataAssign['ordering_column'] = $this->primary_model->orderingColumn();
        $this->dataAssign['ordering'] = true;
        $this->dataAssign['id'] = 0;
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable();
    }

    public function show(){
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function store(Request $request){
        $validation = Validator::make($request->all(), Rules::store(),['unique'=> 'Role :attribute has already been taken']);

        if ($validation->fails()) {
            return sendErrorToClient($validation->messages()->all());
        }

        $request_data = [];
        $params = $request->all();
        $slug = str_replace(" ","_",strtolower($request->name));
        $request->merge(['slug'=>$slug]);
        $id = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;
        if(isset($params['access_modules'])){
            foreach($params['access_modules'] as $param){
                $request_data[] = [
                    "module_id" => $param,
                    "role_id" => $id,
                ];
            }
          $this->permission_model->insert($request_data);
        }
    }

    public function delete($id){
        $IfExist = $this->appUser_model->where("role_id",$id)->get()->toArray();
        if(empty($IfExist)){
            $this->primary_model->where("id",$id)->delete();
        }else{
            return sendErrorToClient("It can not remove. It seems the role is assigned to user");
        }
    }

    public function update(Request $request){
        // dd($request->all());
        $validation = Validator::make($request->all(), Rules::update($request),['unique'=> 'Role :attribute has already been taken']);

        if ($validation->fails()) {
            return sendErrorToClient($validation->messages()->all());
        }

        $request_data = [];
        $params = $request->all();
        $slug = str_replace(" ","_",strtolower($request->name));
        $request->merge(['slug'=>$slug]);
        $this->primary_model->where('id',$request->id)->update($request->only($this->primary_model->getFillable()));
        if(isset($params['access_modules'])){
            $this->permission_model->where('role_id', $request->id)->delete();
            foreach($params['access_modules'] as $param){
                $request_data[] = [
                    "module_id" => $param,
                    "role_id" => $request->id,
                ];
            }
          $this->permission_model->insert($request_data);
        }
    }

    public function edit($id){
         $this->dataAssign['module_permissions'] = $this->module_model->get()->toArray();
         $this->dataAssign['permissions'] = $this->permission_model->where('role_id',$id)->get()->toArray();
         $this->dataAssign['roles'] = $this->primary_model->findorFail($id);
         $this->dataAssign['id'] = $id;
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    protected function ajaxChildListing()
    {
        $data = $this->primary_model->whereNull("deleted_at");
        $this->dataAssign['actions'] = ['edit','delete'];
        $actions = $this->dataAssign['actions'];
        $module = $this->dataAssign['module'];
        $ordering = true;
        return $this->makeDataTable($data, $actions, $module, $ordering);
    }

    public function add(){
        $this->dataAssign['module_permissions'] = $this->module_model->get()->toArray();
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }
}
