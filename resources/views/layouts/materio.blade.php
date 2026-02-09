<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Materio')</title>

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">
</head>
<body>

    @yield('content')

    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
</body>
</html>