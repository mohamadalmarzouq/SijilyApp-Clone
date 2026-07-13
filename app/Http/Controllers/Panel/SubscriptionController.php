<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Support\Facades\Validator;
use App\Http\Validation\RulesSubscription as Rules;
use App\Models\Upload;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->primary_model = new Subscription();
        $this->uploadModel = new Upload();
        $this->dataAssign['module'] = 'subscriptions';
        $this->dataAssign['actions'] = ['view','add','edit','delete'];
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxListing';
        $this->dataAssign['ordering_column'] = $this->primary_model->orderingColumn();
        $this->dataAssign['ordering'] = true;
        $this->dataAssign['id'] = 0;
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable();

    }

    public function store(Request $request)
    {
        try{

            $validation = Validator::make($request->all(), Rules::addSubscription());

            if ($validation->fails()) {
                return sendErrorToClient($validation->messages()->all());
            }

            $image="";
            $slug =  strtolower(str_replace(" ","_",$request->subscription));
            $request->merge(['slug'=>$slug,'expiry'=>'per month']);
            $id = $this->primary_model->create($request->only($this->primary_model->getFillable()))->id;

            if($request->hasFile('image')){
                $image = $this->uploadModel->uploadFile($request->image);
            }

            $this->primary_model->where('id',$id)->update(['image'=>$image]);
        }catch(\Exception $e){
            return sendExpToClient($e);
        }

    }

    public function delete($id){
       return $this->primary_model->where("id",$id)->delete();
    }

    public function add(){
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function view($id)
    {
         $this->dataAssign['data'] = $this->primary_model->findorFail($id);
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function show()
    {
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function edit($id)
    {
         $this->dataAssign['data'] = $this->primary_model->findorFail($id);
         return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function update(Request $request)
    {
        try{
            $validation = Validator::make($request->all(), Rules::update($request));

            if ($validation->fails()) {
                return sendErrorToClient($validation->messages()->all());
            }
            $slug =  strtolower(str_replace(" ","_",$request->subscription));
            $request->merge(['slug'=>$slug,'expiry'=>'per month']);
            $this->primary_model->where("id",$request->id)->update($request->only($this->primary_model->getFillable()));

            if($request->hasFile('image')){
                $image = $this->uploadModel->uploadFile($request->image);
                $this->primary_model->where('id',$request->id)->update(['image'=>$image]);
            }
         }catch(\Exception $e){
            return sendExpToClient($e);
        }
    }

    protected function ajaxListing()
    {
        $data = $this->primary_model->whereNull("deleted_at");
        $actions = $this->dataAssign['actions'];
        $module = $this->dataAssign['module'];
        $ordering = true;
        return $this->makeDataTable($data, $actions, $module, $ordering);
    }
}
