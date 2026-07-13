@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <div class="row row-xs">
        </div>
        <div class="row row-xs mt-2">

            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div class="d-flex justify-content-between align-items-end mb-3">
                        <h3 class="tx-18">Per User</h3>
                        <div class="col-md-6 float-right">
                            <input type="text" class="form-control" id="search" placeholder="search">
                        </div>
                        <div class="col-md-2 float-right">
                           <select class="form-control" id="duration">
                                <option value="">Select Filter</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="7days">7 Days</option>
                                <option value="30days">30 Days</option>
                            </select>
                        </div>
                        <div class="col-md-2 float-right">
                            <select class="form-control" id="packages">
                                 <option value="">Select Package</option>
                                 @foreach($packages as $package)
                                     <option value="{{$package->id}}">{{$package->subscription}}</option>
                                 @endforeach
                             </select>
                         </div>
                    </div>
                    <table id="per_user" class="datatable" data-route="{{ route($module.'.per_user_ajax') }}">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subscriber</th>
                                <th>Organization</th>
                                <th>Package</th>
                                <th>Billed Till Date (KWD)</th>
                            </tr>
                        </thead>
                    </table>
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

    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.1.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.print.min.js"></script>

    <script>
        $(function() {

            var oldExportAction = function (self, e, dt, button, config) {
            if (button[0].className.indexOf('buttons-excel') >= 0) {
                    if ($.fn.dataTable.ext.buttons.excelHtml5.available(dt, config)) {
                        $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config);
                    }
                    else {
                        $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
                    }
                }else if (button[0].className.indexOf('buttons-print') >= 0) {
                    $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
                }
            };

            var newExportAction = function (e, dt, button, config) {
            var self = this;
            var oldStart = dt.settings()[0]._iDisplayStart;

                dt.one('preXhr', function (e, s, data) {
                    // Just this once, load all data from the server...
                    data.start = 0;
                    data.length = 2147483647;

                    dt.one('preDraw', function (e, settings) {
                        // Call the original action function
                        oldExportAction(self, e, dt, button, config);

                        dt.one('preXhr', function (e, s, data) {
                            // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                            // Set the property to what it was before exporting.
                            settings._iDisplayStart = oldStart;
                            data.start = oldStart;
                        });

                        // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
                        setTimeout(dt.ajax.reload, 0);

                        // Prevent rendering of the full data to the DOM
                        return false;
                    });
                });

                // Requery the server with the new one-time export settings
                dt.ajax.reload();
            };

            var filter = {
                limit: 0,
                duration:'years',
                searching:'',
                package:'',
            };
            $.fn.dataTable.ext.errMode = 'none';
            var dataTable_fns = {
                init: function(filter) {
                    this.callDataTable(filter)
                },
                callDataTable(filter, changed) {
    var route = $("#per_user").attr("data-route");
    var columns = [
        { data: 'id' },
        { data: 'subscriber' },
        { data: 'organization' },
        { data: 'package' },
        { data: 'bill_till_date' },
    ];

    if ($.fn.DataTable.isDataTable("#per_user")) {
        $("#per_user").DataTable().destroy();
    }

    var table = $("#per_user").DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', action: newExportAction },
            'print'
        ],
        processing: true,
        serverSide: true,
        stateSave: false,
        paging: true,
        ajax: {
            url: route,
            data: function(d) {
                d.limit = filter.limit;
                d.duration = filter.duration;
                d.searching = filter.searching;
                d.package = filter.package;
            }
        },
        columns: columns,
        order: [[0, 'desc']], // ✅ Default order by ID descending
        columnDefs: [
            { targets: 0, orderable: true },
            { targets: '_all', orderable: true }
        ],
        fnInitComplete: function(oSettings, json) {
            if (typeof afterDatatable == 'function') {
                afterDatatable();
            }
        }
    });
    $("#per_user").css("width", "100%");

    if (changed)
        table.ajax.reload();
}
                // callDataTable(filter,changed) {
                //         var route = $("#per_user").attr("data-route");
                //         var columns = [{
                //                         data: 'id'
                //                     },
                //                     {
                //                         data: 'subscriber'
                //                     },
                //                     {
                //                         data: 'organization'
                //                     },
                //                     {
                //                         data: 'package'
                //                     },
                //                     {
                //                         data: 'bill_till_date'
                //                     },
                //                 ];

                //         var table = $("#per_user").DataTable({
                //             dom: 'Bfrtip',
                //             buttons: [
                //                 {
                //                     extend:'excel',
                //                     action: newExportAction,
                //                 },
                //                 'print',
                //             ],
                //             processing: true,
                //             serverSide: true,
                //             stateSave: true,
                //             "paging": true,
                //             "ajax": {
                //                 "url": route,
                //                 "data": function(d) {
                //                     d.limit = filter.limit;
                //                     d.duration = filter.duration
                //                     d.searching = filter.searching
                //                     d.package = filter.package
                //                 }
                //             },

                //             'columns': columns,
                //             order: [[0, 'desc']],
                //                 // ✅ Ensure only the ID column (0) has forced ordering behavior
                //             columnDefs: [
                //                 { targets: 0, orderable: true },   // ID column (sortable)
                //                 { targets: '_all', orderable: true } // keep others same behavior
                //             ],
                //             "fnInitComplete": function(oSettings, json) {
                //                 if (typeof afterDatatable == 'function') {
                //                     afterDatatable();
                //                 }
                //             }
                //         });
                //         $("#per_user").css("width", "100%");

                //         if(changed)
                //             table.ajax.reload();

                // }
            };
            dataTable_fns.init(filter,false);

            $("#duration").on("change",function(){
                var val = $(this).val();
                // if(val !==""){
                    filter.duration = val
                    dataTable_fns.callDataTable(filter,true);
                // }
             })

             $("#packages").on("change",function(){
                var val = $(this).val();
                if(val !==""){
                    filter.package = val
                    dataTable_fns.callDataTable(filter,true);
                }

             })

            $("#search").on('keyup',function(){
                var val = $(this).val();
                filter.searching = val
                dataTable_fns.callDataTable(filter,true);
            })
        });
    </script>
@endpush

