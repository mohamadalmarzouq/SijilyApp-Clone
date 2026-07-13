@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <h3 class="tx-18">Edit {{ setText($module,true) }}</h3>
                    <div>
                        <form method="POST" action="{{ route($module.'.edit',['id' => $data->id]) }}" id="editForm">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Name</label>
                                        <input type="text" name="name" id="name" class="form-control" placeholder="Name" value="{{ $data->name }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="" class="mb-1">User Role</label>
                                    <select class="custom-select mr-0 font-weight-500" name="role_id"
                                            id="role_id">
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option {{ $data->role_id == $role->id ? 'selected' : ''}}
                                                value="{{ $role->id }}">
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Email ID</label>
                                        <input type="email" name="email" id="email" class="form-control" placeholder="Email ID" value="{{ $data->email }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <label for="" class="mb-1">Status</label>
                                    <select class="custom-select mr-0 font-weight-500" name="user_status_id"
                                            id="user_status_id">
                                        @foreach($statuses as $status)
                                            <option {{ $data->user_status_id == $status->id ? 'selected' : ''}}
                                                value="{{ $status->id }}">
                                                {{ $status->status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <button class="btn btn-primary float-right" type="submit">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

