@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">Add Subscriber</h3>
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div>
                        <div id="errorMsg" class="alert alert-danger d-none"></div>
                        <form method="POST" action="{{ route($module . '.add') }}" id="addForm">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Full Name <span
                                                class="color_red">*</span></label>
                                        <input name="full_name" required type="text" class="form-control" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Email ID <span
                                                class="color_red">*</span></label>
                                        <input name="email" required type="text" class="form-control" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">User Name</label>
                                        <input name="user_name" type="text" class="form-control" value="">
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group position-relative">
                                        <label for="" class="mb-1">Password <span
                                                class="color_red">*</span></label>
                                        <input id="password" name="password" required type="password" class="form-control" value="">
                                        <i id="togglePassword" class="far fa-eye position-absolute" 
                                            style="right: 10px; top: 70%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>
                                   
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Contact</label>
                                        <input name="contact" type="text"
                                            oninput="
                this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
                document.querySelector('input[name=phone]').value = this.value;
            "
                                            class="form-control" value="">
                                        <input type="hidden" name="phone" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Business Name <span
                                                class="color_red">*</span></label>
                                        <input type="text" required class="form-control" name="business_name"
                                            value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Industry Type <span
                                                class="color_red">*</span></label>
                                        <select class="form-control" required name="industry_type" id="select_countries">
                                            <option value="">Select Industry</option>
                                            @foreach ($industries as $industry)
                                                <option value="{{ $industry['id'] }}">{{ $industry['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Address <span
                                                class="color_red">*</span></label>
                                        <input name="address" required type="text" class="form-control" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Postal Code</label>
                                        <input type="text" class="form-control" maxlength="10" name="postal_code"
                                            value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">City <span class="color_red">*</span></label>
                                        <input name="city" required onkeypress="return /[a-z]/i.test(event.key)"
                                            type="text" class="form-control" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Country <span
                                                class="color_red">*</span></label>
                                        <select class="form-control" required name="country" id="select_countries">
                                            <option value="">Select Country</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country['id'] }}">{{ $country['name_en'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Country Code <span
                                                class="color_red">*</span></label>
                                        <select name="country_code" required class="form-control">
                                            <option value="">Select a country code</option>
                                            @foreach ($countryCode as $country)
                                                <option value="{{ $country['callingCode'] }}">{{ $country['title'] }}
                                                    ({{ $country['callingCode'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Currency <span
                                                class="color_red">*</span></label>
                                        <select name="currency" required class="form-control">
                                            <option value="">Select a currency code</option>
                                            @foreach ($countryCode as $country)
                                                <option value="{{ $country['id'] }}">{{ $country['title'] }}
                                                    
                                                </option>
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
                                        <label for="" class="mb-1">Company Year End Date <span
                                                class="color_red">*</span></label>
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
                                        <label for="" class="mb-1">Language <span
                                                class="color_red">*</span></label>
                                        <select class="form-control" required name="language">
                                            <option value="">Select Language</option>
                                            <option value="ar">Arabic</option>
                                            <option value="en">English</option>
                                        </select>

                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <input type="hidden" name="status_id" value="1" />
                                </div>
                            </div>
                            <button class="btn btn-primary float-right" type="submit">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
          <script>
        document.addEventListener("DOMContentLoaded", function () {
            const togglePassword = document.querySelector("#togglePassword");
            const password = document.querySelector("#password");
           
            togglePassword.addEventListener("click", function () {
                const type = password.getAttribute("type") === "password" ? "text" : "password";
                password.setAttribute("type", type);

                // Toggle icon style (eye ↔ eye-slash)
                this.classList.toggle("fa-eye");
                this.classList.toggle("fa-eye-slash");
            });
        });
    </script>
@endsection
