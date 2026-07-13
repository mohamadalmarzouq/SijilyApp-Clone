@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div class="d-flex justify-content-between align-items-end mb-3">
                        <h3 class="tx-18">{{ setText($module) }} Management</h3>
                    </div>
                    {{-- @include('panel.includes.datatable') --}}
                </div>
            </div>
        </div>
        <div class="row row-xs mt-2">
            <div class="col-sm-4">
                <a href="{{url('get-faqs')}}">
                    <div class="card card-body">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h3 class="tx-18"><i class="fa fa-question mr-2"></i>Faq's Management</h3>
                        </div>

                    </div>
                </a>
            </div>
            <div class="col-sm-4">
                <a href="{{url('get-pages')}}">
                    <div class="card card-body">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h3 class="tx-18"><i class="fa fa-file mr-2"></i>Pages Management</h3>
                        </div>

                    </div>
                </a>
            </div>
            <div class="col-sm-4">
                <a href="{{url('get-videos')}}">
                    <div class="card card-body">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h3 class="tx-18"><i class="fa fa-video mr-2"></i>Videos Management</h3>
                        </div>

                    </div>
                </a>
            </div>
        </div>
    </div>

@endsection
