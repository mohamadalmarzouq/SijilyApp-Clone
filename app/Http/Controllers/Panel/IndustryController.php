<?php

namespace App\Http\Controllers\Panel;

use App\Http\Validation\RulesAppUser as Rules;
use App\Models\AppUser;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class IndustryController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Industry();
        $this->user_model = new AppUser();
        $this->module = $this->primary_model->getTable();
        $this->dataAssign['module'] = 'industries';
        $this->dataAssign['actions'] = ['delete']; //'view','add','edit',
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxListing';
        $this->dataAssign['ordering_column'] = $this->primary_model->orderingColumn();
        $this->dataAssign['ordering'] = true;
        $this->dataAssign['id'] = 0;
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable();
    }

     public function show()
    {
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function view($id)
    {
        $this->dataAssign['statuses'] = $this->status_model->getStatusByModule($this->dataAssign['module']);

        $this->dataAssign['data'] = $this->primary_model->findorFail($id);

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function edit($id)
    {
        $this->dataAssign['statuses'] = $this->status_model->getStatusByModule($this->dataAssign['module']);

        $this->dataAssign['data'] = $this->primary_model->findorFail($id);

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function delete(Request $request){

       $checkExist = $this->user_model->where('industry_type', $request->id)->get()->toArray();
       if(!empty($checkExist)){
                return sendErrorToClient(trans('auth.can_not_delete'));
        }else{
             return sendMsgToClient(trans('auth.deleted_successfully'));
        }

    }

    protected function ajaxListing()
    {
        $data = $this->primary_model->whereNotNull("deleted_at");
        $actions = $this->dataAssign['actions'];

        $module = $this->dataAssign['module'];

        $ordering = true;

        return $this->makeDataTable($data, $actions, $module, $ordering);
    }
}
