<!DOCTYPE html>
<html lang="en">

@include('panel.includes.head')

<body>

<div class="container-fluid p-0">

    @include('panel.includes.top_bar')

    <section class="w-100 d-flex">

        @include('panel.includes.left_bar')

        @yield('main')
    </section>


</div>

@include('panel.includes.scripts')

@stack('custom-scripts')
</body>
</html>
