@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        @include('auth.includes.flash_mesages')
        <div class="row row-xs">
            <div class="col-sm-12 col-lg-12 mt-2">
                <div class="card card-body flex-row justify-content-between">
                    <form class="w-100" method="POST"
                          action="{{ route('update_profile',['id' => $data->id]) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-sm-2">
                                <div class="w-100 profile_avatar mb-4 mt-2 position-relative">
                                    <img id="user_image" src="{{ getUserAvatar($data->id) }}" alt="">
                                    <label for="upload-photo">
                                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                             stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                             class="css-i6dzq1">
                                            <path
                                                d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                            <circle cx="12" cy="13" r="4"></circle>
                                        </svg>
                                    </label>
                                    <input type="file" name="image" id="upload-photo"/>
                                </div>
                            </div>
                            <div class="col-sm-10">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="">Name</label>
                                            <input type="text" name="name" id="name" class="form-control"
                                                   placeholder="Name" value="{{ $data->name }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="">Email ID</label>
                                            <input type="email" name="email" id="email" class="form-control"
                                                   placeholder="Email ID" value="{{ $data->email }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="">Contact Number</label>
                                            <input type="number" name="contact" id="contact" class="form-control"
                                                   placeholder="Contact Number" value="{{ $data->contact }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="">Reset password</label>
                                            <input type="password" name="password" id="password" class="form-control"
                                                   placeholder="Reset password" value="">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="">Address</label>
                                            <input type="text" name="address" id="address" class="form-control"
                                                   placeholder="Address" value="{{ $data->address }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary float-right">Submit</button>
                    </form>
                </div>
            </div>
        </div>
        <!--/// first row end here ///-->
    </div>
@endsection

@push('custom-scripts')

    <script type="text/javascript">

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#user_image').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#upload-photo").change(function(){
            readURL(this);
        });

    </script>

@endpush
