<?php

namespace App\Http\Controllers\Panel;

use App\Http\Requests\Role\StoreRole;
use App\Http\Requests\Role\UpdateRole;
use App\Http\ViewComposer\SiderbarComposer;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AccessToken;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->primary_model = new Role();
        $this->permission_model = new Permission();
        $this->modules = new SiderbarComposer();
        $this->dataAssign['module'] = 'roles';
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxListing';
        $this->dataAssign['actions'] = ['add', 'edit', 'delete'];
        $this->dataAssign['ordering_column'] = $this->primary_model->orderingColumn();
        $this->dataAssign['ordering'] = true;
        $this->dataAssign['id'] = 0;
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable();
    }

    public function show()
    {
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function store(StoreRole $storeRole)
    {
        try {
            $storeRole->merge(['slug' => Str::slug($storeRole->name)]);

            $role = $this->primary_model->create($storeRole->only($this->primary_model->getFillable()));

            $this->permission_model->attachRoles($storeRole, $role->id);

            $storeRole->session()->flash('activity_log_data', [
                'identifier' => 'role_added',
                'subject_type' => $role,
                'name' => 'name',
                'module' => $this->dataAssign['module'],
                'method' => __FUNCTION__
            ]);
        }catch (\Exception $exception){
            //
        }
    }

    public function edit($id)
    {
        $this->dataAssign['modules'] = $this->modules->getModuleList();

        $this->dataAssign['data'] = $this->primary_model->with('permissions')->findOrFail($id);

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function update(UpdateRole $updateRole)
    {
                // dd($updateRole);
              $updateRole->merge(['slug' => Str::slug($updateRole->name)]);

                $role = $this->primary_model->find($updateRole->id);

                $role->update($updateRole->only($this->primary_model->getFillable()));

                $this->permission_model->attachRoles($updateRole, $updateRole->id);

                // $updateRole->session()->flash('activity_log_data', [
                //     'identifier' => 'role_updated',
                //     'subject_type' => $role,
                //     'name' => 'name',
                //     'module' => $this->dataAssign['module'],
                //     'method' => __FUNCTION__
                // ]);

                // $exp_time = time() + (365 * 24 * 60 * 60);
                // AccessToken::query()->update(['expiry_time' => $exp_time]);

                return redirect($this->dataAssign['module']);

    }

    public function delete($id)
    {
        $data = $this->primary_model->findOrFail($id);

        $data->delete();

        request()->session()->flash('activity_log_data', [
            'identifier' => 'role_deleted',
            'subject_type' => $data,
            'name' => 'name',
            'module' => $this->dataAssign['module'],
            'method' => __FUNCTION__
        ]);
    }

    protected function ajaxListing()
    {
        $data = $this->primary_model->allRoles();

        $actions = $this->dataAssign['actions'];

        $module = $this->dataAssign['module'];

        $ordering = $this->dataAssign['ordering'];

        return $this->makeDataTable($data, $actions, $module,$ordering);
    }

}
