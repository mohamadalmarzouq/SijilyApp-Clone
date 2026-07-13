@extends('panel.master')

@section('main')

    <div class="contents pt-4 pl-4 pr mb-3">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 id="section1" class="mb-0">Dashboard</h4>
            <div class="">
                <select class="form-control" id="duration">
                     <option value="">Select Filter</option>
                     <option value="today">Today</option>
                     <option value="7">7 Days</option>
                     <option value="30">30 Days</option>
                </select>
            </div>
        </div>

        <div class="row row-xs">
            @if(count($widgets)>0)
                @foreach($widgets as $widget)
                    @switch ($widget->type)
                        @case ('counter')
                        @include('panel.widget_types.counter')
                        @break
                        @case ('flot_line_chart')
                        @include('panel.widget_types.plot_line_graph' , ['widget' => $widget] )
                        @break
                        @case ('flot_bar_chart')
                        @include('panel.widget_types.plot_bar_graph' , ['widget' => $widget] )
                        @break
                        @case ('pie_chart')
                        @include('panel.widget_types.pie_chart' , ['widget' => $widget] )
                        @break
                        @case ('single_table')
                        @include('panel.widget_types.single_table' , ['widget' => $widget] )
                        @break
                    @endswitch
                @endforeach
            @endif


        </div>

        <div class="row row-xs mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">New Subscriptions</h5>
                        <table id="new_subscriptions" class="datatable" data-route="{{ route($module.'.new_subscriptions') }}">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>No. of users</th>
                                    {{-- <th>Subscription</th> --}}

                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Consumer Retention Value</h5>
                        <table id="consumer_retentaion" class="datatable" data-route="{{ route($module.'.consumer_retention_value') }}">
                            <thead>
                                <tr>
                                    <th>Subscriber</th>
                                    <th>Organization</th>
                                    <th>No. of users</th>
                                    <th>Subscribe Since</th>
                                    <th>Amount (KWD)</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Subscriptions due for renewal in 15 Days</h5>
                        <table id="subscription_renewal" class="datatable" data-route="{{ route($module.'.subscriptions_renewal') }}">
                            <thead>
                                <tr>
                                    <th>Subscriber</th>
                                    <th>Organization</th>
                                    <th>No. of users</th>
                                    <th>Days left</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Subscriptions Sold</h5>
                        <table id="subscription_sold" class="datatable" data-route="{{ route($module.'.subscriptions_sold') }}">
                            <thead>
                                <tr>
                                    <th>Subscription</th>
                                    <th>No.</th>
                                    <th>No. of users</th>
                                    <th>Amount (KWD)</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('custom-scripts')

    <script src="{{ asset('assets/lib/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/lib/datatables.net-dt/js/dataTables.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/lib/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js') }}"></script>

    <script>
        $(function() {
            var filter = {
                limit: 5,
                duration:'years',
                search :'',
            };
            $.fn.dataTable.ext.errMode = 'none';
            var dataTable_fns = {
                init: function(filter) {
                    this.callDataTable(filter)
                },
                callDataTable(filter,changed) {
                    $(".datatable").each(function(i) {
                        console.log(i);
                        var mod = $(this).attr("id");
                        var route = $(this).attr("data-route");
                        var columns;
                        console.log({route});
                        switch (mod) {
                            case 'new_subscriptions':
                                columns = [{
                                        data: 'full_name'
                                    },
                                    {
                                        data: 'date'
                                    },
                                    {
                                        data: 'no_of_user'
                                    },
                                    // {
                                    //     data: 'subscription'
                                    // },
                                ];
                                break;
                            case 'subscription_renewal':
                                columns = [{
                                        data: 'subscriber'
                                    },
                                    {
                                        data: 'organization'
                                    },
                                    {
                                        data: 'no_of_user'
                                    },
                                    {
                                        data: 'days_left'
                                    },
                                ];
                                break;
                            case 'subscription_sold':
                                columns = [{
                                        data: 'subscription'
                                    },
                                    {
                                        data: 'no'
                                    },
                                    {
                                        data: 'no_of_users'
                                    },
                                    {
                                        data: 'total_amount'
                                    },
                                ];
                                break;
                            case 'consumer_retentaion':
                                columns = [{
                                        data: 'subscriber'
                                    },
                                    {
                                        data: 'organization'
                                    },
                                    {
                                        data: 'no_of_user'
                                    },
                                    {
                                        data: 'subscribe_since'
                                    },
                                    {
                                        data: 'values'
                                    },
                                ];
                                break;
                        }
                        var table = $(this).DataTable({
                            processing: true,
                            serverSide: true,
                            stateSave: true,
                            "paging": false,
                            "ajax": {
                                "url": route,
                                "data": function(d) {
                                    d.limit = filter.limit;
                                    d.length = 5;
                                    d.duration = filter.duration;
                                    d.search = filter.search
                                }
                            },
                            'columns': columns,
                            "fnInitComplete": function(oSettings, json) {
                                if (typeof afterDatatable == 'function') {
                                    afterDatatable();
                                }
                            }
                        });
                        $(this).css("width", "100%");

                        if(changed)
                            table.ajax.reload();
                    })
                }
            };
            dataTable_fns.init(filter,false);

            $("#duration").on('change',function(){
                var val = $(this).val();
                // if(val !==""){
                    filter.duration = val
                    dataTable_fns.callDataTable(filter,true);
                // }
            })

        });
    </script>
@endpush
