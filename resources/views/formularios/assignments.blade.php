@extends('layouts.materio')

@section('title', 'Usuarios')

@section('content')


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
$currVars = [];

@endphp


<div class="d-flex align-items-center my-3">
    <hr class="flex-grow-1">
    <h3 class="mx-3 text-center mb-0">
        {{ $formulario->sistema->nombre }} - {{ $formulario->gestion->nombre }}: {{ $formulario->descripcion }}
    </h3>
    <hr class="flex-grow-1">
</div>
<div class="card" style="padding: 20px;">
@foreach($formulario->variables as $variable)

@php
    $niveles = maxDepth($variable->cabeceras);
    $rows = [];
    $currVars[] = $variable->id;
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
<div class="containerTabla" data-variable-id="{{ $variable->id }}">
<div class="d-flex justify-content-end buttons mb-3">

    <div class="btn-group btn-group-sm">
        <a href="{{ route('variables.tabla', ['variable' => $variable->id, 'form' => $formulario->id]) }}" class="btn btn-warning edit-variable"
            data-id="{{ $variable->id }}" style="border: none;
            border-radius: 5rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;">
            <i class="ri ri-edit-fill"></i>
        </a>
        <button class="btn btn-danger delete-variable"
            data-id="{{ $variable->id }}"  style="border: none;
            border-radius: 5rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;">
            <i class="ri ri-delete-bin-fill"></i>
        </button>
    </div>  
</div>
<div class="table-responsive">

    <table class="table table-sm small table-materio table-variable" id="tablaVariable">
        
        <thead>
            
            {{-- Primera fila: Variable + primer nivel --}}
            <tr>
                <th rowspan="{{ $niveles - 1 }}"
                class="text-center align-middle text-white variable-cell"
                style="width: 33%">
                {{ $variable->descripcion }}
            </th>
            
                @foreach($rows[0] ?? [] as $cabecera)
                <th colspan="{{ countLeaves($cabecera) }}"
                        class="text-center bg-secondary text-white cabecera-cell">
                        {{ $cabecera->descripcion }}
                    </th>
                    @endforeach
                </tr>
                
                {{-- Niveles siguientes --}}
            @for($i = 1; $i < $niveles; $i++)
            <tr>
                @if ( $i == $niveles - 1 )
                @php
                    $rowlenght = count($rows[$i]);
                    $descs = [];
                        foreach ($rows[$i] ?? [] as $cabecera) {
                            $descs[] = $cabecera->descripcion;
                        }
                    $longNames = count(array_filter($descs, function($desc) {
                        return strlen($desc) <= 5;
                    })) === 0;
                    $uniqueDescs = array_unique($descs);
                    $vallength = count($uniqueDescs);
                @endphp
                @if ($vallength < $rowlenght && $longNames)
                <th rowspan="{{ $niveles - $i }}"
                    class="text-center variable-cell"
                    style="border-radius: 0 0 0 0; padding: 8px 10px; border-top: 1px solid white;"
                    >
                    {{ implode(', ', array_unique($descs)) }}
                </th>
                @else
                <th rowspan="{{ $niveles - $i }}"
                    class="text-center variable-cell"
                    style="border-radius: 0 0 0 0; padding: 8px 10px;"
                    >
                </th>
                @endif
                @endif
                @foreach($rows[$i] ?? [] as $cabecera)
                        @php
                            $hue = 176 + ($i * 3);
                            $saturation = 78 - ($i * 5);
                            $lightness = 34 - ($i * 2);
                        @endphp
                        <!-- hsl(213, 30%, 32%) -->
                        @if($cabecera->hijos->isEmpty() && $vallength < $rowlenght && $longNames)
                        <th rowspan="{{ $niveles - $i }}"
                        class="text-center"
                        style="background: hsl({{ $hue }}, {{ $saturation }}%, {{ $lightness }}%) !important;"
                        >
                        {{ $cabecera->descripcion[0] }}
                    </th>
                        @else
                        <th colspan="{{ countLeaves($cabecera) }}"
                        class="text-center subcabecera-cell"
                        style="background: hsl({{ $hue }}, {{ $saturation }}%, {{ $lightness }}%) !important;"
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
</div>
    <hr>
</div>
@endforeach
</div>
<div class="mt-3 mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVariableModal" title="Agregar subvariable">
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

                <table id="dataTable" class="table table-sm table-hover">

                    <thead>
                        <tr>
                            <th>Variable</th> 
                            <th>Subvariables</th>
                            <th>Cabeceras</th>
                            <th class="actions">Accion</th>
                        </tr>
                    </thead>

                <tbody>

                @foreach($variables as $variable)

                    <tr>
                        <td>{{ $variable->descripcion }}</td>
                        <td class="data">
                            <span class="badge bg-label-info cursor-pointer" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#subvars-{{ $variable->id }}" 
                            aria-expanded="false" 
                            aria-controls="subvars-{{ $variable->id }}">
                                {{ $variable->subvariables->count() }} subvariables  <i class="ri ri-arrow-down-s-fill"></i>
                            </span>
                            <div class="collapse mt-2" id="subvars-{{ $variable->id }}">
                                <div class="p-2 bg-light rounded small">
                                    @forelse($variable->subvariables as $sub)
                                        <div>• {{ $sub->descripcion }}</div>
                                    @empty
                                        <div class="text-muted">Sin subvariables</div>
                                    @endforelse
                                </div>
                            </div>
                        </td>
                        <td class="data">
                            <span class="badge bg-label-success cursor-pointer"
                            data-bs-toggle="collapse"
                            data-bs-target="#cabs-{{ $variable->id }}"
                            aria-expanded="false"
                            aria-controls="cabs-{{ $variable->id }}">
                                {{ $variable->cabeceras->count() }} cabeceras   <i class="ri ri-arrow-down-s-fill"></i>
                            </span>
                            <div class="collapse mt-2" id="cabs-{{ $variable->id }}">
                                <div class="p-2 bg-light rounded small">
                                    @php
                                        // Construir árbol de cabeceras
                                        $cabecerasHijas = $variable->cabeceras->groupBy('cabecera_id');
                                        
                                        $renderTree = function($parentId = null, $level = 0) use (&$renderTree, $cabecerasHijas, $variable) {
                                            $cabeceras = $cabecerasHijas->get($parentId) ?? collect();
                                            $count = count($cabeceras);
                                            
                                            foreach ($cabeceras as $index => $cab) {
                                                $isLast = ($index === $count - 1);
                                                $prefix = '';
                                                
                                                // Crear prefijo tipo árbol
                                                if ($level > 0) {
                                                    for ($i = 0; $i < $level - 1; $i++) {
                                                        $prefix .= '│&nbsp;&nbsp;&nbsp;';
                                                    }
                                                    $prefix .= $isLast ? '└─ ' : '├─ ';
                                                }
                                                
                                                echo '<div>' . $prefix . htmlspecialchars($cab->descripcion, ENT_QUOTES, 'UTF-8') . '</div>';
                                                
                                                // Renderizar recursivamente hijos
                                                $renderTree($cab->id, $level + 1);
                                            }
                                        };
                                        
                                        $renderTree();
                                    @endphp
                                    @if($variable->cabeceras->isEmpty())
                                        <div class="text-muted">Sin cabeceras</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <button class="btn-icon add-variable"
                                data-id="{{ $variable->id }}"
                                @if (in_array($variable->id, $currVars))
                                    disabled
                                @endif
                                >
                                <i class="ri ri-add-line bg-success"></i>
                            </button>
                        </td>
                    </tr>

                @endforeach

                </tbody>

                </table>

                <button class="btn mb-3 text-white" id="btnNuevaVariable" style="background-color: #17a2b8">
                   + Nueva variable
                </button>

                <!-- FORMULARIO NUEVA VARIABLE -->
                <div id="formNuevaVariable" style="display:none">

                    <form id="crearVariableForm">

                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nombre de la variable</label>
                            <input type="text" class="form-control" name="descripcion" required>
                        </div>

                        <button class="btn btn-success">
                            Crear variable
                        </button>

                        <button type="button" class="btn btn-secondary" id="cancelNuevaVariable">
                            Cancelar
                        </button>

                    </form>

                    <hr>

                </div>

            </div>

        </div>
    </div>
</div>

<script>
    $(function () {
        $("#dataTable").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(Filtrado de _MAX_ total registros)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscador:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true, "lengthChange": true, "autoWidth": false,

        }).buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');
    });
</script>
<script>
        $(function () {
                $('#btnNuevaVariable').on('click', function(){
                    $('#formNuevaVariable').slideDown();
                });

                $('#cancelNuevaVariable').on('click', function(){   
                    $('#formNuevaVariable').slideUp();
                });
                $(document).on('click', '.add-variable', function () {

                    $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                    const variableId = $(this).data('id');
                    const formularioId = "{{ $formulario->id }}";

                    $.post("{{ route('formularios.addVariable') }}", {
                        _token: "{{ csrf_token() }}",
                        formulario_id: formularioId,
                        variable_id: variableId
                    }).done(function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            timer: 2500,
                            showConfirmButton: false,
                            target: document.getElementById('addVariableModal')
                        });
                        location.reload();
                    })
                });

                $(document).on('click', '.delete-variable', function () {

                    if(!confirm("Eliminar variable del formulario?")) return;

                    const variableid = $(this).data('id');
                    const formularioId = "{{ $formulario->id }}";

                    $.post("{{ route('formularios.removeVariable') }}", {
                        _token: "{{ csrf_token() }}",
                        formulario_id: formularioId,
                        variable_id: variableid
                    }).done(function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            timer: 2500,
                            showConfirmButton: false
                        });
                        location.reload();
                    })
                });
                $('#crearVariableForm').on('submit', function(e){

                    e.preventDefault();

                    $.post("{{ route('asignaciones.createVarForm', $formulario) }}", $(this).serialize())
                    .done(function(response){

                        Swal.fire({
                            icon: 'success',
                            title: 'Variable creada',
                            timer: 2500,
                            showConfirmButton: false,
                            target: document.getElementById('addVariableModal')
                        });

                        setTimeout(function(){
                            location.reload();
                        }, 1500);

                    });

                });
        });
        
</script>
<style>
    table.table-materio th.cabecera-cell {
        border: none;
        padding: 10px 5px;
        font-size: x-small;
        border-left: 1px solid #dee2e6;
        background-color: #139b92 !important;
    }
    table.table-materio th.variable-cell {
        border: none;
        background-color: #086375 !important;
    }
    table.table-materio th.subcabecera-cell {
        border: none;
        border-left: 1px solid #dee2e6;
        padding: 10px 5px;
        font-size: x-small
    }
    table.table-materio td.subvariable-cell {
        background: hsl(199, 12%, 45%) !important;
        padding: 5px 10px !important;
    }
    table.table-materio td.data {
        padding: 5px 10px !important;
    }
    .btn-group .btn{
        border: none;
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }
    .btn{
            border: none;
            border-radius: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
    }
    
</style>
@endsection
