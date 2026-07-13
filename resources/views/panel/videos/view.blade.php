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
                                        <label for="" class="mb-1">Full Name</label>
                                        <input disabled type="text" class="form-control" value="{{ $data->full_name }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">User Name</label>
                                        <input disabled type="text" class="form-control" value="{{ $data->user_name }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Email ID</label>
                                        <input disabled type="text" class="form-control" value="{{ $data->email }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Contact</label>
                                        <input disabled type="text" class="form-control" value="{{ $data->contact }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Business Name</label>
                                        <input disabled type="text" class="form-control"
                                               value="{{ $data->business_name }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Address</label>
                                        <input disabled type="text" class="form-control" value="{{ $data->address }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Postal Code</label>
                                        <input disabled type="text" class="form-control"
                                               value="{{ $data->postal_code }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">City</label>
                                        <input disabled type="text" class="form-control" value="{{ $data->city }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Country</label>
                                        <input disabled type="text" class="form-control" value="{{ $data->country }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Accounting Method</label>
                                        <input disabled type="text" class="form-control"
                                               value="{{ $data->accounting_method }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Company Year End Date</label>
                                        <input disabled type="text" class="form-control"
                                               value="{{ $data->company_year_end_date }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Language</label>
                                        <input disabled type="text" class="form-control"
                                               value="{{ $data->language == 'ar' ? 'Arabic' : 'English' }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <label for="" class="mb-1">Status</label>
                                    <select class="custom-select mr-0 font-weight-500" name="status_id" disabled
                                            id="status_id">
                                        @foreach($statuses as $status)
                                            <option {{ $data->status_id == $status->id ? 'selected' : ''}}
                                                    value="{{ $status->id }}">
                                                {{ $status->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

