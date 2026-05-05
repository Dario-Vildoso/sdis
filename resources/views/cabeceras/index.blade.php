@extends('layouts.materio')

@section('title', 'Cabeceras')

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
<div class="row justify-content-end">
@if (Auth::user()->hasPermissionTo('variables.management'))
    <div class="col-md-4 mb-4">
        <div class="card card-action">
            <div class="card-body">
                <h5 class="card-title">Crear una nueva cabecera</h5>
            </div>
            <div class="card-footer">
                <button id="btnNewData" type="button" class="btn btn-card-action" data-bs-toggle="modal" data-bs-target="#dataModal">
                    <i class="ri ri-add-line fs-5"></i>&nbsp;&nbsp;&nbsp;Nueva Cabecera
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
                <th>Descripción</th> 
                <th>Variables</th>
                <th>Cabecera Superior</th>
                @if (Auth::user()->hasPermissionTo('variables.management'))
                <th class="actions">Acciones</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @php
                // Agrupar cabeceras por variable_id
                $cabecerasGrouped = $cabeceras->groupBy('variable_id');
                
                // Array de colores para badges
                $badgeColors = ['bg-label-primary', 'bg-label-success', 'bg-label-warning', 'bg-label-danger', 'bg-label-info', 'bg-label-secondary'];
                $colorIndex = 0;
                $variableColors = [];
            @endphp
            
            @foreach($cabecerasGrouped as $variableId => $cabs)
                @php
                    // Asignar color a esta variable si no lo tiene
                    if (!isset($variableColors[$variableId])) {
                        $variableColors[$variableId] = $badgeColors[$colorIndex % count($badgeColors)];
                        $colorIndex++;
                    }
                    $color = $variableColors[$variableId];
                    $variableDesc = $cabs->first()->variable_descripcion;
                @endphp
                
                @foreach($cabs as $cabecera)
                    <tr class="variable-row" data-variable-id="{{ $cabecera->id }}">
                        <td class="text-muted small id-cell">
                            <div>
                                <strong>{{ $cabecera->id }}</strong>
                            </div>
                        </td>
                        @php
                            $cabeceraPadre = $cabeceras->firstWhere('id', $cabecera->cabecera_id);
                            $cabecerasHijas = $cabeceras->where('cabecera_id', $cabecera->id)->count();
                        @endphp
                        <td class="data">{{ $cabecera->descripcion }}
                            @if($cabecerasHijas > 0)
                                <span class="badge bg-warning">{{ $cabecerasHijas }} hijas</span>
                            @endif
                        </td>
                        <td class="data">
                            <span class="badge {{ $color }}">{{ $variableDesc }}</span>
                        </td>
                        <td class="data">
                            {{ $cabeceraPadre ? $cabeceraPadre->descripcion : 'Sin Cabecera Asignada' }}
                        @if (Auth::user()->hasPermissionTo('variables.management'))
                        <td>
                            <button type="button"
                            
                                    class="btn-icon edit-data"
                                    data-id="{{ $cabecera->id }}"
                                    data-descripcion="{{ $cabecera->descripcion }}"
                                    data-variable-id="{{ $cabecera->variable_id }}"
                                    data-cabecera-id="{{ $cabecera->cabecera_id }}"
                                    onclick="event.stopPropagation();">
                                <i class="ri ri-pencil-line fs-4 bg-success"></i>
                            </button>
        
                            <form action="{{ route('cabeceras.destroy', $cabecera) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn-icon" type="submit"
                                    onclick="return confirm('¿Eliminar cabecera?')">
                                    <i class="ri ri-delete-bin-line fs-4 bg-danger"></i>
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                @endforeach
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
            <form id="dataForm" action="{{ route('cabeceras.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="dataModalLabel">Nueva Cabecera</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        {{-- Descripción --}}
                        <div class="col-md-12 mb-3">
                            <label for="cabeceraDescripcion" class="form-label">Descripción</label>
                            <input type="text"
                                class="form-control"
                                id="cabeceraDescripcion"
                                name="descripcion"
                                required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="cabeceraVariable" class="form-label">Variable</label>
                            <select name="variable_id" id="cabeceraVariable" class="form-select" required>
                                <option value="" disabled selected>Seleccione una variable</option>
                                @foreach($variables as $variable)
                                    <option value="{{ $variable->id }}">{{ $variable->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="cabeceraCabecera" class="form-label">Cabecera</label>
                            <select name="cabecera_id" id="cabeceraCabecera" class="form-select" disabled>
                                <option value="">Sin Cabecera Asignada</option>
                            </select>
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
                var baseUrl = "{{ url('cabeceras') }}";

                // Mapa de cabeceras por variable: variable_id => [...cabeceras]
                var cabecerasByVariable = @json($cabeceras->groupBy('variable_id'));

                // Función para renderizar opciones en árbol
                function renderTreeOptions(cabeceras, selectedId = null) {
                    var html = '<option value="">Sin Cabecera Asignada</option>';
                    
                    // Construir mapa de hijos
                    var children = {};
                    cabeceras.forEach(function(c) {
                        var parentId = c.cabecera_id || 0;
                        if (!children[parentId]) {
                            children[parentId] = [];
                        }
                        children[parentId].push(c);
                    });

                    // Función recursiva para renderizar
                    function renderBranch(parentId, level) {
                        if (!children[parentId]) return '';
                        
                        var count = children[parentId].length;
                        var result = '';
                        
                        children[parentId].forEach(function(child, index) {
                            var prefix = '';
                            if (level > 0) {
                                for (var i = 0; i < level - 1; i++) {
                                    prefix += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                }
                                prefix += (index === count - 1) ? '└─ ' : '├─ ';
                            }
                            
                            var selected = (selectedId && selectedId == child.id) ? ' selected' : '';
                            result += '<option value="' + child.id + '"' + selected + '>' + prefix + child.descripcion + '</option>';
                            result += renderBranch(child.id, level + 1);
                        });
                        
                        return result;
                    }
                    
                    html += renderBranch(0, 0);
                    return html;
                }

                // Manejador de cambio de variable
                $('#cabeceraVariable').on('change', function() {
                    var variableId = $(this).val();
                    var cabeceras = cabecerasByVariable[variableId] || [];
                    
                    if (cabeceras.length > 0) {
                        // Habilitar select de cabecera y llenar opciones
                        $('#cabeceraCabecera').html(renderTreeOptions(cabeceras));
                        $('#cabeceraCabecera').prop('disabled', false);
                    } else {
                        // Si no hay cabeceras de esta variable, solo mostrar "Sin Cabecera Asignada"
                        $('#cabeceraCabecera').html('<option value="">Sin Cabecera Asignada</option>');
                        $('#cabeceraCabecera').prop('disabled', true);
                        $('#cabeceraCabecera').val('');
                    }
                });

                // New establishment: reset form
                $('#btnNewData').on('click', function () {
                        $('#dataModalLabel').text('Nueva Cabecera');
                        $('#dataForm').attr('action', "{{ route('cabeceras.store') }}");
                        $('#formMethod').val('');
                        $('#cabeceraDescripcion').val('');
                        $('#cabeceraVariable').val('').change();
                        $('#cabeceraCabecera').val('').prop('disabled', true);
                        $('#dataSubmit').text('Crear');
                });

                // Edit establishment: populate and set action
                $('.edit-data').on('click', function () {
                        var id = $(this).data('id');
                        var descripcion = $(this).data('descripcion');
                        var variableId = $(this).data('variable-id');
                        var cabeceraId = $(this).data('cabecera-id');

                        $('#dataModalLabel').text('Editar Cabecera');
                        $('#dataForm').attr('action', baseUrl + '/' + id);
                        $('#formMethod').val('PUT');
                        $('#cabeceraDescripcion').val(descripcion);
                        
                        // Setear la variable y triggear change para llenar cabeceras
                        $('#cabeceraVariable').val(variableId).change();
                        
                        // Después de que change() se ejecute, setear la cabecera seleccionada
                        setTimeout(function() {
                            $('#cabeceraCabecera').val(cabeceraId);
                        }, 10);

                        $('#dataSubmit').text('Actualizar');

                        $('#dataModal').modal('show');
                });

                $('#dataForm').on('submit', function() {
                    $('#dataSubmit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
                });
        });
</script>
@endsection
