<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $layout_base = 'panel';
    protected $buttons_view = 'includes.datatables_row_buttons';
    protected $actions = ['add', 'edit', 'delete'];
    protected $toggle_view = 'includes.toggle_view';
    protected $image_view = 'includes.image_view';
    protected $url_view = 'includes.url_view';
    protected $show_toggle_in_list = [];
    protected $show_image_in_list = [];
    protected $rawColumns = ['action'];
    protected $hasManualSearch = [];
    protected $show_column_url_in_list = [];
    protected $data_limit = 10;
    protected $dataList= '';

    protected function ajaxListing()
    {
        $data = $this->primary_model::query();

        $actions = $this->dataAssign['actions'];

        $module = $this->dataAssign['module'];

        return $this->makeDataTable($data, $actions, $module);
    }

    final function makeDataTable($data, $actions, $module, $is_order = false,$module_name="")
    {
        $this->dataList = $module_name;
        // dd($this->dataList);
        
        $buttons = empty($this->makeCustomActionButtonsForNestedTables)
            ? $this->makeCustomActionButtons($module)
            : $this->makeCustomActionButtonsForNestedTables;


        $buttons_view = $this->buttons_view;
        
        $data_table = Datatables::of($data)->order(function ($query) use ($is_order) {
            if (request()->draw != '1' && $is_order) {
                

                !empty($order_array) ? $this->manualOrdering($query, $order_array) : $this->manualOrdering($query);
            }else if($this->dataList=="subscriber"){
                // $array = !empty($order_array) ? $order_array : $this->primary_model->orderArray();
                // $columns = $this->primary_model->orderingColumn();
                // $columns =json_decode($columns);
                // $query->orderByJoin($array[$columns[0][0]]['name'], $columns[0][1]);
                                                // !empty($order_array) ? $this->manualOrdering($query, $order_array) : $this->manualOrdering($query);
                // $query->orderBy('id', $is_order ? 'ASC' : 'DESC');
                $query->orderBy('id', 'DESC');
            }
            else if($this->dataList == "admin"){
                $query->orderBy('created_at', 'DESC');
                // $query->orderBy('created_by', 'DESC');

            }
            else {
                                // !empty($order_array) ? $this->manualOrdering($query, $order_array) : $this->manualOrdering($query);

                $query->orderBy('id', 'DESC');
            }



        }, true)->filter(function ($query) {
            $array_module = ["users.","faqs."];
            $array = $this->primary_model->orderArray($this->dataList);
            $prevent_search = $this->primary_model->preventSearch();
            if(request()->search['value']){
                $counter = 0;
                foreach($array as $key => $arr){

                    if(in_array($arr['name'],$prevent_search)){
                            continue;
                    }

                    if(isset($arr['search']) && $arr['search'] == true && !isset($arr['relation'])){
                        if($counter == 0 && in_array($this->primary_model->getModuleName(),$array_module)){
                             $query->where($this->primary_model->getModuleName().$arr['name'],'like','%'.request()->search['value'].'%');
                        }else if($counter == 1 && !in_array($this->primary_model->getModuleName(),$array_module)){
                             $query->where($this->primary_model->getModuleName().$arr['name'],'like','%'.request()->search['value'].'%');
                        }else{
                             $query->orWhere($this->primary_model->getModuleName().$arr['name'],'like','%'.request()->search['value'].'%');
                             if(isset($this->dataAssign['search_col']) && !empty($this->dataAssign['search_col'])){
                                    $array_keys = array_keys($this->dataAssign['search_col'])[0];
                                    $array_values = array_values($this->dataAssign['search_col'])[0];
                                    $query->where($array_keys,$array_values);
                             }

                            if(isset($this->dataAssign['id']) && $this->dataAssign['id']){
                                
                                $query->where('app_users.parent',$this->dataAssign['id']);
                            }
                        }
                        if($this->dataList == "subscriber" || $this->dataList == "users"){
                            $query->where('app_users.deleted', 0);
                        }
    
                    }else if(isset($arr['search']) && $arr['search'] == true && isset($arr['relation'])){
                        $query->orWhereJoin($arr['name'],'like','%'.request()->search['value'].'%');
                        if($this->dataList == "subscriber"  || $this->dataList == "users" ){
                            $query->where('app_users.deleted', 0);
                        }
                          // 👇 Dump relation search query
    
                    }

                    $counter++;
                }

                // $query->where('full_name','like','%'.request()->search['value'].'%');
                // $query->orWhere('business_name','like','%'.request()->search['value'].'%');
                // $this->manualSearching($query,request()->search['value']);
            }
        })->addColumn('action', function ($row) use ($actions, $module, $buttons, $buttons_view) {
            return View::make($this->layout_base . '.' . $buttons_view, compact('buttons', 'actions', 'module', 'row'))->render();
        });
        //show toggle in datatable
        if (!empty($this->show_toggle_in_list)) {

            $toggle_view = $this->toggle_view;


            foreach ($this->show_toggle_in_list as $toggle_column) {

                $data_table->addColumn($toggle_column['column_name'], function ($row) use ($toggle_column, $toggle_view) {

                    return View::make($this->layout_base . '.' . $toggle_view, compact('row', 'toggle_column'))->render();

                });

                $this->rawColumns[] = $toggle_column['column_name'];
            }
        }

        if (!empty($this->show_image_in_list)) {

            $image_view = $this->image_view;

            foreach ($this->show_image_in_list as $image_column) {

                $data_table->addColumn($image_column['column_name'], function ($row) use ($image_column, $image_view) {

                    $image_path = getUserAvatar($row->id);

                    return View::make($this->layout_base . '.' . $image_view, compact('image_path'))->render();

                });

                $this->rawColumns[] = $image_column['column_name'];
            }
        }

        if (!empty($this->show_column_url_in_list)) {

            $column_url_view = $this->url_view;
            foreach ($this->show_column_url_in_list as $url_column) {
                $data_table->editColumn($url_column['column_name'], function ($row) use ($url_column, $column_url_view) {

                    $text = $row->{$url_column['column_name']};

                    $url_column['route_name'] = $row->module;

                    $id = $row->subject_id;

                    $actions = $url_column['actions'];

                    return View::make($this->layout_base . '.' . $column_url_view, compact('id', 'text', 'url_column',
                        'actions'))->render();

                });

                $this->rawColumns[] = $url_column['column_name'];
            }

        }

        if (!empty($this->hasManualSearch)) {
            $model = $this->hasManualSearch['model'];

            $data_table->filter(function ($query) use ($model) {
                return $model->manualSearch($query);
            });
        }

        return $data_table->rawColumns($this->rawColumns)->make(true);

    }

    protected function makeCustomActionButtons($module)
    {
        return [
            'cancel_user' => ['route' => $module . '.cancel_user'],
            'child_user' => ['route' => $module . '.child_user'],
            'edit' => ['route' => $module . '.edit'],
            'delete' => ['route' => $module . '.delete'],
            'view' => ['route' => $module . '.view'],
            'reset_package' => ['route' => $module . '.reset_package']
        ];
    }

    public function manualOrdering(&$query, $order_array = [])
    {

        if(!empty($this->dataList)){
              $array = !empty($order_array) ? $order_array : $this->primary_model->orderArray($this->dataList);
        }else{
               $array = !empty($order_array) ? $order_array : $this->primary_model->orderArray();
        }
        $order_column = request()->order[0];
        $column_no_to_sort = $order_column['column'];

        if ($array[$column_no_to_sort]['order']) {
            if (!empty($array[$column_no_to_sort]['relationship'])) {
                $query->orderByJoin($array[$column_no_to_sort]['name'], $order_column['dir']);
            } else {
                $query->orderBy($array[$column_no_to_sort]['name'], $order_column['dir']);
            }

        } else {
            $query->orderBy('created_at', 'desc');
        }
    }


}
