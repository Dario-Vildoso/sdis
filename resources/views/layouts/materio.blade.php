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
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"> -->

        <!-- jQuery --> 
    <script src="{{url('plugins/jquery/jquery.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- alertas-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- datatables-->
    <!-- <link rel="icon" href="{{ asset('sedes.jpg') }}" type="image/jpg"> -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{url('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{url('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{url('plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">

    <style>
        /* Sidebar oscuro personalizado */
        .bg-menu-dark {
            /* background: linear-gradient(135deg, #575a68 0%, #353f55 100%) !important; degradado azul oscuro tipo Materio Pro */
            background: white !important;
            box-shadow: #0000001a 0px 1px 10px;
        }

        /* Texto sidebar */
        .menu-item .menu-link {
            color: #6e6e6e;
        }

        .menu-item.active > .menu-link {
            border-radius: 40px !important;
            background: linear-gradient(90deg, #086375, #1dd3b0) !important;
            margin-right: 0px !important;
            margin-left: 20px;
            box-shadow: #139b929a 0px 0px 10px !important;
        }

        .menu-item a.menu-link {
            transition: all 0.3s ease;
        }
        .menu-item a.menu-link:hover {
            color: #020617;
            transform: translateX(10px);
        }

        .navbar-sticky {
            position: sticky;
            top: 0;
            z-index: 1030;
            background-color: white !important;
        }
        
        .card-action {
            background: linear-gradient(90deg, #086375, #1dd3b0) !important;
        }

        .card-action .card-title {
            color: white;
        }

        .card-action .btn-card-action {
            border-radius: 40px;
            background-color: #e9ecef;
            width: 100%;                 /* quitamos el none */
            /* Sombra interior (inset) + sombra exterior suave */
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;   /* para que se sienta interactivo */
        }

        /* Efecto al hacer hover (opcional pero muy recomendado) */
        .card-action .btn-card-action:hover {
            background-color:   #ffffff;
        }

        /* Efecto al presionar (active) */
        .card-action .btn-card-action:active {
            box-shadow: 
                inset 0 5px 10px rgba(0, 0, 0, 0.25),
                inset 0 -1px 2px rgba(255, 255, 255, 0.6);
            transform: translateY(-1px);
        }

        .card-info .card-header {
            padding: 10px 20px;
            color: white;
        }
        
        .card-info .card-body {
            padding: 0px;
        }

        .card-info .badge {
            border-radius: 20px;
            background: white !important;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.24);
        }

        .card-info .icon-box {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #17a2b8;
            color: white;
        }
        
        /* Buscador */
        .dataTables_filter input {
            border-radius: 20px;
            border: rgba(67, 89, 113, 0);
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.15);
            padding: 6px 12px;
        }

        .dataTables_filter input:focus {
            outline: none;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.25);
        }
        
        .table.table-materio thead th.id-header {
            background: transparent !important;
            border: none !important;
            width: 10px;
        }

        .table.table-materio thead th {
            background: #139b92 !important;
            color: white;
        }
        .table.table-materio thead th.actions {
            background: #086375 !important;
            color: white;
            width: 5rem;
        }

        
        .table.table-materio tbody td.id-cell{
            padding: 0px 0px 0px 10px;
        }
        
        .table.table-materio thead th.transparent-cell{
            background: transparent !important;
            border: none !important;
            display: flex;
            width: fit-content;
            padding: 10px 10px 10px 0px;
        }
        
        .table.table-materio tbody td.id-cell div{
            /* width: 40px;
            height: 40px; */
            text-align: center;
            justify-content: center;
            align-items: center;
            display: flex;
            width: 100%;
            height: 40px;
            border-radius: 50% 0 0 50%;
            font-size: 12px;
            color: #ffffff;
            background-color: #17a2b8;
            font-weight: 500;
        }

        .table.table-materio tbody td.button-cell-left div,
        .table.table-materio thead th.button-cell-left div{
            /* width: 40px;
            height: 40px; */
            padding-left: 10px;
            text-align: left;
            justify-content: left;
            align-items: center;
            display: flex;
            width: 40px;
            height: 40px;
            border-radius: 40px 0 0 40px;
            font-size: 12px;
            color: #ffffff;
            background: linear-gradient(90deg, #ffc107, #ff9800);
            font-weight: 500;
            cursor: pointer;
        }

        .table.table-materio tbody td.button-cell-right div,
        .table.table-materio thead th.button-cell-right div{
            /* width: 40px;
            height: 40px; */
            border: none;
            padding-right: 10px;
            text-align: right;
            justify-content: right;
            align-items: center;
            display: flex;
            width: 40px;
            height: 40px;
            border-radius: 0% 40px 40px 0%;
            font-size: 12px;
            color: #ffffff;
            background: linear-gradient(90deg, #ffc107, #ff9800);
            font-weight: 500;
            cursor: pointer;
        }

        /* Hover */
        .table.table-materio tbody tr:hover {
            background-color: #f5f5f9;
        }
        .table.table-materio tbody .badge {
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .table.table-materio tbody .badge:hover {
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.3);
        }

        /* Paginación */
        .dataTables_paginate .pagination .page-link {
            border-radius: 20px !important;
            margin: 0 3px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        .dataTables_paginate .pagination .page-link:hover {
            background: #139b9244;
            color: white;
            box-shadow: 0 0 10px #139b9248;
        }
        .dataTables_paginate .pagination .active .page-link {
            background: #139b92 ;
            box-shadow: 0 0 10px #139b929a;
        }

        .dt-buttons .btn {
            border-radius: 20px;
            color: white;
            border: none;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
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

        .modal-content .input-group-text {
            border: none;
            color: white;
            border-radius: 5rem;
        }

        .modal-content select,
        .modal-content input{
            border: none;
            border-radius: 50rem;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.15);

        }

        .modal-content .btn{
            border: none;
            border-radius: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .select2-container .select2-selection {
            border-radius: 1.5rem;
            border: none;
            min-height: 46px;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.15);
            background: #fff;
        }

        .select2-container .select2-selection__rendered {
            padding: 0.55rem 0.75rem;
        }

        .select2-container .select2-selection__arrow {
            height: 100%;
        }

        .select2-container .select2-results__option--highlighted {
            background-color: #139b92;
            color: white;
        }

        form.reports select,
        form.reports input {
            border: none;
            border-radius: 50rem;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.15);
        }

        form.reports select[multiple][size]:not([size="1"]) {
            border-radius: 25px 0px 0px 25px;
        }

        form.reports .input-group-text {
            border: none;
            color: white;
            border-radius: 5rem;
        }

        form.reports select:hover,
        form.reports input:hover {
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.25);
        }

        form.reports select:focus,
        form.reports input:focus {
            outline: none;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.25);
        }

        form.reports .btn {
            border: none;
            border-radius: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        form.reports .btn:hover {
            background-color: #17a2b8;
            color: white;
            box-shadow: 0 0 15px #17a2b8;
        }

        [contenteditable="true"] {
            cursor: text;
            outline: 2px dashed transparent;
            transition: all 0.2s ease;
        }

        [contenteditable="true"]:hover {
            background-color: #fff3cd;
            outline: 2px dashed #ffc107;
        }

        [contenteditable="true"]:focus {
            background-color: #e7f3ff2a;
            outline: 2px solid #df5307;
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
