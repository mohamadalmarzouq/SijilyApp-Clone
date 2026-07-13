@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">View {{ setText($module,true) }}</h3>
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div>

                        <form method="POST" action="{{ route($module.'.edit',['id' => $data->id]) }}" id="editForm">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Name</label>
                                        <input disabled type="text" class="form-control" value="{{ $data->name }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Email</label>
                                        <input disabled type="text" class="form-control" value="{{ $data->email }}">
                                    </div>
                                </div>
                                {{-- <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Password</label>
                                        <select name="role_id" class="form-control" disabled>
                                            <option value="">Select Role</option>
                                            @foreach($roles as $role)
                                                @if($role['id'] !=1)
                                                    <option value="{{ $role['id'] }}" {{ $data['role_id'] == $role['id'] ? "selected" :"" }}>{{ $role['name'] }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div> --}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

