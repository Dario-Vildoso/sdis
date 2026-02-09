<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Materio')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}">
</head>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        {{-- Sidebar --}}
        @include('sidebar')

        <div class="layout-page">

            {{-- Navbar --}}
            @include('navbar')

            {{-- Content --}}
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    @yield('content')
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Core JS -->
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>

</body>
</html>
