<div class="d-flex action_buttons">
 {{--  {{ dd($row->Subscription->subscription_id) }}  --}}

    <input type="hidden" value="{{ $row->id }}" id="id">
    @if(in_array('cancel',$actions) && hasRole($module , 'show'))
        @if ($row->package_taken == 0)
        <a href="#" class="mr-2" title="Cancel Subscription" style="pointer-events:none">
            <i class="{{ $row->status['slug'] == 'block' ? 'fa fa-times' : 'fa fa-times'}}" style="color:#ececec"></i>
        </a>
        @else
        <a href="{{  ($row->status['slug'] == "block"  || $row->is_subscribed==0) ? "#" :  route($buttons['cancel_user']['route'] , ['id' => $row->id,'subscription_id' => isset($row->subscription->subscription_id) ? $row->subscription->subscription_id :""])}}" class="mr-2" title="Cancel Subscription" style="{{ ($row->status['slug'] == 'block'|| $row->is_subscribed==0) ? 'pointer-events:none' :'' }}">
            <i class="{{ ($row->status['slug'] == 'block' || $row->is_subscribed==0 ) ? 'fa fa-times' : 'fa fa-times'}} " style="{{ ($row->status['slug'] == 'block'|| $row->is_subscribed==0 ) ? 'color:#ececec' :'' }}"></i>
        </a>
        @endif
    @endif

    @if(isset($row->Subscription) && !empty($row->Subscription->expiry_date) && date('Y-m-d') > $row->Subscription->expiry_date && getSubscription('subscriptions','id',$row->Subscription->subscription_id,'type') == 1 && $row->free_package == 1)
        <a href="{{ route($buttons['reset_package']['route'] , ['id' => $row->id]) }}" class="mr-2" title="Reset Package">
            <i class="fa fa-sync" style="{{ $row->status['slug'] == 'block' ? 'color:#ececec' :'' }}"></i>
        </a>
    @endif
   @if(in_array('child',$actions) && hasRole($module , 'show'))
        <a href="{{  ($row->status['slug'] == "block" || $row->is_subscribed == 0 ) ? "#" : route($buttons['child_user']['route'] , ['id' => $row->id]) }}" class="mr-2" title="user" style="{{ ($row->status['slug'] == 'block' || $row->is_subscribed==0 ) ? 'pointer-events:none' :'' }}">
            <i class="{{ ($row->status['slug'] == 'block' || $row->is_subscribed==0 ) ? 'fa fa-user-times' : 'fa fa-users'}} " style="{{ ($row->status['slug'] == 'block' || $row->is_subscribed==0) ? 'color:#ececec' :'' }}"></i>
        </a>
    @endif

    @if(in_array('view',$actions) && hasRole($module , 'show'))
        <a href="{{ route($buttons['view']['route'] , ['id' => $row->id]) }}" class="mr-2" title="View">
            <i class="far fa-eye"></i>
        </a>
    @endif
    @if(in_array('edit',$actions) && hasRole($module , 'edit'))
        <a href="{{ route($buttons['edit']['route'] , ['id' => $row->id]) }}" class="mr-2" title="Edit">
            <i class="fa fa-edit"></i>
        </a>
    @endif

    @if(in_array('delete',$actions) && hasRole($module , 'delete'))
        <a href="javascript:" onclick="deleteRow({{$row->id}},'{{ $module }}' , $(this))" class="mr-2" title="Delete">
            <i class="fa fa-trash-alt"></i>
        </a>
    @endif
</div>






