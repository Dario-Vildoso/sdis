@extends('layouts.materio')

@section('title', 'Usuarios')

@section('content')

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ session('error') }}",
            timer: 2500,
            showConfirmButton: false
        });
    </script>
@endif
<div class="card" style="padding: 20px;">
    <div class="table-responsive">
        <table id="dataTable" class="table table-sm table-bordered table-stripped small table-materio">
            <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripcion</th>
                <th>Gestion</th>
                <th>Periodo</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th class="actions">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($formularios as $formulario)
            @foreach ($formulario->periodos as $periodo)
            
            <tr>
                <td class="data">{{ $formulario->sistema->nombre ?? 'Sin sistema' }}</td>
                <td class="data">{{ $formulario->descripcion ?? 'Sin descripción' }}</td>
                <td class="data">{{ $gestiones->where('id', $formulario->gestion_id)->first()->nombre ?? 'Sin gestión' }}</td>
                <td class="data">{{ $periodo->descripcion }}</td>
                <td class="data">{{ $periodo->fecha_inicio }}</td>
                <td class="data">{{ $periodo->fecha_fin }}</td>
                <td style="width: 20px">
                        <!-- <button type="button"
                        class="btn btn-icon edit-data"
                        data-id="{{ $formulario->id }}"
                        data-nombre="{{ $formulario->nombre }}"
                        data-gestion_id="{{ $formulario->gestion_id }}">
                        <i class="ri ri-pencil-line fs-4 bg-success"></i>
                    </button> -->
                    @if ((now() > $periodo->fecha_inicio && now() < $periodo->fecha_fin) || $periodo->estado)
                    <button type="button" 
                        class="btn-icon"> 
                        <a href="{{ route('formularios.periodo', [$formulario, $periodo]) }}" class="btn-icon">
                            <i class="ri ri-pencil-line fs-4 bg-info"></i>
                        </a>
                    </button>
                    @else
                    <button type="button" 
                        disabled
                        class="btn btn-icon"> 
                        <a href="{{ route('formularios.periodo', [$formulario, $periodo]) }}" class="btn-icon">
                            <i class="ri ri-pencil-line fs-4 bg-info"></i>
                        </a>
                    </button>
                    @endif
            </td>
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
            <form id="dataForm" action="{{ route('formularios.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel">Nuevo formulario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        {{-- Descripción --}}
                        <div class="col-md-8 mb-3">
                            <label for="formNombre" class="form-label">Nombre</label>
                            <input type="text"
                                class="form-control"
                                id="formNombre"
                                name="nombre"
                                required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="formGestion" class="form-label">Gestión</label>
                            <select name="gestion_id" id="formGestion" class="form-select">
                                <option value="" disabled required></option>
                                @foreach($gestiones as $gestion)
                                    <option value="{{ $gestion->id }}">{{ $gestion->nombre }}</option>
                                @endforeach
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
@endsection
