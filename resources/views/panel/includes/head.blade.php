<head>
    <meta charset="UTF-8">
    <title>Sijily - Accounting Platform</title>
     {{--<link rel="shortcut icon" href="{{asset('assets/img/favicon.png')}}" type="image/x-icon">--}}
    <link href="{{ asset('assets/lib/@fortawesome/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/ionicons/css/ionicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/jqvmap/jqvmap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/prismjs/themes/prism-vs.css') }}" rel="stylesheet">
    @stack('custom-head')
    @php
        $unique = uniqid();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css?b='.$unique) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashforge.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashforge.demo.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashforge.dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashforge.auth.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.css') }}">
    <link href="{{ asset('assets/css/jquery.datetimepicker.css') }}" rel="stylesheet">

</head>
