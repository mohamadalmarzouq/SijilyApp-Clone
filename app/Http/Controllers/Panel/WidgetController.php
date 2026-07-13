<?php

namespace App\Http\Controllers\Panel;

use App\Http\Requests\Widget\StoreWidget;
use App\Http\Requests\Widget\UpdateWidget;
use App\Models\Role;
use App\Models\Status;
use App\Models\Type;
use App\Models\Widget;
use App\Models\WidgetsRole;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WidgetController extends Controller
{
    public function __construct()
    {
        $this->primary_model = new Widget();
        $this->type_model = new Type();
        $this->widget_role_model = new WidgetsRole();
        $this->role_model = new Role();
        $this->status_model = new Status();
        $this->dataAssign['module'] = 'widgets';
        $this->rawColumns = ['widget_status', 'action'];
        $this->dataAssign['actions'] = ['add', 'edit', 'view', 'delete'];
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxListing';
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable();
    }

    public function show()
    {
        $this->dataAssign['types'] = $this->type_model->getWidgetTypes();

        $this->dataAssign['roles'] = $this->role_model->getWidgetRoles();

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function store(StoreWidget $storeWidget)
    {
        if (strpos(strtolower($storeWidget->all()['query']), 'update') !== FALSE || strpos(strtolower($storeWidget->all()['query']), 'delete') !== FALSE) {
            flash(ucfirst('Bad query'), 'danger');
            return back();
        }

        $status_id = $this->status_model->getStatusID($this->dataAssign['module'], 'active');

        $storeWidget->merge(['status_id' => $status_id]);

        $id = $this->primary_model->create($storeWidget->only($this->primary_model->getFillable()))->id;

        $this->widget_role_model->attachWidgetRoles($storeWidget, $id);
    }

    public function edit($id)
    {
        $this->dataAssign['data'] = $this->primary_model->findOrFail($id);

        $this->dataAssign['roles'] = $this->role_model->getWidgetRoles();

        $this->dataAssign['types'] = $this->type_model->getWidgetTypes();

        $this->dataAssign['widget_roles'] = $this->widget_role_model->getWidgetRolesID($id);

        $this->dataAssign['statuses'] = $this->status_model->getWidgetStatus();

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function update(UpdateWidget $updateWidget)
    {
        $this->primary_model->findOrFail($updateWidget->id)->update($updateWidget->only($this->primary_model->getFillable()));

        $this->widget_role_model->attachWidgetRoles($updateWidget, $updateWidget->id);

        return redirect($this->dataAssign['module']);

    }

    public function delete($id)
    {
        $data = $this->primary_model->findOrFail($id);

        $data->delete();

        return back();

    }

    protected function ajaxListing()
    {
        $data = $this->primary_model::query()->with(['type']);

        $actions = $this->dataAssign['actions'];

        $module = $this->dataAssign['module'];

        return $this->makeDataTable($data, $actions, $module);
    }
}
