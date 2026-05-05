@extends('layouts.materio')

@section('title', 'Variables')

@section('content')

@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Exito',
            text: "{{ session('success') }}",
            timer: 1000,
            showConfirmButton: false
        });
    </script>
@endif
<div class="row justify-content-end">
    @if (Auth::user()->hasPermissionTo('variables.management'))
    <div class="col-md-4 mb-4">
        <div class="card card-action">
            <div class="card-body">
                <h5 class="card-title">Crear una nueva Variable</h5>
            </div>
            <div class="card-footer">
                <button id="btnNewData" type="button" class="btn btn-card-action" data-bs-toggle="modal" data-bs-target="#dataModal">
                    <i class="ri ri-add-line fs-5"></i>&nbsp;&nbsp;&nbsp;Nueva Variable
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="card" style="padding: 20px;">

    <div class="table-responsive">
        <table id="dataTable" class="table table-sm table-bordered table-stripped small table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Descripcion</th> 
                <th>Subvariables</th>
                <th>Cabeceras</th>
                @if (Auth::user()->hasPermissionTo('variables.management'))
                <th class="actions">Acciones</th>
                @endif
            </tr>
            </thead>
            <tbody class="table-border-bottom-0">
            @foreach($variables as $variable)
                <tr class="variable-row">
                    <td class="text-muted small id-cell">
                        <div class="text-center d-flex align-items-center justify-content-center">
                            <strong>{{ $variable->id }}</strong>
                        </div>
                    </td>
                    <td class="data">{{ $variable->descripcion }}</td>
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
                    @if (Auth::user()->hasPermissionTo('variables.management'))
                    <td width="125px">
                        <a href="{{ route('variables.tabla', $variable) }}" 
                           class="btn-icon" 
                           title="Ver tabla">
                            <i class="ri ri-table-2 fs-4 bg-info text-white rounded-2" style="padding: 6px;"></i>
                        </a>

                        <button type="button"
                                class="btn-icon edit-data"
                                data-id="{{ $variable->id }}"
                                data-descripcion="{{ $variable->descripcion }}"
                                onclick="event.stopPropagation();">
                            <i class="ri ri-pencil-line fs-4 bg-success"></i>
                        </button>
                        @if (Auth::user()->hasRole('super-admin'))
                        
                        <form action="{{ route('variables.destroy', $variable) }}" method="POST" class="d-inline" onclick="event.stopPropagation();">
                            @csrf
                            @method('DELETE')
                            <button class=" btn-icon" type="submit"
                                onclick="return confirm('¿Eliminar variable?')">
                                <i class="ri ri-delete-bin-line fs-4 bg-danger"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
$(function () {
    var table = $("#dataTable").DataTable({
        "pageLength": 10,                    // Cambié de 6 a 10 (más estándar)
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
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
            "search": '<i class="ri ri-search-line"></i>',
            "searchPlaceholder": "Buscar...",
            "zeroRecords": "Sin resultados encontrados",
            
            "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
            }
            
        },
        
        "responsive": true, "lengthChange": true, "autoWidth": false,

        
        // === BOTONES (Exportar + Acciones) ===
        "dom": 'Bfrtip',
        
        "buttons": [
            {
                extend: 'collection',
                text: '<i class="ri ri-file-excel-line"></i> Exportar',
                className: 'btn-success btn-sm',
                buttons: [
                    { extend: 'excelHtml5', text: 'Excel (.xlsx)', exportOptions: { columns: ':visible' } },
                    { extend: 'csvHtml5',   text: 'CSV', exportOptions: { columns: ':visible' } },
                    { extend: 'pdfHtml5',   text: 'PDF', exportOptions: { columns: ':visible' } },
                    { extend: 'copyHtml5',  text: 'Copiar al portapapeles' }
                ]
            }
        ],
        
        // Opciones recomendadas para mejor experiencia
        "pagingType": "full_numbers",
        "scrollX": true,                    // Útil si tienes muchas columnas
        "stateSave": true,                  // Recuerda la página, filtros y orden al recargar
    });

    // Mover los botones a la izquierda (opcional)
    table.buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');
});
</script>

<!-- Modal: Crear / Editar Formulario -->
<div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="dataForm" action="{{ route('formularios.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="dataModalLabel">Nueva Variable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        {{-- Descripción --}}
                        <div class="col-md-12 mb-3">
                            <label for="varDescripcion" class="form-label">Descripción</label>
                            <input type="text"
                                class="form-control"
                                id="varDescripcion"
                                name="descripcion"
                                required>
                        </div>

                    </div>
                    
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="dataSubmit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
        $(function () {
                // Base URL for update action
                var baseUrl = "{{ url('variables') }}";

                // New establishment: reset form
                $('#btnNewData').on('click', function () {
                        $('#dataModalLabel').text('Nueva Variable');
                        $('#dataForm').attr('action', "{{ route('variables.store') }}");
                        $('#formMethod').val(''); // Limpia el método para creación
                        $('#varDescripcion').val('');
                        $('#dataSubmit').text('Crear');
                });

                // Edit establishment: populate and set action
                $('.edit-data').on('click', function () {
                        var id = $(this).data('id');
                        var descripcion = $(this).data('descripcion');

                        $('#dataModalLabel').text('Editar Variable');
                        $('#dataForm').attr('action', baseUrl + '/' + id);
                        $('#formMethod').val('PUT'); // Cambia el método a PUT para actualización
                        $('#varDescripcion').val(descripcion);
                        $('#dataSubmit').text('Actualizar');

                        $('#dataModal').modal('show');
                });
                $('#dataForm').on('submit', function() {
                    $('#dataSubmit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
                    
                });
        });
</script>
@endsection
