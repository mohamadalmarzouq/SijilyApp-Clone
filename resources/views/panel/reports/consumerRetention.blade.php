@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <div class="row row-xs">
        </div>
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div class="d-flex justify-content-between align-items-end mb-3">
                        <h3 class="tx-18">Consumer Retention</h3>
                        <div class="col-md-6 float-right">
                            <input type="text" id="search" class="form-control" placeholder="search">
                        </div>
                        <div class="col-md-2 float-right">
                            <input type="text" id="date" class="form-control" placeholder="Date">
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
                    </div>
                    <table id="consumer_retentaion" class="datatable"
                        data-route="{{ route($module . '.consumer_retention_value_ajax') }}">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>Subscriber</th>
                                <th>Organization</th>
                                <th>Subscribe Since</th>
                                <th>No. of users</th>
                                <th>Amount (KWD)</th>
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
            var oldExportAction = function(self, e, dt, button, config) {
                if (button[0].className.indexOf('buttons-excel') >= 0) {
                    if ($.fn.dataTable.ext.buttons.excelHtml5.available(dt, config)) {
                        $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config);
                    } else {
                        $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
                    }
                } else if (button[0].className.indexOf('buttons-print') >= 0) {
                    $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
                }
            };

            var newExportAction = function(e, dt, button, config) {
                var self = this;
                var oldStart = dt.settings()[0]._iDisplayStart;

                dt.one('preXhr', function(e, s, data) {
                    // Just this once, load all data from the server...
                    data.start = 0;
                    data.length = 2147483647;

                    dt.one('preDraw', function(e, settings) {
                        // Call the original action function
                        oldExportAction(self, e, dt, button, config);

                        dt.one('preXhr', function(e, s, data) {
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
                duration: 'years',
                date: '',
                search: '',
            };
            $.fn.dataTable.ext.errMode = 'none';
            var dataTable_fns = {
                init: function(filter) {
                    this.callDataTable(filter)
                },
                callDataTable(filter, changed) {
                    var route = $("#consumer_retentaion").attr("data-route");
                    var columns = [{
                            data: 'id',
                            orderable: true
                        },
                        {
                            data: 'subscriber',
                            orderable: true
                        },
                        {
                            data: 'organization',
                            orderable: true
                        },

                        {
                            data: 'subscribe_since',
                            orderable: true
                        },
                        {
                            data: 'no_of_user',
                            orderable: false

                        },
                        {
                            data: 'values'
                        },
                    ];
                    if ($.fn.DataTable.isDataTable("#consumer_retentaion")) {
                        $("#consumer_retentaion").DataTable().destroy();
                    }
                    var table = $("#consumer_retentaion").DataTable({
                        dom: 'Bfrtip',
                        buttons: [{
                                extend: 'excel',
                                action: newExportAction,
                            },
                            'print'
                        ],
                        processing: true,
                        serverSide: true,
                        // stateSave: true,
                        paging: true,
                        ajax: {
                            url: route,
                            data: function(d) {
                                d.limit = filter.limit;
                                d.duration = filter.duration;
                                d.search = filter.search;
                                d.date = filter.date;
                            }
                        },
                        order: [
                            [0, 'desc']
                        ], // ✅ Default order by first column (id) descending
                        columnDefs: [{
                                targets: 0,
                                orderable: true
                            },
                            {
                                targets: '_all',
                                orderable: true
                            }
                        ],
                        columns: columns,
                        fnInitComplete: function(oSettings, json) {
                            if (typeof afterDatatable == 'function') {
                                afterDatatable();
                            }
                        }
                    });

                    $("#consumer_retentaion").css("width", "100%");

                    if (changed)
                        table.ajax.reload();

                }
            };
            dataTable_fns.init(filter, false);

            $("#duration").on("change", function() {
                var val = $(this).val();
                // if(val !==""){
                filter.duration = val
                dataTable_fns.callDataTable(filter, true);
                // }
            })

            $("#search").on('keyup', function() {
                var val = $(this).val();
                console.log(val);
                filter.search = val
                dataTable_fns.callDataTable(filter, true);
            })

            $('#date').datepicker({
                autoHide: true,
                format: 'yyyy-mm-dd'
            }).on('pick.datepicker', function(e) {
                var date = moment(e.date).format("YYYY-MM-DD");
                filter.date = date;
                dataTable_fns.callDataTable(filter, true);
            });
        });
    </script>
@endpush
