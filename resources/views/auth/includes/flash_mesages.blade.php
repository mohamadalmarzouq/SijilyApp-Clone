@if($errors->any() && !isset($hide_all_errors))
    @foreach($errors->all() as $error)
        <div class="alert alert-danger alert-dismissible" role="alert">

            <div class="icon"> <span class="mdi mdi-block-alt"></span></div>
            <div class="message">{{ $error }}</div>
        </div>
    @endforeach
@endif


@include('flash::message')
