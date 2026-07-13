<?php   namespace App\Http\Controllers\Panel;

use App\Models\AppUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Page;

class PagesController extends Controller
{
    function __construct()
    {
        $this->primary_model = new Page();
        $this->user_model = new AppUser();
        $this->module = $this->primary_model->getTable();
        $this->dataAssign['module'] = 'pages';
        $this->dataAssign['actions'] = ['edit']; //'view','add','edit',
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

    public function edit(Request $request)
    {
        $id = $request->id;
        $this->dataAssign['data'] = $this->primary_model->findorFail($id);
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    protected function ajaxListing()
    {
        $data = $this->primary_model->select("*");
        $actions = $this->dataAssign['actions'];
        $module = $this->dataAssign['module'];
        $ordering = true;
        return $this->makeDataTable($data, $actions, $module, $ordering);
    }

    public function update(Request $request){
        $page = $this->primary_model->find($request->id);
        $page->update($request->only($this->primary_model->getFillable()));
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.show', $this->dataAssign);
    }
}
