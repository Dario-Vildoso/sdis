<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Login' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Materio CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}">
</head>

<body style="background: linear-gradient(135deg, #f5f5f9, #e0e7ff);">

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="card shadow-lg border-0" style="max-width: 800px; width: 100%;">
        
        <div class="row g-0">

            {{-- 🔹 COLUMNA IZQUIERDA (FORMULARIO) --}}
            <div class="col-md-6 p-4" style="background: #f5f5f5;">

                <div class="text-center mb-4">
                    <h4>Bienvenido</h4>
                </div>

                {{ $slot }}

            </div>

            {{-- 🔹 COLUMNA DERECHA (LOGO) --}}
            <div class="col-md-6 d-flex align-items-center justify-content-center text-white"
     >

                <div class="text-center p-4">
                    <img src="{{ asset('assets/img/sedes.jpg') }}" 
                         alt="Logo" 
                         style="max-width: 300px; width: 100%;">
                    
                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>
