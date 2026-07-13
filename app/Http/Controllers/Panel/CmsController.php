    <?php

namespace App\Http\Controllers\Panel;

use App\Http\Requests\AppUser\UpdateAppUser;
use App\Models\AppUser;
use App\Models\HelpVideo;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CmsController extends Controller
{
    public function __construct()
    {
        $this->primary_model = new AppUser();
        $this->help_model = new HelpVideo();
        $this->status_model = new Status();
        $this->dataAssign['module'] = 'cms';
        $this->dataAssign['actions'] = ['view','add','edit','delete'];
        $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxListing';
        $this->dataAssign['ordering_column'] = $this->primary_model->orderingColumn();
        $this->dataAssign['ordering'] = true;
        $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable();
        $this->dataAssign['id'] = 0;
        $this->dataAssignVideos['module'] = 'videos';
        $this->dataAssignVideos['actions'] = ['view','add','edit','delete'];
        $this->dataAssignVideos['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxListingVideos';
        $this->dataAssignVideos['ordering_column'] = $this->help_model->orderingColumn();
        $this->dataAssignVideos['ordering'] = true;
        $this->dataAssignVideos['data_table_columns'] = $this->help_model->getColumnsForDataTable();
    }

    public function show()
    {
        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function videos()
    {
        return view($this->layout_base . '.' . $this->dataAssignVideos['module'] . '.' . __FUNCTION__, $this->dataAssignVideos);
    }

    protected function ajaxListingVideos()
    {
        $data = $this->help_model->select('*');
        $actions = $this->dataAssign['actions'];
        $module = $this->dataAssign['module'];
        $ordering = true;
        return $this->makeDataTable($data, $actions, $module, $ordering);
    }

    public function view($id)
    {
        $this->dataAssign['statuses'] = $this->status_model->getStatusByModule($this->dataAssign['module']);

        $this->dataAssign['data'] = $this->primary_model->findorFail($id);

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function edit($id)
    {
        $this->dataAssign['statuses'] = $this->status_model->getStatusByModule($this->dataAssign['module']);

        $this->dataAssign['data'] = $this->primary_model->findorFail($id);

        return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function users(){
          $this->dataAssign['data_table_columns'] = $this->primary_model->getColumnsForDataTable("user");
          $this->dataAssign['route_name_for_listing'] = $this->dataAssign['module'] . '.ajaxUserListing';
          return view($this->layout_base . '.' . $this->dataAssign['module'] . '.' . __FUNCTION__, $this->dataAssign);
    }

    public function update(UpdateAppUser $updateAppUser)
    {
        $user = $this->primary_model->find($updateAppUser->id);

        $user->update(['status_id' => $updateAppUser->status_id]);

        $blocked_status_id = $this->status_model->getStatusID($this->dataAssign['module'], 'block');

        if ($updateAppUser->status_id == $blocked_status_id) {

            $user->token()->delete();
        }

        return redirect($this->dataAssign['module']);
    }

    public function delete(Request $request){
        $this->primary_model->where('id', $request->id)->where('deleted', 0)->update(['deleted' => 1, 'is_disabled' => 1]);
        return sendMsgToClient(trans('auth.deleted_successfully'));
    }

    protected function ajaxListing()
    {
        $data = $this->primary_model->with(['status','Subscription.subscription'])->withCount('userCount')->where('is_child',0)->where("deleted",0);
        $actions = $this->dataAssign['actions'];
        $module = $this->dataAssign['module'];
        $ordering = true;
        return $this->makeDataTable($data, $actions, $module, $ordering);
    }

    protected function ajaxUserListing()
    {
        $data = $this->primary_model->with(['status','parentUser'])->where('is_child',1)->where("deleted",0);
        $actions = $this->dataAssign['actions'];
        $module = $this->dataAssign['module'];
        $ordering = true;
        return $this->makeDataTable($data, $actions, $module, $ordering);
    }
}
