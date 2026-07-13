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
                                        <label for="" class="mb-1">Full Name <span class="color_red">*</span></label>
                                        <input name="full_name" required type="text" class="form-control" value="">
                                        <input name="user_id" type="hidden" class="form-control" value="{{ $id }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Email ID <span class="color_red">*</span></label>
                                        <input  name="email" required type="text" class="form-control" value="">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Role <span class="color_red">*</span></label>
                                        <select name="role_id"  required class="form-control">
                                           <option value="">Select Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                 <div class="col-sm-6">
                                    <div class="form-group position-relative">
                                        <label for="" class="mb-1">Password <span class="color_red">*</span></label>

                                        <input name="password" id="password" required type="password" class="form-control" value="">
                                          <i id="togglePassword" class="far fa-eye position-absolute" 
                                            style="right: 10px; top: 70%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>
                                  
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

