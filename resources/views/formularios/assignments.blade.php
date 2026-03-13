@extends('layouts.materio')

@section('title', 'Usuarios')

@section('content')

@if(session('success'))
    <div id="successAlert" class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
    </div>

    <script>
        setTimeout(function() {
            var alert = new bootstrap.Alert(document.getElementById('successAlert'));
            alert.close();
        }, 3000);
    </script>
@endif

@php

// Devuelve cuántas hojas tiene debajo
function countLeaves($cabecera) {
    if ($cabecera->hijos->isEmpty()) {
        return 1;
    }

    return $cabecera->hijos->sum(fn($h) => countLeaves($h));
}

// Devuelve profundidad máxima
function maxDepth($cabeceras) {
    if ($cabeceras->isEmpty()) return 0;

    return 1 + $cabeceras->max(function ($c) {
        return maxDepth($c->hijos);
    });
}

@endphp

<h3>Formulario: {{ $formulario->nombre }}</h3>

@foreach($formulario->variables as $variable)

@php
    $niveles = maxDepth($variable->cabeceras);
    $rows = [];
@endphp

@php

$buildRows = function ($cabeceras, $nivel = 0, &$rows = []) use (&$buildRows) {

    foreach ($cabeceras as $cabecera) {

        $rows[$nivel][] = $cabecera;

        if ($cabecera->hijos->isNotEmpty()) {
            $buildRows($cabecera->hijos, $nivel + 1, $rows);
        }
    }

    return $rows;
};

$rows = $buildRows($variable->cabeceras);

@endphp

<div class="d-flex justify-content-end">

    <div class="btn-group btn-group-sm">
        <button class="btn btn-warning edit-variable"
            data-id="{{ $variable->id }}">
            <i class="ri ri-edit-fill"></i>
        </button>

        <button class="btn btn-danger delete-variable"
            data-id="{{ $variable->id }}">
            <i class="ri ri-delete-bin-fill"></i>
        </button>
    </div>  
</div>
    <table class="table table-sm small table-materio mb-5">

        <thead>

            {{-- Primera fila: Variable + primer nivel --}}
            <tr>
                <th rowspan="{{ $niveles }}"
                    class="text-center align-middle text-white variable-cell"
                    style="width: 50%">
                    {{ $variable->descripcion }}
                </th>

                @foreach($rows[0] ?? [] as $cabecera)
                    <th colspan="{{ countLeaves($cabecera) }}"
                        @if($cabecera->hijos->isEmpty())
                            
                        @endif
                        class="text-center bg-secondary text-white cabecera-cell"
                        style="background: {{ $loop->even ? '#9d9fa3 !important' : '#9d9fa3 !important' }}">
                        {{ $cabecera->descripcion }}
                    </th>
                @endforeach
            </tr>

            {{-- Niveles siguientes --}}
            @for($i = 1; $i < $niveles; $i++)
                <tr>
                    @foreach($rows[$i] ?? [] as $cabecera)

                        @php
                            $hue = 213 - ($i * 3);   
                            $saturation = 10;
                            $lightness = 82 - ($i * 5);
                        @endphp
                        <!-- hsl(213, 30%, 32%) -->
                        @if($cabecera->hijos->isEmpty())
                            <th rowspan="{{ $niveles - $i }}"
                                class="text-center subcabecera-cell"
                                style="background: hsl({{ $hue }}, {{ $saturation }}%, {{ $lightness }}%)"
                                >
                                {{ $cabecera->descripcion }}
                            </th>
                        @else
                            <th colspan="{{ countLeaves($cabecera) }}"
                                class="text-center subcabecera-cell"
                                style="background: hsl({{ $hue }}, {{ $saturation }}%, {{ $lightness }}%)"
                                >
                                {{ $cabecera->descripcion }}
                            </th>
                        @endif

                    @endforeach
                </tr>
            @endfor

        </thead>

        <tbody>

            @foreach($variable->subvariables as $subvariable)

                <tr>
                    {{-- Columna Variable --}}
                    <td class="fw-semibold subvariable-cell text-white">
                        {{ $subvariable->descripcion }}
                    </td>

                    {{-- Columnas cabeceras vacías --}}
                    @for($i = 0; $i < $variable->total_leaf_columns; $i++)
                        <td class="data"></td>
                    @endfor

                </tr>

            @endforeach

        </tbody>

    </table>
    <hr>

@endforeach

<div class="mt-3 mb-3">
    <button type="button" class="btn btn-primary btn-sm" id="btnAgregarSubvariable" title="Agregar subvariable">
        <i class="ri fs-5 ri-add-line"></i>&nbsp; Agregar Variable
    </button>
</div>

<!-- Modal: Crear / Editar Formulario -->
<div class="modal fade" id="addVariableModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Seleccionar variable</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-sm table-hover">

                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th width="120">Acción</th>
                        </tr>
                    </thead>

                <tbody>

                @foreach($variablesDisponibles as $var)

                <tr>
                <td>{{ $var->descripcion }}</td>

                <td>
                <button class="btn btn-success btn-sm add-variable"
                    data-id="{{ $var->id }}">
                    Añadir
                </button>
                </td>

                </tr>

                @endforeach

                </tbody>

                </table>

            </div>

        </div>
    </div>
</div>

<script>
        $(function () {
                // Base URL for update action
                var baseUrl = "{{ url('formularios') }}";

                // New establishment: reset form
                $('#btnNewForm').on('click', function () {
                        $('#formModalLabel').text('Nuevo formulario');
                        $('#dataForm').attr('action', "{{ route('formularios.store') }}");
                        $('#formMethod').val(''); // Limpia el método para creación
                        $('#formNombre').val('');
                        $('#dataSubmit').text('Crear');
                });

                // Edit establishment: populate and set action
                $('.edit-data').on('click', function () {
                        var id = $(this).data('id');
                        var nombre = $(this).data('nombre');
                        var gestionId = $(this).data('gestion_id'); 

                        $('#formModalLabel').text('Editar formulario');
                        $('#dataForm').attr('action', baseUrl + '/' + id);
                        $('#formMethod').val('PUT'); // Cambia el método a PUT para actualización
                        $('#formNombre').val(nombre);
                        $('#formGestion').val(gestionId);
                        $('#dataSubmit').text('Actualizar');

                        var dataModal = new bootstrap.Modal(document.getElementById('dataModal'));
                        dataModal.show();
                });
                $('#dataForm').on('submit', function() {
                    $('#dataSubmit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
                    
                });
        });
</script>
<style>
    table.table-materio th.cabecera-cell {
        border: none;
        border-radius: 0 20px 0 0;
        padding: 8px 10px !important;
    }
    table.table-materio th.variable-cell {
        border: none;
        background: hsl(199, 15%, 61%) !important;
        
        border-radius: 0 20px 0 0;
        padding: 0;
    }
    table.table-materio th.subcabecera-cell {
        border: none;
        border-radius: 0 0 0 0;
        padding: 8px 10px !important;
    }
    table.table-materio td.subvariable-cell {
        background: hsl(199, 12%, 45%) !important;
        padding: 5px 10px !important;
    }
    table.table-materio td.data {
        padding: 5px 10px !important;
    }
    
</style>
@endsection
