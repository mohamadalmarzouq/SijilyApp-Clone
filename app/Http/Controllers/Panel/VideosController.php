<?php   namespace App\Http\Controllers\Panel;
use Illuminate\Validation\ValidationException;
use App\Models\AppUser;
use App\Models\Upload;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\HelpVideo;

class VideosController extends Controller
{
    function __construct()
    {
        $this->primary_model = new HelpVideo();
        $this->user_model = new AppUser();
        $this->uploadModel = new Upload();
        $this->module = $this->primary_model->getTable();
        $this->dataAssign['module'] = 'videos';
        $this->dataAssign['actions'] = ['add', 'edit','delete']; //'view','add','edit',
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

    public function add()
    {
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function save(Request $request){
        $validation = Validator::make($request->all(), ["title"=>'required|max:30',"url"=>'required',"image"=>'required|mimes:jpg,jpeg,png,bmp,tiff']);
        if ($validation->fails()) {
            return sendErrorToClient($validation->messages()->all());
        }

        // if (strpos($request->embedded_url, '<iframe') === false) {
        //       return sendErrorToClient(["Embedded field should be Youtube iframe."]);
        // }
        $url = $request->url;

        $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i';

        if (!preg_match($pattern, $url, $matches)) {

            return sendErrorToClient(["Invalid YouTube URL."]);
        }
        if($request->hasFile('image')){
            $image = $this->uploadModel->uploadFile($request->image);
            $request->merge(['thumb_nail'=>$image]);
        }


        $this->primary_model->create($request->only($this->primary_model->getFillable()));
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $this->dataAssign['data'] = $this->primary_model->findorFail($id);

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function delete(Request $request){
        $sub_cat = $this->primary_model->where('id', $request->id)->delete();
        return $sub_cat;
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
        $validation = Validator::make($request->all(), ["title"=>'required|max:30',"url"=>'required',"image"=>'mimes:jpg,jpeg,png,bmp,tiff']);
        if ($validation->fails()) {
            return sendErrorToClient($validation->messages()->all());
        }
        $url = $request->url;

        $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i';

        if (!preg_match($pattern, $url, $matches)) {

            return sendErrorToClient(["Invalid YouTube URL."]);

        }
        // if (strpos($request->embedded_url, '<iframe') === false) {
        //       return sendErrorToClient(["Embedded field should be Youtube iframe."]);
        // }

        if($request->hasFile('image')){
            $image = $this->uploadModel->uploadFile($request->image);
            $request->merge(['thumb_nail'=>$image]);
        }
        $help_video = $this->primary_model->find($request->id);
        $help_video->update($request->only($this->primary_model->getFillable()));
    }
}
