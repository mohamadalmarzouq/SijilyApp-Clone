@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">Add Role</h3>

        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div>
                        <div id="errorMsg" class="alert alert-danger d-none"></div>
                        <form method="POST" action="{{  route($module.'.edit',['id' => $id]) }}" id="editForm" data-module="user_roles">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Role Name <span class="color_red">*</span></label>
                                        <input name="name" type="text" required class="form-control" value="{{ $roles['name'] }}">
                                        <input name="id" type="hidden" required class="form-control" value="{{ $id }}">

                                    </div>

                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Role Name (ar) <span class="color_red">*</span></label>
                                        <input name="name_ar" type="text" required class="form-control" value="{{ $roles['name_ar'] }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                    @foreach($module_permissions as $key => $module)
                                            <div class="col-sm-12">
                                                <label for="access_modules_{{ $key }}" class="checkbox_container user_roles_label">
                                                    <input type="checkbox" id="access_modules_{{ $key }}" {{ checkUserPermissions($permissions , $module['id']) }} name="access_modules[]" value="{{ $module['id'] }}"/>
                                                    {{ $module['name'] }}
                                                    <span class="checkmark"></span>
                                                </label>
                                            </div>
                                    @endforeach
                            </div>
                            <button class="btn btn-primary float-right" type="submit">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

