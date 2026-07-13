@push('custom-head')
    <link rel="stylesheet" href="{{ asset('assets/css/Chart.css') }}" type="text/css"/>
@endpush

<div class="col-lg-4 col-xl-5 mg-t-10">
    <div class="card set-min-height">
        <div class="card-header">
            <h6 class="mg-b-0">{{ $widget->title }}</h6>
        </div><!-- card-header -->
        <div class="card-body pd-lg-25">
            <div id="vmap" class="ht-200"
                 style="position: relative; overflow: hidden; background-color: rgb(255, 255, 255);">

                    <canvas id="myChart-{{ $widget->id }}" width="200" height="200"></canvas>

            </div>
            <div class="card-footer mt-4 pd-0 border-0">
                <div class="row pl-2 pr-2 mb-3">
                    <div class="media align-items-center">
                        <div class="wd-45 ht-45 bg-gray-900 set_icon_color rounded d-flex align-items-center justify-content-center">
                            {{--<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"--}}
                                 {{--fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"--}}
                                 {{--stroke-linejoin="round" class="feather feather-github tx-white-7 wd-20 ht-20">--}}
                                {{--<path--}}
                                    {{--d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>--}}
                            {{--</svg>--}}
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="media-body pd-l-10">
                            <h6 class="tx-color-01 mg-b-3 tx-20">{{ $widget->graph_data->total_properties->data }}</h6>
                            <p class="tx-12 mg-b-0">TOTAL PROPERTIES</p>
                        </div>
                    </div>
                </div><!-- row -->
                <div class="row pr-2 pl-2 justify-content-between">
                    <div>
                        <h3 class="tx-normal tx-rubik tx-spacing--2 mg-b-5 mt-0 tx-20"> {{ $widget->graph_data->total_units->data }} </h3>
                        <h6 class="tx-uppercase tx-12 tx-color-02 tx-semibold mg-b-10"> TOTAL UNITS </h6>
                    </div>
                    <div>
                        <h3 class="tx-normal tx-rubik tx-spacing--2 mg-b-5 mt-0 tx-20"> {{ $widget->graph_data->vacant_units->data }} </h3>
                        <h6 class="tx-uppercase tx-12 tx-color-02 tx-semibold mg-b-10"> TOTAL VACANT
                            UNITS </h6>
                    </div>
                    <div>
                        <h3 class="tx-normal tx-rubik tx-spacing--2 mg-b-5 mt-0 tx-20"> {{ $widget->graph_data->occupied_units->data }} </h3>
                        <h6 class="tx-uppercase tx-12 tx-color-02 tx-semibold mg-b-10"> TOTAL OCCUPIED
                            UNITS </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('custom-scripts')
    <script src="{{ asset('assets/js/Chart.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/Chart.bundle.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">

        ctx = document.getElementById('myChart-{{ $widget->id }}').getContext('2d');
        var item = [];
        var y = [];

        item.push('{{ $widget->graph_data->vacant_units->data }}');
        y.push('{{ $widget->graph_data->vacant_units->title }}');

        item.push('{{ $widget->graph_data->occupied_units->data }}');
        y.push('{{ $widget->graph_data->occupied_units->title }}');

        chart = new Chart(ctx, {
            type: 'pie',
            data: {
                datasets: [{
                    label: 'Colors',
                    data: item,
                    backgroundColor: ["#d1e6fa", "#69b2f8"]
                }],
                labels: y
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });

    </script>

@endpush
