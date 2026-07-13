<?php

namespace App\Http\Controllers\Api;
use App\Models\UserRole;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserRoleController extends Controller
{
    function __construct(){
        $this->primary_model = new UserRole();
    }

    public function get(){
        return $this->primary_model->apiListing();
    }
}
