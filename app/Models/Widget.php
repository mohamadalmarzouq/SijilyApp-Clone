<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class Widget extends Model
{
    protected $fillable = ['title', 'icon', 'query', 'module', 'method', 'type', 'status_id'];

    protected $appends = ['widget_status'];

    public function getColumnsForDataTable()
    {
        $data = [
            ['data' => 'title', 'name' => 'title'],
            ['data' => 'type.name', 'name' => 'type.name', 'title' => 'Widget Type'],
            ['data' => 'widget_roles', 'name' => 'widget_roles', 'title' => 'Access Roles', 'searchable' => 'false'],
            ['data' => 'widget_status', 'name' => 'widget_status', 'title' => 'Widget Status', 'searchable' => 'false'],
            ['data' => 'action', 'name' => 'Actions', 'searchable' => 'false'],
            ['data' => 'created_at', 'name' => 'created_at', 'visible' => false]
        ];

        return json_encode($data);
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function getWidgets($current_user)
    {
        return $this->orderByRaw('sorting + 0 asc')->where('status','active')->get();
    }

    public function getQueryResult($widgets)
    {
        foreach ($widgets as $key => $widget) {
            if (isset($widget->query)) {
                $widget->query = $this->parseWidgetQuery($widget->query);
                $widget->query = DB::select($widget->query);
            }
        }
        return $widgets;
    }

    public function parseWidgetQuery($query)
    {
        $new_query = '';
        $replacers = [
            '[USER_ID]' => Auth::user()->id,
            '[USER_ROLE]' => Auth::user()->role_id,
        ];

        foreach ($replacers as $key => $replacer) {
            $new_query = str_replace($key, $replacer, $query);
            $query = $new_query;
        }

        return $new_query;
    }

    public function getListingData($widgets)
    {
        $listing_data = [];

        foreach ($widgets as $key => $widget) {

            if ($widget->type == 'table' || $widget->type == 'single_table') {

                $controller = config('filesystems.FULL_PANEL_CONTROLLER_PATH') . ucfirst($widget->module) . 'Controller';

                $class = new $controller(Request::create('', 'GET'));

                $widget->listing_data = json_decode($class->{$widget->method}());

                $listing_data[] = $widget;
            }
        }
        return $listing_data;
    }

    public function getGraphData($widgets)
    {
        $graph_data = [];

        foreach ($widgets as $key => $widget) {

            if ($widget->type == 'flot_line_chart' ||
                $widget->type == 'pie_chart' ||
                $widget->type == 'flot_bar_chart') {

                $controller = config('filesystems.FULL_PANEL_CONTROLLER_PATH') . ucfirst($widget->module) . 'Controller';

                $class = new $controller(Request::create('', 'GET'));

                $widget->graph_data = json_decode($class->{$widget->method}());

                $graph_data[] = $widget;
            }
        }

        return $graph_data;
    }
}
