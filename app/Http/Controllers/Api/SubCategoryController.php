<?php

namespace App\Http\Controllers\Api;

use App\Events\UserSignUp;
use App\Http\Validation\RulesAppUser as Rules;
use App\Models\AppUser;
use App\Models\Status;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class SubCategoryController extends Controller
{
    function __construct()
    {
        $this->primary_model = new AppUser();
        $this->status_model = new Status();
        $this->sub_cat_model = new SubCategory();
        $this->module = $this->primary_model->getTable();
    }

    public function addSubCategory(Request $request)
    {

        $slug = strtolower(str_replace(" ","_",$request->title));
        $request->merge(['slug'=>$slug]);
        $validation = Validator::make($request->all(), Rules::storeSubCategory($request->user_id));

        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }
        $sub_cat = $this->sub_cat_model->store($request);
        return makeClientHappy($sub_cat,trans('auth.success'));

    }

    public function deleteSubCategory(Request $request)
    {
        $validation = Validator::make($request->all(), Rules::deleteSubCategory());

        if ($validation->fails()) {
            return sendErrorToClient($validation->errors()->first());
        }

        // $sub_cat = $this->sub_cat_model->where('id',$request->id)->delete();
        $sub_cat = $this->sub_cat_model->where('id',$request->id)->update(['status' => 0]);

        return sendMsgToClient(trans('auth.deleted'));

    }

    public function subCategoryListing(Request $request)
    {
        // if(isset($request->local) && $request->local=="ar"){
        //     app()->setLocale('ar');
        // }

        $parent_id = getParentId('app_users','id',$request->user_id);

        if($parent_id !=0){

            $user_id = $parent_id;
        }else {
            $user_id = $request->user_id;

        }
        $request->merge(['user_id'=>$user_id]);


        $categories=[];
        $sub_cat = $this->sub_cat_model->select('id','title')->where('user_id',$request->user_id)->where('status',1)->get();
        $title_ar='';
        foreach($sub_cat as $cat){
            if(trans("categories.".strtolower(str_replace(" ","_",$cat->title))) == "categories.".strtolower(str_replace(" ","_",$cat->title))){
                $title_ar ='';
            }else{
                $title_ar =trans("categories.".strtolower(str_replace(" ","_",$cat->title)));
            }
            $categories[]=['id' => $cat->id,'title'=>$cat->title,'title_ar'=>$title_ar];
        }
        return pageResponse($categories,trans('auth.success'));
    }
}
