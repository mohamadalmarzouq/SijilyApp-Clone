<?php

namespace App\Http\Middleware;

use App\Models\ActivityLogTag;
use App\Models\AppUser;
use Closure;

class ActivityLog
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $app = $next($request);

        if (session()->has('activity_log_data')) {

            $parent_id = getParentId('app_users','id',$request->user_id);

            if($parent_id !=0){
                $recorded_by = $request->user_id;
                $request->user_id = $parent_id;
            }else {
                $recorded_by = $request->user_id;
            }
            $request->merge(['user_id'=>$request->user_id]);

            $log_data = session()->get('activity_log_data');

            $current_user = AppUser::find($request->user_id);

            $identifier = $log_data['identifier'];

            $activity_log_tag = ActivityLogTag::where('identifier', $identifier)->first();

            $module_column = getSingleRelationDataForPDF($log_data['subject_type'], $log_data['name']);
            if($log_data['module']=="owner_accounts"){
                $log_data['module'] = $log_data['name'];
            }
            $module_name = !empty($log_data['no_module']) ? '' : ',' . setText($log_data['module'], true);

            //  $replacers = $current_user->full_name . $module_name . ','. $log_data['data'];
            //$replacers = $current_user->full_name . $module_name . ',' . $module_column;
            if (isset($log_data['data']) && !empty($log_data['data']))
                $replacers = $current_user->full_name . $module_name . ',' . $log_data['data'];
            else
                $replacers = $current_user->full_name . $module_name;

            $description = str_replace(explode(',', $activity_log_tag->wildcards), explode(',', $replacers), $activity_log_tag->body);

            $title = str_replace(explode(',', $activity_log_tag->wildcards), explode(',', $replacers), $activity_log_tag->title);

            $activity = activity($title)
                ->causedBy($current_user)
                ->performedOn($log_data['subject_type'])
                ->log($description);

            $activity->module = $log_data['module'];

            $activity->save();

            $data = (isset($log_data['data_ar'])) ? $log_data['data_ar'] : "";

            RecordArabicLog($log_data['module'],$activity->id,$data,$recorded_by);

            session()->forget('activity_log_data');
        }

        return $app;
    }
}
