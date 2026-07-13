@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <div class="row row-xs">
            <div class="col-sm-12 col-lg-12 mt-2">
                <div class="card card-body flex-row justify-content-between">
                    <div class="search-form mg-t-20 mg-sm-t-0 w-50 mr-3">
                        <input type="text" id="search" class="form-control" placeholder="Search">
                        <button class="btn" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-search">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </button>
                    </div>
                    @if($parent_user['user_count_count'] != $parent_user['subscription']['no_of_users'] && $parent_user['status']['slug'] !="block")
                        <a href="{{  route($module.'.add_child',["id"=>$id]) }}" class="btn btn-success">Add User</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div class="d-flex justify-content-between align-items-end mb-3">
                         <h3 class="tx-18">Users</h3>
                    </div>
                    @include('panel.includes.datatable')
                </div>
            </div>
        </div>
    </div>
@endsection

