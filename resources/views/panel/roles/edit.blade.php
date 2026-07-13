@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <h3 class="tx-18">Edit {{ setText($module,true) }}</h3>
                    <div>
                        <form method="POST" action="{{ route($module.'.edit' , ['id' => $data->id]) }}">
                            @csrf
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="" class="mb-1">Name</label>
                                    <input type="text" name="name" id="name" required class="form-control" placeholder="Name"
                                           value="{{ $data->name }}">
                                </div>
                            </div>
                            <div class="form-group pt-2">
                                @include('panel.permissions.view')
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class=" text-right">
                                        <button class="btn btn-primary btn-rounded btn-lg float-right" type="submit">
                                            Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

