<div class="col-lg-8 col-xl-7 mg-t-10">
    <div class="card set-min-height">
        <div class="card-header pd-y-8 pb-0 d-sm-flex flex-column align-items-start justify-content-between">
            <h6 class="mg-b-0">{{ $widget->title }}</h6>
            @if(isset($widget->graph_data->nav_data))
                <nav class="nav nav-line border-0">
                    @foreach($widget->graph_data->nav_data as $index =>$data)
                        <a id="nav-{{ $data->value }}" href="javascript:;" onclick="getGraphData('{{ $data->value }}');"
                           class="nav-link {{ $data->value == $widget->graph_data->selected_nav_value ? 'active' : '' }}">{{ $data->value }}</a>
                    @endforeach
                </nav>
            @endif
        </div>
        <div class="card-body pos-relative pd-0">
            <div class="pos-absolute t-20 l-20 wd-xl-100p z-index-10">
                <div class="row">
                    <div class="col-sm-5">
                        <h3 id="total_value"
                            class="tx-normal tx-rubik tx-spacing--2 mg-b-5 mt-minus-15">{{ $widget->graph_data->total_value }}
                            <span
                                class="tx-12">USD</span></h3>
                        <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-10">
                            {{ $widget->graph_data->header }}</h6>
                    </div><!-- col -->
                </div><!-- row -->
            </div>

            <div class="chart-one pt-5">
                <div class="flot-chart" style="padding: 0px; position: relative;">
                    <canvas id="lineGraph-{{ $widget->id }}" class="flot-base" width="640" height="350"
                            style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 640.828px; height: 350px;"></canvas>

                </div>
            </div><!-- chart-one -->
        </div><!-- card-body -->
    </div><!-- card -->
</div>

@push('custom-scripts')
    <script>
        let line_chart;
        ctx_line_graph = document.getElementById('lineGraph-{{ $widget->id }}');

        let item_data = [];
        let y_axis_data = [];
        @foreach($widget->graph_data->data as $item)
        item_data.push({{ $item->value }});
        y_axis_data.push('{{ $item->label }}');
        @endforeach
        createChart(item_data, y_axis_data);

        function createChart(item_data, y_axis_data) {

            line_chart = new Chart(ctx_line_graph, {
                type: 'line',
                data: {
                    datasets: [{
                        label: '{{ $widget->graph_data->label }}',
                        data: item_data,
                        backgroundColor: ["#69b2f8"],
                        fill: true,
                        borderColor: '#0074D9'
                    }],
                    labels: y_axis_data
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        }

        function getGraphData(value) {

            let requested_url = base_url + '/{{ $widget->module }}s-get-graph-data/' + value;

            $.get(requested_url).done(function (data) {
                data = JSON.parse(data);

                $('#total_value').html(data.total_value + ' <span class="tx-12">USD</span>');

                $('.nav-line a.active').removeClass('active');

                $('#nav-' + value).addClass('active');

                line_chart.destroy();
                let item_data_array = [];
                let y_axis_data_array = [];

                $.each(data.data, function (index, value) {

                    item_data_array.push(value.value);
                    y_axis_data_array.push(value.label);

                });
                createChart(item_data_array, y_axis_data_array);
            }).fail(function (error) {
                console.log(error);
            });
        }
    </script>
@endpush
