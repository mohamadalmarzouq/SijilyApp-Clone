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
                        <form action="{{ route('password.email') }}" method="POST">
                            @csrf
                            <div class="wd-100p">
                                @if (Session::has('message'))
                                <div class="alert alert-success">
                                    {{ Session::get('message') }}
                                </div>
                                @endif
                                <h3 class="tx-color-01 mg-b-5">Forgot Password</h3>
                                <p class="tx-color-03 tx-16 mg-b-40">Please provide us your Email to get the reset link</p>
                                <div class="form-group">
                                    <label>Email address</label>
                                    <input autocomplete="username" type="email" name="email" class="form-control"
                                           placeholder="yourname@yourmail.com">
                                    @include('auth.includes.single_error' , ['name' => 'email'])
                                </div>
                                {{-- <div class="form-group">
                                    <div class="d-flex justify-content-between mg-b-5">
                                        <label class="mg-b-0-f">Password</label>
                                    </div>
                                    <input autocomplete="current-password" type="password" name="password" class="form-control"
                                           placeholder="Enter your password">
                                    @include('auth.includes.single_error' , ['name' => 'password'])
                                </div> --}}
                                <a href="{{ route('login') }}">Back to Login</a>
                                <input type="submit" class="btn btn-brand-02 btn-block" value="Submit">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('auth.includes.scripts');
</body>
</html>
