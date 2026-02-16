<!DOCTYPE html>
<html lang="en" class="dark-style layout-menu-fixed">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Materio')</title>

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

        <!-- jQuery --> 
    <script src="{{url('plugins/jquery/jquery.min.js')}}"></script>
    <!-- alertas-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- datatables-->
    <link rel="stylesheet" href="{{url('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{url('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{url('plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">

    <style>
        /* Sidebar oscuro personalizado */
        .bg-menu-dark {
            background: linear-gradient(135deg, #020617 0%, #041744 100%) !important; /* degradado azul oscuro tipo Materio Pro */
        }

        /* Navbar oscuro */
        .navbar-dark.bg-menu-dark {
            background-color: #020617 !important;
        }

        /* Texto sidebar */
        .menu-item .menu-link {
            color: #e5e7eb;
        }

        .menu-item.active > .menu-link {
            background-color: rgba(255,255,255,0.08);
        }

        /* Hover en sidebar */
        .menu-link:hover {
            color: #020617;
        }
        
        /* Buscador */
        .dataTables_filter input {
            border-radius: 0px;
            border: rgba(67, 89, 113, 0);
            border-bottom: 1px solid #d9dee3;
            padding: 6px 12px;
        }

        /* Tabla */
        .table.dataTable {
            border-collapse: separate !important;
            border-spacing: 0 4px;
        }

        /* Filas estilo card */
        .table.dataTable tbody tr {
            background: #fff;
            box-shadow: 0 2px 6px rgba(67, 89, 113, 0.12);
            border-radius: 8px;
        }

        /* Hover */
        .table.dataTable tbody tr:hover {
            background-color: #f5f5f9;
        }

        /* Paginación */
        .dataTables_paginate .pagination .page-link {
            border-radius: 6px !important;
            margin: 0 3px;
        }
        .btn-icon {
            border: none;
            background: none;
            padding: 0;
            cursor: pointer;
        }
        .btn-icon i {
            transition: transform 0.2s ease-in-out;
        }

        .btn-icon:hover i {
            transform: scale(1.3);
        }
        .table-materio thead th {
            background-color: #ffc107 !important;
            color: #fff;
            font-weight: 600;
            border-bottom: none;
        }
    </style>
</head>
@stack('scripts')
<body class="dark-style">
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        @include('partials.sidebar')

        <div class="layout-page">

            @include('partials.navbar')

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    @yield('content')
                </div>
            </div>

        </div>
    </div>
</div>


<script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
<script src="{{ asset('assets/js/config.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- JQuery -->
<script src="{{url('plugins/jquery/jquery.min.js')}}"></script>
<script src="{{url('plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- datatable -->
<script src="{{url('plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{url('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{url('plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{url('plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{url('plugins/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
<script src="{{url('plugins/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
<script src="{{url('plugins/jszip/jszip.min.js')}}"></script>
<script src="{{url('plugins/pdfmake/pdfmake.min.js')}}"></script>
<script src="{{url('plugins/pdfmake/vfs_fonts.js')}}"></script>
<script src="{{url('plugins/datatables-buttons/js/buttons.html5.min.js')}}"></script>
<script src="{{url('plugins/datatables-buttons/js/buttons.print.min.js')}}"></script>
<script src="{{url('plugins/datatables-buttons/js/buttons.colVis.min.js')}}"></script>
</body>
</html>
