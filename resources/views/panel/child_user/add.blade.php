@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">Add Subscriber</h3>
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div>
                        <div id="errorMsg" class="alert alert-danger d-none"></div>
                        <form method="POST" action="{{ route($module.'.add') }}" id="addForm">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Full Name</label>
                                        <input name="full_name" required type="text" class="form-control" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Email ID</label>
                                        <input  name="email" required type="text" class="form-control" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">User Name</label>
                                        <input name="user_name" required type="text" class="form-control" value="">
                                    </div>
                                </div>


                                 <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Password</label>
                                        <input name="password" required type="password" class="form-control" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Contact</label>
                                        <input name="contact" required type="text" class="form-control" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Business Name</label>
                                        <input   type="text" required class="form-control" name="business_name"
                                               value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Industry Type</label>
                                         <select class="form-control" required name="industry_type" id="select_countries">
                                            <option value="">Select Industry</option>
                                            @foreach($industries as $industry)
                                                <option value="{{ $industry['id']  }}">{{ $industry['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Address</label>
                                        <input name="address" required  type="text" class="form-control" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Postal Code</label>
                                        <input type="text" class="form-control" maxlength="10" name="postal_code" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">City</label>
                                        <input  name="city" required  type="text" class="form-control" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Country</label>
                                        <select class="form-control" required name="country" id="select_countries">
                                            <option value="">Select Country</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country['id']  }}">{{ $country['name_en'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Accounting Method</label>
                                        <input   type="text" class="form-control"
                                               value="{{ $data->accounting_method }}">
                                    </div>
                                </div> --}}

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Company Year End Date</label>
                                        <select class="form-control" required name="company_year_end_date">
                                            <option value="">Select Year End Date</option>
                                            <option value="31/3">31 March</option>
                                            <option value="30/6">30 June</option>
                                            <option value="31/8">31 August</option>
                                            <option value="30/9">30 September</option>
                                            <option value="31/10">31 October</option>
                                            <option value="31/12">31 December</option>
                                        </select>
                                        {{-- <input required type="text" class="form-control" name="company_year_end_date"
                                               value=""> --}}
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Language</label>
                                        <select class="form-control" required name="language">
                                            <option value="">Select Language</option>
                                            <option  value="ar">Arabic</option>
                                            <option value="en">English</option>
                                        </select>

                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <input type="hidden" name="status_id" value="1"/>
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

