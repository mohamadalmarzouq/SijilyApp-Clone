@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">Edit {{ setText($module, true) }}</h3>
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div>
                        <div id="errorMsg" class="alert alert-danger d-none"></div>
                        <form method="POST" action="{{ route($module . '.edit', ['id' => $data->id]) }}" id="editForm">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Full Name <span
                                                class="color_red">*</span></label>
                                        <input name="full_name" type="text" required class="form-control"
                                            value="{{ $data->full_name }}">
                                        <input name="is_child" type="hidden" class="form-control"
                                            value="{{ $data->is_child }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Email ID <span
                                                class="color_red">*</span></label>
                                        <input type="text" readonly class="form-control" value="{{ $data->email }}">
                                    </div>
                                </div>
                                @if ($data->is_child)
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="mb-1">Password <span
                                                    class="color_red">*</span></label>
                                            <input type="password" name="password" disabled id="password"
                                                class="form-control" placeholder="Password" value="">
                                            <a href="#" class="float-right mt-2" id="change_password">Enable Password
                                                Field</a>
                                        </div>
                                    </div>
                                @endif
                                @if ($data->is_child)
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="mb-1">Role <span
                                                    class="color_red">*</span></label>
                                            <select name="role_id" class="form-control" required>
                                                <option value="">Select Role</option>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role['id'] }}"
                                                        {{ $data->role_id == $role['id'] ? 'selected' : '' }}>
                                                        {{ $role['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                @if (!$data->is_child)
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="mb-1">User Name </label>
                                            <input name="user_name" type="text" class="form-control"
                                                value="{{ $data->user_name }}">
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
                                                class="form-control" value="{{ $data->contact ?? '' }}">
                                            <input type="hidden" name="phone" value="{{ $data->contact ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="mb-1">Business Name <span
                                                    class="color_red">*</span></label>
                                            <input required type="text" class="form-control" name="business_name"
                                                value="{{ $data->business_name }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">

                                        <div class="form-group">
                                            <label for="" class="mb-1">Industry Type <span
                                                    class="color_red">*</span></label>
                                            <select class="form-control" required name="industry_type" required
                                                id="select_countries">
                                                <option value="">Select Industry</option>
                                                @foreach ($industries as $industry)
                                                    <option value="{{ $industry['id'] }}"
                                                        {{ $data->industry_type == $industry['id'] ? 'selected' : '' }}>
                                                        {{ $industry['name'] }}</option>
                                                @endforeach
                                            </select>
                                            {{-- <input   type="text" class="form-control"
                                                 value="{{ !is_null(@json_decode($data->industry_type)->title) ? json_decode($data->industry_type)->title : $data->industry_type }}"> --}}
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="mb-1">Address <span
                                                    class="color_red">*</span></label>
                                            <input name="address" type="text" required class="form-control"
                                                value="{{ $data->address }}">
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="mb-1">Postal Code</label>
                                            <input maxlength="10" type="text" class="form-control" name="postal_code"
                                                value="{{ $data->postal_code }}">
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="mb-1">City <span
                                                    class="color_red">*</span></label>
                                            <input name="city" type="text"
                                                onkeypress="return /[a-z]/i.test(event.key)" required class="form-control"
                                                value="{{ $data->city }}">
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="mb-1">Country<span
                                                    class="color_red">*</span></label>
                                            @if (is_numeric($data->country))
                                                <select class="form-control" name="country" required>
                                                    <option value="">Select Country</option>
                                                    @foreach ($countries as $country)
                                                        <option value="{{ $country['id'] }}"
                                                            {{ $country['id'] == $data->country ? 'selected=true' : '' }}>
                                                            {{ $country['name_en'] }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <select class="form-control" name="country" required>
                                                    <option value="">Select Country</option>
                                                    @foreach ($countries as $country)
                                                        <option value="{{ $country['id'] }}"
                                                            {{ $country['name_en'] == $data->country ? 'selected=true' : '' }}>
                                                            {{ $country['name_en'] }}</option>
                                                    @endforeach
                                                </select>
                                            @endif

                                            {{-- <input  name="country"  type="text" class="form-control" value="{{ $data->country }}"> --}}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="mb-1">Country Code <span
                                                    class="color_red">*</span></label>
                                            <select name="country_code" required class="form-control">
                                                <option value="">Select a country code</option>
                                                @foreach ($countryCode as $country)
                                                    <option value="{{ $country['callingCode'] }}"
                                                        {{ isset($data['country_code']) && $data['country_code'] == $country['callingCode'] ? 'selected' : '' }}>
                                                        {{ $country['title'] }} ({{ $country['callingCode'] }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Currency
                                            <span class="color_red">*</span></label>
                                        <select name="currency" required class="form-control">
                                            <option value="">Select a currency code</option>
                                            @foreach ($countryCode as $country)
                                                <option value="{{ $country['id'] }}" {{ isset($data['currency']) && $data['currency'] == $country['id'] ? 'selected' : '' }}>
                                                        {{ $country['id'] }}
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
                                                <option value="31/3"
                                                    {{ $data->company_year_end_date == '31/3' ? 'selected' : '' }}>31 March
                                                </option>
                                                <option value="30/6"
                                                    {{ $data->company_year_end_date == '30/6' ? 'selected' : '' }}>30 June
                                                </option>
                                                <option value="31/8"
                                                    {{ $data->company_year_end_date == '31/8' ? 'selected' : '' }}>31
                                                    August
                                                </option>
                                                <option value="30/9"
                                                    {{ $data->company_year_end_date == '30/9' ? 'selected' : '' }}>30
                                                    September</option>
                                                <option value="31/10"
                                                    {{ $data->company_year_end_date == '31/10' ? 'selected' : '' }}>31
                                                    October</option>
                                                <option value="31/12"
                                                    {{ $data->company_year_end_date == '31/12' ? 'selected' : '' }}>31
                                                    December</option>
                                            </select>
                                            {{-- <input  type="text" class="form-control" name="company_year_end_date"
                                               value="{{ $data->company_year_end_date }}"> --}}
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="mb-1">Language <span
                                                    class="color_red">*</span></label>
                                            <select class="form-control" required name="language">
                                                <option value="">Select Language</option>
                                                <option {{ $data->language == 'ar' ? "selected='true'" : '' }}
                                                    value="ar">Arabic</option>
                                                <option {{ $data->language == 'en' ? "selected='true'" : '' }}
                                                    value="en">English</option>
                                            </select>

                                        </div>
                                    </div>
                                @endif
                                <div class="col-sm-6">
                                    <label for="" class="mb-1">Status <span class="color_red">*</span></label>
                                    <select class="custom-select mr-0 font-weight-500" required name="status_id"
                                        id="status_id">
                                        @foreach ($statuses as $status)
                                            <option {{ $data->status_id == $status->id ? 'selected' : '' }}
                                                value="{{ $status->id }}">
                                                {{ $status->title }}
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
    <script>
        document.getElementById("change_password").addEventListener('click', function() {
            const passinput = document.getElementById("password");
            passinput.disabled = !passinput.disabled;
        })
    </script>
@endsection
