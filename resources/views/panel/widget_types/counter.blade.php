@if($widget->title !="Target")
<div class="d-flex {{ $widget->class }}">
    <div class="card card-body set_width">
        <div class="d-flex d-lg-block d-xl-flex align-items-end mb-2">
            @isset($widget->query[0])
                <h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1">{{ $widget->query[0]->value ? $widget->query[0]->value : 0 }} KWD</h3>
            @endisset
        </div>
        <h6 class="tx-uppercase tx-12 tx-spacing-1 tx-color-02 tx-semibold mb-0">{{ $widget->title }}</h6>
    </div>
</div>
@endif
