@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">Add User</h3>
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div>

                        <div id="errorMsg" class="alert alert-danger d-none"></div>
                        <form method="POST" action="{{ route($module.'.create_user') }}" id="addForm">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Full Name</label>
                                        <input name="full_name" type="text" class="form-control" value="">
                                        <input name="user_id" type="hidden" class="form-control" value="{{ $id }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Email ID</label>
                                        <input  name="email" type="text" class="form-control" value="">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Role</label>
                                        <select name="role_id" class="form-control">
                                           <option value="">Select Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                 <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Password</label>
                                        <input name="password" type="password" class="form-control" value="">
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary float-right" type="submit">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

