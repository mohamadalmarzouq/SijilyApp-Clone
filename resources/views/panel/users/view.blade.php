@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <h3 class="tx-18">View {{ setText($module,true) }}</h3>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group pt-2">
                                <label for="inputEmail"><h6>Name</h6></label>
                                <p>{{ $data->name }}</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group pt-2">
                                <label for="inputEmail"><h6>Email</h6></label>
                                <p>{{ $data->email }}</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group pt-2">
                                <label for="inputEmail"><h6>Role</h6></label>
                                <p>{{ $data->role->name }}</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group pt-2">
                                <label for="inputEmail"><h6>Address</h6></label>
                                <p>{{ $data->address }}</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group pt-2">
                                <label for="inputEmail"><h6>Contact</h6></label>
                                <p>{{ $data->contact }}</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group pt-2">
                                <label for="inputEmail"><h6>Status</h6></label>
                                <p>{!! $data->user_status !!}</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group pt-2 avatar avatar-xxl avatar-online">
                                <label for="inputEmail"><h6>Photo</h6></label>
                                <p><img  src="{{ getUserAvatar($data->id) }}"></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

