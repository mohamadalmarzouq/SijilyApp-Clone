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
                        <img class="w-100" src="{{ asset('assets/img/not-verified.gif') }}" alt="">
                    </div>
                    <div class="col-sm-6">
                        <h1>Your Account Has Not Been Verified By Admin Yet</h1>
                        <button class="btn btn-danger btn-primary btn-rounded btn-lg" onclick="event.preventDefault();
                                   document.getElementById('logout-form-not-verified').submit();">LOGOUT
                        </button>
                    </div>
                    <form id="logout-form-not-verified" action="{{ route('logout') }}" method="POST"
                          style="display: none;">{{ csrf_field() }}</form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
