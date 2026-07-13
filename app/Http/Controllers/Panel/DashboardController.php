<?php

namespace App\Http\Controllers\Panel;
use App\Models\Country;
use App\Models\AppUser;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Carbon\Carbon;

class DashboardController extends Controller
{
    function __construct(){
        $this->primary_model = new AppUser();
        $this->user_subscription_model = new UserSubscription();
        $this->subscription_model = new Subscription();
        $this->dataAssign['actions'] = [];
        $this->dataAssign['module'] = 'app_user';
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

    public function subscriptionsSold(Request $request){
        $response = $this->subscription_model->soldSubscriptions($request);
        return $response;
    }
}
