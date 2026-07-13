@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">Change Password</h3>
        {{--  {{ dd($module_permissions) }}  --}}
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div>
                        <div id="errorMsg" class="alert alert-danger d-none"></div>
                        <form method="POST" action="{{ route($module.'.change') }}" id="addForm">
                            @csrf
                             <div class="row">
                                <div class="col-md-5 m-auto">
                                    <div class="col-sm-12">
                                        <div class="form-group position-relative">
                                            <label>Old Password <span class="color_red">*</span></label>
                                            <input id="oldPassword" name="old_password" type="password" class="form-control" value="" required>
                                            <i id="toggleOldPassword" class="far fa-eye position-absolute" 
                                                style="right: 10px; top: 70%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">                                        
                                        <div class="form-group position-relative">
                                            <label>New Password <span class="color_red">*</span></label>
                                            <input id="newPassword" name="password" type="password" class="form-control"  required>
                                            <i id="toggleNewPassword" class="far fa-eye position-absolute" 
                                            style="right: 10px; top: 70%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group position-relative">
                                            <label>Confirm Password <span class="color_red">*</span></label>
                                            <input id="confirmPassword" name="cpassword" type="password" class="form-control" value="" required>
                                            <i id="toggleConfirmPassword" class="far fa-eye position-absolute" 
                                            style="right: 10px; top: 70%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary float-right" type="submit">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
      <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toggleOldPassword = document.querySelector("#toggleOldPassword");
            const oldPassword = document.querySelector("#oldPassword");
            const toggleNewPassword = document.querySelector("#toggleNewPassword");
            const newPassword = document.querySelector("#newPassword");
            const toggleConfirmPassword = document.querySelector("#toggleConfirmPassword");
            const confirmPassword = document.querySelector("#confirmPassword");

            toggleOldPassword.addEventListener("click", function () {
                const type = oldPassword.getAttribute("type") === "password" ? "text" : "password";
                oldPassword.setAttribute("type", type);

                // Toggle icon style (eye ↔ eye-slash)
                this.classList.toggle("fa-eye");
                this.classList.toggle("fa-eye-slash");
            });
            toggleNewPassword.addEventListener("click", function () {
                const type = newPassword.getAttribute("type") === "password" ? "text" : "password";
                newPassword.setAttribute("type", type);

                // Toggle icon style (eye ↔ eye-slash)
                this.classList.toggle("fa-eye");
                this.classList.toggle("fa-eye-slash");
            });
            toggleConfirmPassword.addEventListener("click", function () {
                const type = confirmPassword.getAttribute("type") === "password" ? "text" : "password";
                confirmPassword.setAttribute("type", type);

                // Toggle icon style (eye ↔ eye-slash)
                this.classList.toggle("fa-eye");
                this.classList.toggle("fa-eye-slash");
            });
        });
    </script>
@endsection

