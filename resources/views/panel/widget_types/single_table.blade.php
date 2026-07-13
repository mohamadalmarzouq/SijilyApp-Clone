<div class="{{ $widget->class }}">
    <div class="col-lg-12 col-xl-12 mg-t-10">
        <div class="card">
            <div class="card-header pd-y-20 d-md-flex align-items-start flex-column justify-content-between">
                <h6 class="mg-b-0">{{ $widget->title }}</h6>
                @if(isset($widget->sub_title))
                    <p class="tx-12 tx-color-03 mg-b-0">{{ $widget->sub_title }}</p>
                @endif
                @isset($widget->listing_data->filter_available)
                    <form method="GET" action="{{ route($widget->listing_data->module.'search') }}">
                        <div class="d-flex">
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                   placeholder="Start Date">
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                   placeholder="End Date">
                        </div>
                        <button type="submit">Search</button>
                    </form>
                @endisset
            </div>

            <div class="card-body pos-relative pd-2">
                @isset($widget->listing_data)
                    @include('panel.includes.datatable' , [
                 'data_table_columns' => $widget->listing_data->data_table_columns ,
                 'route_name_for_listing' => $widget->listing_data->route_name_for_listing ,
                 'module' => $widget->listing_data->module,
                 'ordering' => isset($widget->listing_data->ordering) ? $widget->listing_data->ordering : 'false'])
                @endisset

            </div>
        </div>
    </div>
</div>
