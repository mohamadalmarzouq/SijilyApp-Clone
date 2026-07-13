<!DOCTYPE html>
<html lang="en">

@include('auth.includes.head')

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">

                    <div class="card-header">{{ __('Reset Password') }}</div>

                    @if (Session::has('message'))
                        <div class="alert alert-success">
                            {{ Session::get('message') }}
                        </div>
                    @endif

                    <div class="card-body">
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            {{-- <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div> --}}

                            <div class="form-group row">
                                <label for="password"
                                    class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                                <div class="col-md-6 ">
                                    <div class="position-relative">
                                        <input id="password" type="password" class="form-control" name="password"
                                            autocomplete="new-password">

                                        <i id="togglePassword" class="far fa-eye position-absolute"
                                            style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>

                                    </div>

                                    {{-- @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $password }}</strong>
                                    </span>
                                @enderror --}}
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password-confirm"
                                    class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                                <div class="col-md-6">
                                    <div class="position-relative">
                                        <input id="password-confirm" type="password" class="form-control"
                                        name="password_confirmation" autocomplete="new-password">
                                        <i id="toggleConfirmPassword" class="far fa-eye position-absolute"
                                            style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>

                                    </div>
                                </div>
                            </div>
                            @if ($errors->has('password'))
                                <p style="color:red;">{{ $errors->first('password') }}</p>
                            @endif


                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Reset Password') }}
                                    </button>
                                </div>
                            </div>
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
            const toggleConfirmPassword = document.querySelector("#toggleConfirmPassword");
            const confirmPassword = document.querySelector("#password-confirm");


            togglePassword.addEventListener("click", function () {
                const type = password.getAttribute("type") === "password" ? "text" : "password";
                password.setAttribute("type", type);

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
    @include('auth.includes.scripts')
    @if (Session::has('passcodeChanged'))
        @php
            Session::forget('passcodeChanged');
        @endphp
        <script>
            setTimeout(() => {
                window.location.href = "{{ route('login') }}";
            }, 3000)
        </script>
    @endif
</body>

</html>
