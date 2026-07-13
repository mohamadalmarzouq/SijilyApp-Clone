<?php

namespace App\Http\Controllers\Panel;

use App\Events\LandLordAccountApproved;
use App\Exports\UserExport;
use App\Http\Requests\User\StoreUser;
use App\Http\Requests\User\UpdateUser;
use App\Http\Requests\User\UpdateUserProfile;
use App\Models\Property;
use App\Models\Role;
use App\Models\Status;
use App\Models\UserProperty;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function __construct()
    {
        $this->primary_model = new User();
        $this->status_model = new Status();
        $this->dataAssign['module'] = 'users';
        $this->rawColumns = ['user_status', 'action'];
        $this->dataAssign['actions'] = ['add', 'edit', 'view', 'delete'];
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxListing';
        $this->dataAssign['ordering_column'] = $this->primary_model->orderingColumn();
        $this->dataAssign['ordering'] = true;
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable();
    }

    public function show()
    {
        $this->dataAssign['statuses'] = $this->status_model->getUserStatus();

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function store(StoreUser $storeUser)
    {
        $this->primary_model->create($storeUser->only($this->primary_model->getFillable()));

        return back();
    }

    public function update(UpdateUser $updateUser)
    {
        $user = $this->primary_model->find($updateUser->id);

        if ($updateUser->filled('password') && !is_null($updateUser->password)) {

            $updateUser->merge(['password' => Hash::make($updateUser->password)]);
        } else {
            $updateUser->request->remove('password');
        }

        $user->update($updateUser->only($this->primary_model->getFillable()));

        return redirect($this->dataAssign['module']);
    }

    public function edit($id)
    {
        $this->dataAssign['data'] = $this->primary_model->findOrFail($id);

        $this->dataAssign['statuses'] = $this->status_model->getUserStatus();

        $this->dataAssign['roles'] = $this->role_model->allRoles()->get();

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function view($id)
    {
        $this->dataAssign['data'] = $this->primary_model->findOrFail($id);

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function profile()
    {
        $current_user_id = Auth()->user()->id;

        $this->dataAssign['data'] = $this->primary_model->find($current_user_id);

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function updateProfile(UpdateUserProfile $updateUserProfile)
    {
        if ($updateUserProfile->hasFile('image')) {

            $image_path = uploadCustomFile($updateUserProfile->image);

            $updateUserProfile->merge(['photo' => $image_path]);
        }

        if ($updateUserProfile->filled('password') && !is_null($updateUserProfile->password)) {

            $updateUserProfile->merge(['password' => Hash::make($updateUserProfile->password)]);
        } else {
            $updateUserProfile->request->remove('password');
        }

        $user = $this->primary_model->find($updateUserProfile->id);

        $user->update($updateUserProfile->only($this->primary_model->getFillable()));

        flash('User profile updated', 'success');

        return back();
    }

    public function delete($id)
    {
        $data = $this->primary_model->find($id);

        $data->delete();

        return back();
    }

    public function export()
    {
        return Excel::download(new UserExport(), time() . $this->dataAssign['module'] . '.xlsx');
    }

    protected function ajaxListing()
    {
        $data = $this->primary_model->ajaxListing();

        $actions = $this->dataAssign['actions'];

        $module = $this->dataAssign['module'];

        $ordering = $this->dataAssign['ordering'];

        return $this->makeDataTable($data, $actions, $module, $ordering);
    }
}
