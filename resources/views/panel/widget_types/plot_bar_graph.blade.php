<div class="{{ $widget->class }}">
    <div class="card {{ $widget->graph_data->class }}">
        <div class="card-header pd-y-8 pb-0 d-sm-flex flex-column align-items-start justify-content-between">
            <h6 class="mg-b-0">{{ $widget->title }}</h6>
            @if(isset($widget->graph_data->nav_data))
                <nav class="nav nav-line border-0">
                    @foreach($widget->graph_data->nav_data as $data)
                        <a id="nav-{{ $data->tab_value }}" href="javascript:;"
                           onclick="getGraphData{{ $widget->id }}('{{ $data->tab_value }}');"
                           class="nav-link {{ $data == $widget->graph_data->selected_nav_value ? 'active' : '' }}">
                            {{ $data->tab_title }}
                        </a>
                    @endforeach
                </nav>
            @endif
        </div>
        <div class="card-body pd-20">
            <div class="chart-two mg-b-20">
                <div id="flotChart2" class="flot-chart" style="padding: 0px; position: relative;">
                    <div id="chartContainer-{{ $widget->id }}"
                         style="height: {{ $widget->graph_data->height }}px; width: {{ $widget->graph_data->width }}px;"></div>
                </div>
            </div><!-- chart-two -->
            @if(isset($widget->graph_data->bottom_data))
                <div class="row" style="padding-top: 30px">
                    <div class="col-sm mg-t-20 mg-sm-t-0">
                        <h4 class="tx-normal tx-rubik tx-spacing--1 mg-b-5">{{ addCommaForNumeric($widget->graph_data->bottom_data->value2) }}
                        </h4>
                        <p class="tx-11 tx-uppercase tx-spacing-1 tx-semibold mg-b-0 tx-primary" >
                            Revenues</p>
                    </div><!-- col -->
                    <div class="col-sm">
                        <h4 class="tx-normal tx-rubik tx-spacing--1 mg-b-5"> {{ addCommaForNumeric($widget->graph_data->bottom_data->value1) }}
                        </h4>
                        <p class="tx-11 tx-uppercase tx-spacing-1 tx-semibold mg-b-0" style="color: red">
                            Expenses</p>
                    </div><!-- col -->

                </div><!-- row -->
            @endif
        </div><!-- card-body -->
    </div><!-- card -->
</div>

@push('custom-scripts')
    <script src="{{ asset('assets/js/canvasjs.min.js') }}"></script>
    <script type="text/javascript">

        var chart_data_{{ $widget->id }} = [];

        $(function () {
            createChart{{ $widget->id }}({!! json_encode($widget->graph_data->graph_data) !!})
        });

        function createChart{{ $widget->id }}(data) {

            chart_data_{{ $widget->id }} = data;

            window['chart_{{ $widget->id }}'] = new CanvasJS.Chart("chartContainer-{{ $widget->id }}", {
                theme: "light1",
                animationEnabled: true,
                toolTip: {
                    shared: true,
                    reversed: true
                },
                legend: {
                    cursor: "pointer",
                    itemclick: toggleDataSeries{{ $widget->id }}
                },
                data: chart_data_{{ $widget->id }}

            });

            window['chart_{{ $widget->id }}'].render();

            function toggleDataSeries{{ $widget->id }}(e) {
                if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                    e.dataSeries.visible = false;
                } else {
                    e.dataSeries.visible = true;
                }
                e.chart.render();
            }

        }

        function getGraphData{{ $widget->id }}(value) {

            let requested_url = base_url + '/{{ $widget->graph_data->route_for_search }}/' + value;

            $.get(requested_url).done(function (data) {

                data = JSON.parse(data);

                $('.nav-line a.active').removeClass('active');

                $('#nav-' + value).addClass('active');

                chart_data_{{ $widget->id }} = [];

                $.each(data.graph_data, function (index, value) {

                    chart_data_{{ $widget->id }}.push(value);

                });

                createChart{{ $widget->id }}(chart_data_{{ $widget->id }});

            }).fail(function (error) {
                console.log(error);
            });
        }

    </script>
@endpush
