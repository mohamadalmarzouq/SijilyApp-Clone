<?php

namespace App\Http\Controllers\Panel;

use App\Models\Widget;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PanelController extends Controller
{
    public function __construct()
    {
        $this->primary_model = new Widget();
        $this->dataAssign['module'] = 'dashboard';
    }

    public function index()
    {
        $current_user = auth()->user();

        $widgets = $this->primary_model->getWidgets($current_user);

        $this->dataAssign['widgets'] = $this->primary_model->getQueryResult($widgets);

        $this->dataAssign['listing_data'] = $this->primary_model->getListingData($widgets);

        $this->dataAssign['graph_data'] = $this->primary_model->getGraphData($widgets);

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }
}
