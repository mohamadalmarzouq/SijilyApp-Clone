@push('custom-head')
    <link href="{{ asset('assets/lib/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}" rel="stylesheet">
@endpush

<table id="datatable-{{ $module }}" class="table module-table">
    <thead>
        <tr>
            @foreach (json_decode($data_table_columns) as $column)
                <th>{{ ucfirst(str_replace('_', ' ', $column->name)) }}</th>
            @endforeach
        </tr>
    </thead>
</table>

@php

    $route_name_for_listing = json_decode(json_encode($route_name_for_listing), true);

    if (is_array($route_name_for_listing)) {
        $route = route($route_name_for_listing['route'], [
            $route_name_for_listing['key'] => $route_name_for_listing['value'],
        ]);
    } else {
        $route = route($route_name_for_listing);
    }

@endphp

@push('custom-scripts')
    <script src="{{ asset('assets/lib/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/lib/datatables.net-dt/js/dataTables.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/lib/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js') }}"></script>
    {{-- {{ dd($sort_colum) }} --}}
    {{-- columnDefs : {!! isset($sort_colum) ? $sort_colum : [] !!}, --}}
    <script>
        $(function() {
            let table = '';
            let module_name = "{{ $module }}"; // ✅ Fix for undefined $module

            function callDataTable(dl = '') {
                $.fn.dataTable.ext.errMode = 'none';
                let columns = {!! $data_table_columns !!};


                // 👇 Apply conditional logic only for specific module
                if (module_name == 'app_users') {
                    columns = columns.map(col => {

                        if (col.data == 'subscription.start_date' || col.data === 'subscription.expiry_date' || col.data ==="subscription.subscription.subscription" || col.data==="subscription.subscription.expiry") {
                            col.render = function(data, type, row) {
                                if (!data || !row.is_subscribed) return '<span class="text-muted">—</span>';
                                return `<span>${data}</span>`;
                            };
                        }
                        
                        return col;
                    });
                }
                table = $('#datatable-{{ $module }}').DataTable({
                    processing: true,
                    responsive: true,
                    serverSide: true,
                    stateSave: false,
                    //                "scrollX": true,
                    "ajax": {
                        "url": '{!! $route !!}',
                        "data": function(d) {
                            // console.log(dl);
                            d.id = '{!! $id !!}';
                            if (dl && $('#search').value == "") {
                                d.draw = 1;
                            }
                        }
                    },
                    // ajax: '{!! $route !!}',
                    paging: {{ isset($paging) ? $paging : 'true' }},
                    ordering: {!! isset($ordering) ? $ordering : 'false' !!},
                    columnDefs: {!! isset($sort_colum) ? $sort_colum : 'false' !!},
                    columns: columns,
                        order: [[0, 'desc']], // 0 = first column (ID), 'desc' = descending

                    "fnInitComplete": function(oSettings, json) {
                        if (typeof afterDatatable == 'function') {
                            afterDatatable();
                        }
                    }
                });
            }
            callDataTable();

            $('#search').keyup(function() {
                if (this.value == "") {
                    table.clear().destroy();
                    console.log(this.value)
                    callDataTable(1);
                } else {
                    table.search(this.value).draw();
                }
            });
        });
    </script>
@endpush
