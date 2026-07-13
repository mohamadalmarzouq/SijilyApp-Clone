<!DOCTYPE html>
<html lang="en">
@include('auth.includes.head')
<body>
<div class="container-fluid">
    <div class="row">
        <div class="sign_up w-100 d-flex align-items-center">
            <div class="container">
                @include('auth.includes.flash_mesages',['hide_all_errors' => false])
                <div class="row align-items-center">
                    <div class="col-sm-6">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('auth.includes.scripts')
</body>
</html>
