<?php

namespace App\Http\Controllers\Panel;

use App\Http\Validation\RulesAppUser as Rules;
use App\Models\AppUser;
use App\Models\UserSubscription;
use App\Models\Subscription;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class RevenueController extends Controller
{
    function __construct()
    {
        $this->primary_model = new AppUser();
        $this->subscription_model = new Subscription();
        $this->module = $this->primary_model->getTable();
        $this->dataAssign['module'] = 'revenue';
        $this->dataAssign['actions'] = ['delete']; //'view','add','edit',
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxListing';
        $this->dataAssign['ordering_column'] = $this->primary_model->orderingColumn();
        $this->dataAssign['ordering'] = true;
        $this->dataAssign['id'] = 0;
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable();
    }

    public function show(){
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function perUser(Request $request){
       $response = $this->subscription_model->getPerUser($request);
       return $response;
    }

    public function PerUserChart(Request $request){
       $response = $this->subscription_model->getPerUserChart($request);
       return $response;
    }

    public function perSubscription(Request $request){
        $response = $this->subscription_model->perSubscriptions($request);
        return $response;
    }

    public function SubscriptionChart(Request $request){
        $response = $this->subscription_model->perSubscriptionChart($request);
        return $response;
    }

    public function Abandoned(Request $request){
        $response = $this->subscription_model->getAbandoned($request);
        return $response;
    }

    public function AbandonedChart(Request $request){
        $response = $this->subscription_model->getAbandonedChart($request);
        return $response;
    }
}
