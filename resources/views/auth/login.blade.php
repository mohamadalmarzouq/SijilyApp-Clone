<!DOCTYPE html>
<html lang="en">

@include('auth.includes.head')
<body>
<div class="container-fluid">
    <div class="row">
        <div class="sign_up w-100 d-flex align-items-center">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="wd-100p">
                                <h3 class="tx-color-01 mg-b-5">Login</h3>
                                <p class="tx-color-03 tx-16 mg-b-40">Welcome back! Please Login to continue.</p>

                                <div class="form-group">
                                    <label>Email address</label>
                                    <input autocomplete="username" type="email" name="email" class="form-control"
                                           placeholder="yourname@yourmail.com" value="{{ old('email') }}">
                                    @include('auth.includes.single_error' , ['name' => 'email'])
                                </div>
                                <div class="form-group position-relative">
                                    <div class="d-flex justify-content-between mg-b-5">
                                        <label class="mg-b-0-f">Password</label>
                                    </div>
                                    <input autocomplete="current-password" type="password" name="password" class="form-control"
                                           placeholder="Enter your password" id="currentPassword">
                                    <i id="toggleCurrentPassword" class="far fa-eye position-absolute" 
                                            style="right: 10px; top: 70%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>
                                    @include('auth.includes.single_error' , ['name' => 'password'])
                                </div>
                                <a href="{{ route('forgot_password') }}">forgot password</a>
                                <input type="submit" class="btn btn-brand-02 btn-block" value="Login">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
      <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toggleCurrentPassword = document.querySelector("#toggleCurrentPassword");
            const currentPassword = document.querySelector("#currentPassword");
           
            toggleCurrentPassword.addEventListener("click", function () {
                const type = currentPassword.getAttribute("type") === "password" ? "text" : "password";
                currentPassword.setAttribute("type", type);

                // Toggle icon style (eye ↔ eye-slash)
                this.classList.toggle("fa-eye");
                this.classList.toggle("fa-eye-slash");
            });
        });
    </script>
@include('auth.includes.scripts')
</body>
</html>
