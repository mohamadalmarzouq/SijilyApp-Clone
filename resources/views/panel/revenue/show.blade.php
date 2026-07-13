@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <div class="row">
            <div class="col-md-10">
                <h3>Revenue</h3>
            </div>
            <div class="col-md-2">
                <select class="form-control" id="duration">
                    <option value="">Select Filter</option>
                    <option value="clearAll">All Time</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="7days">7 Days</option>
                    <option value="30days">30 Days</option>
                </select>
            </div>
        </div>
         {{-- Per User --}}
        <div class="row row-xs mt-2">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Per User</h5>
                        <table id="per_user" class="datatable" data-route="{{ route($module.'.per_user') }}">
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
                        <div class="col-md-12 text-center mt-3">
                            <a href="reports-per_user" class="btn btn-success" id="per_user_btn">View Report</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div style="text-align: right; margin-bottom:13px">
                            <button class="btn btn-primary" id="getPerUserChartPrintBtn">Print Chart</button>
                        </div>
                        <div id="getPerUserChart" style="height: 370px; width: 100%;"></div>
                        <canvas id="hiddenCanvas" style="display: none;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        {{-- Per Subscription --}}
        <div class="row row-xs mt-2">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                         <h5 class="card-title">Per Subscription</h5>
                        <table id="per_subscription" class="datatable" data-route="{{ route($module.'.per_subscription') }}">
                            <thead>
                                <tr>
                                    <th>Subscription Pack</th>
                                    <th>No. of users</th>
                                    <th>Amount (KWD)</th>
                                </tr>
                            </thead>
                        </table>
                       <div class="col-md-12 text-center mt-3"><a href="reports-per_subscription" class="btn btn-success" id="per_subs_btn">View Report</a></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div style="text-align: right; margin-bottom:13px">
                                <button class="btn btn-primary" id="getPerSubscriptionChartPrintBtn">Print Chart</button>
                        </div>
                        <div id="getPerSubscriptionChart" style="height: 370px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Abandoned --}}
        <div class="row row-xs mt-2">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                         <h5 class="card-title">Abandoned</h5>
                        <table id="abandoned" class="datatable" data-route="{{ route($module.'.abandoned') }}">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subscriber</th>
                                    <th>Organization</th>
                                    <th>Package</th>
                                    <th>Lost Date</th>
                                </tr>
                            </thead>
                        </table>
                       <div class="col-md-12 text-center mt-3"><a href="reports-abandoned" id="abandoned_btn" class="btn btn-success">View Report</a></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div style="text-align: right; margin-bottom:13px">
                            <button class="btn btn-primary" id="getAbandonedChartPrintBtn">Print Chart</button>
                        </div>
                        <div id="getAbandonedChart" style="height: 370px; width: 100%;"></div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>

    <script>

        $(function() {
             var filter = {
                limit: 5,
                duration:'year'
            };
            jQuery.extend({
                getValues: function(url,filter) {
                    filter = $.param(filter);
                    var result = null;
                    $.ajax({
                        url: url,
                        type: 'get',
                        data:filter,
                        dataType: 'json',
                        async: false,
                        success: function(data) {
                            result = data;
                        }
                    });
                return result;
                }
            });
            $.fn.dataTable.ext.errMode = 'none';
            var dataTable_fns = {
                init: function(filter) {
                    this.callDataTable(filter,false)
                },
                callDataTable:function(filter,changed) {
                    $(".datatable").each(function() {
                        var mod = $(this).attr("id");
                        var route = $(this).attr("data-route");
                        var columns;
                        switch (mod) {
                            case 'per_user':
                                columns = [{
                                      data: 'id'
                                    },
                                    {
                                      data: 'subscriber'
                                    },
                                    {
                                        data: 'organization'
                                    },
                                    {
                                        data: 'package'
                                    },
                                    {
                                        data: 'bill_till_date'
                                    },
                                ];
                                break;
                            case 'per_subscription':
                                columns = [{
                                        data: 'subscription'
                                    },
                                    {
                                        data: 'no_of_user'
                                    },
                                    {
                                        data: 'total_amount'
                                    },
                                ];
                                break;
                            case 'abandoned':
                                columns = [{
                                      data: 'id'
                                    },
                                    {
                                      data: 'subscriber'
                                    },
                                    {
                                        data: 'organization'
                                    },
                                    {
                                        data: 'package'
                                    },
                                    {
                                        data: 'expiry'
                                    },
                                ];
                                break;

                        }
                        var table = $(this).DataTable({
                            processing: true,
                            serverSide: true,
                            stateSave: false,
                            "paging": false,
                            "ajax": {
                                "url": route,
                                "data": function(d) {
                                    d.limit = filter.limit;
                                    d.length = 5;
                                    d.duration = filter.duration
                                    d.revenue = true

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

                },
                getPerUserChart:function(filter){
                    var url = '{{ route($module.".per_user_chart") }}';
                    var result = $.getValues(url,filter);
                    if(result.length < 1){
                        $("#per_user_btn").css('pointer-events','none')
                    }
                    var chart = new CanvasJS.Chart("getPerUserChart", {
                        exportEnabled: true,
                        animationEnabled: true,
                        title:{
                            text: "Latest Revenue - Per User",
                        },

                        toolTip: {
                            shared: true
                        },
                        legend:{
                            cursor: "pointer",
                            fontFamily: '"Helvetica Neue",Helvetica,Arial,sans-serif',
                            fontSize:12,
                        },
                        data: [
                        {
                            indexLabelFontSize: 12,
                            type: "pie",
                            showInLegend: true,
                            fontFamily: '"IBM Plex Sans", sans-serif',
                            name: "Users",
                            indexLabel: "{name} {y}",
                            yValueFormatString: "#,##0.#",
                            dataPoints: result,
                        }]
                    });


                    chart.render();
                    $("#getPerUserChartPrintBtn").on("click", function() {
                        var canvas1 = document.getElementById('getPerUserChart').getElementsByTagName('canvas')[0];
                        var dataUrl1 = canvas1.toDataURL(); // Get the data URL of the canvas
                        var newWindow1 = window.open();
                        newWindow1.document.write('<img src="' + dataUrl1 + '" width="100%">');
                        setTimeout(function() {
                            newWindow1.print(); // Print the image
                            newWindow1.close(); // Close the new window after printing
                        }, 800); // Delay printing by 1 second
                    });

                },
                getPerSubscriptionChart:function(filter){
                    var url = '{{ route($module.".subscription_chart") }}';
                    var result = $.getValues(url,filter);
                    if(result.length < 1){
                        $("#per_subs_btn").css('pointer-events','none')
                    }
                    var chart = new CanvasJS.Chart("getPerSubscriptionChart", {
                        exportEnabled: true,
                        animationEnabled: true,
                        title:{
                            text: "Latest Revenue - Per Subscription",
                            fontFamily: '"IBM Plex Sans", sans-serif',
                        },
                        axisY: {
                            title: "Oil Filter - Units",
                            titleFontColor: "#4F81BC",
                            lineColor: "#4F81BC",
                            labelFontColor: "#4F81BC",
                            tickColor: "#4F81BC",
                            includeZero: true
                        },
                        toolTip: {
                            shared: true
                        },
                        legend:{
                            cursor: "pointer",
                            fontFamily: '"Helvetica Neue",Helvetica,Arial,sans-serif',
                            fontSize:12,
                        },
                        data: [
                        {
                            indexLabelFontSize: 12,
                            type: "pie",
                            showInLegend: true,
                            name: "Users",
                            indexLabel: "{name} {y}",
                            yValueFormatString: "#,##0.#",
                            dataPoints: result
                        }]
                    });


                    chart.render();
                    $("#getPerSubscriptionChartPrintBtn").on("click", function() {
                        var canvas2 = document.getElementById('getPerSubscriptionChart').getElementsByTagName('canvas')[0];
                        var dataUrl2 = canvas2.toDataURL(); // Get the data URL of the canvas
                        var newWindow2 = window.open();
                        newWindow2.document.write('<img src="' + dataUrl2 + '" width="100%">');
                        setTimeout(function() {
                            newWindow2.print(); // Print the image
                            newWindow2.close(); // Close the new window after printing
                        }, 800); // Delay printing by 1 second
                    });
                },
                getAbandonedChart:function(filter){
                    var url = '{{ route($module.".abandoned_chart") }}';
                    var result = $.getValues(url,filter);
                    if(result.length < 1){
                        $("#abandoned_btn").css('pointer-events','none')
                    }
                    var chart = new CanvasJS.Chart("getAbandonedChart", {
                        exportEnabled: true,
                        animationEnabled: true,
                        title:{
                            text: "Latest Abandoned",
                            fontFamily: '"IBM Plex Sans", sans-serif',
                        },
                        axisY: {
                            title: "Oil Filter - Units",
                            titleFontColor: "#4F81BC",
                            lineColor: "#4F81BC",
                            labelFontColor: "#4F81BC",
                            tickColor: "#4F81BC",
                            includeZero: true
                        },
                        toolTip: {
                            shared: true
                        },
                        legend:{
                            cursor: "pointer",
                            fontFamily: '"Helvetica Neue",Helvetica,Arial,sans-serif',
                            fontSize:12,
                        },
                        data: [
                        {
                            indexLabelFontSize: 12,
                            type: "pie",
                            showInLegend: true,
                            name: "Users",
                            indexLabel: "{name} {y}",
                            yValueFormatString: "#,##0.#",
                            dataPoints: result
                        }]
                    });
                    chart.render();
                    $("#getAbandonedChartPrintBtn").on("click", function() {
                        var canvas3 = document.getElementById('getAbandonedChart').getElementsByTagName('canvas')[0];
                        var dataUrl3 = canvas3.toDataURL(); // Get the data URL of the canvas
                        var newWindow3 = window.open();
                        newWindow3.document.write('<img src="' + dataUrl3 + '" width="100%">');
                        setTimeout(function() {
                            newWindow3.print(); // Print the image
                            newWindow3.close(); // Close the new window after printing
                        }, 800); // Delay printing by 1 second
                    });
                }
            };
            dataTable_fns.init(filter);
            dataTable_fns.getPerUserChart(filter);
            dataTable_fns.getPerSubscriptionChart(filter);
            dataTable_fns.getAbandonedChart(filter);
            $("#duration").on('change',function(){
            var val = $(this).val();
                // if(val !==""){
                    if(val == 'clearAll')
                    {
                        filter.duration = 'year';
                    }
                    else
                    {
                        filter.duration = val
                    }
                    dataTable_fns.callDataTable(filter,true);
                    dataTable_fns.init(filter);
                    dataTable_fns.getPerUserChart(filter);
                    dataTable_fns.getPerSubscriptionChart(filter);
                    dataTable_fns.getAbandonedChart(filter);
                // }
            })
        });
    </script>
    <style>
        .dataTables_info {
            display: none;
        }
    </style>
@endpush
