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


class ReportController extends Controller
{
     public function __construct()
    {
        $this->primary_model = new AppUser();
        $this->subscription_model = new Subscription();
        $this->user_subscription_model = new UserSubscription();
        $this->module = $this->primary_model->getTable();
        $this->dataAssign['module'] = 'reports';
        $this->dataAssign['actions'] = ['delete']; //'view','add','edit',
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxListing';
        $this->dataAssign['ordering_column'] = $this->primary_model->orderingColumn();
        $this->dataAssign['ordering'] = true;
        $this->dataAssign['id'] = 0;
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function newSubscription()
    {
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function consumerRetention()
    {
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function subcriptionDueforRenewal()
    {
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function subscriptionsSold()
    {
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function perUser()
    {
         $this->dataAssign['packages'] = $this->subscription_model->whereNull('deleted_at')->get();
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function perSubscription()
    {
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function Abandoned()
    {
        $this->dataAssign['packages'] = $this->subscription_model->whereNull('deleted_at')->get();
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }


    public function newSubscriptions(Request $request){
        $response = $this->primary_model->getNewSubscriptions($request);
        return $response;
      }

      public function consumerRetentionValue(Request $request){
          $response = $this->primary_model->getRetainUsers($request);
          return $response;
      }

      public function subscriptionsRenewal(Request $request){
          $response = $this->primary_model->getRenewalUser($request);
          return $response;
      }

      public function subscriptionsSoldData(Request $request){
          $response = $this->subscription_model->soldSubscriptions($request);
          return $response;
      }

      public function perUserData(Request $request){
        $response = $this->subscription_model->getPerUser($request);
        return $response;
     }

     public function perSubscriptionData(Request $request){
        $response = $this->subscription_model->perSubscriptions($request);
        return $response;
    }
    public function AbandonedData(Request $request){
    $response = $this->subscription_model->getAbandoned($request);
        return $response;
    }
}
