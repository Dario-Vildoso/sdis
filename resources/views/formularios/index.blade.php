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
    <div class="col-md-4 mb-4">
        <div class="card card-action">
            <div class="card-body">
                <h5 class="card-title">Crear un nuevo formulario</h5>
            </div>
            <div class="card-footer">
                <button id="btnNewData" type="button" class="btn btn-card-action" data-bs-toggle="modal" data-bs-target="#dataModal">
                    <i class="ri ri-add-line fs-5"></i>&nbsp;&nbsp;&nbsp;Nuevo Formulario
                </button>
            </div>
        </div>
    </div>
<div class="card" style="padding: 20px;">
    <div class="table-responsive">
        <table id="dataTable" class="table table-sm table-bordered table-stripped small table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Formulario</th>
                <th>Gestion</th>
                <th>Fecha de Creacion</th>
                <th class="actions">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($formularios as $formulario)
                <tr>
                    <td class="id-cell">
                        <div class="text-center d-flex align-items-center justify-content-center">
                            <strong>{{ $formulario->id }}</strong>
                        </div>
                    </td>
                    <td class="data">{{ $formulario->sistema->nombre ?? 'Sin sistema' }}</td>
                    <td class="data">{{ $formulario->gestion->nombre ?? 'Sin gestión' }}</td>
                    <td class="data">{{ $formulario->created_at->format('d/m/Y') }}</td>
                    <td width="125px">
                        <div class="dropdown">
                            <button type="button"
                                    class="btn-icon"> 
                                <a href="{{ route('formularios.asignarVariables', $formulario) }}">
                                    <i class="ri ri-pencil-line bg-success"></i>
                                </a>
                            </button>
                            <button type="button" 
                                    class="btn-icon">
                                <a href="{{ route('formularios.show', $formulario) }}">
                                    <i class="ri ri-eye-line bg-info"></i>
                                </a>
                            </button>
                            <form action="{{ route('formularios.destroy', $formulario) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn-icon" type="submit"
                                    onclick="return confirm('¿Eliminar formulario?')">
                                    <i class="ri ri-delete-bin-line bg-danger"></i>
                                </button>
                            </form>
                        </div>
                    </td>
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
                    <h5 class="modal-title" id="formModalLabel">Nuevo formulario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        {{-- Descripción --}}
                        <div class="col-md-8 mb-3">
                            <label for="formNombre" class="form-label">Nombre</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success">
                                    <i class="ri ri-file-line"></i>
                                </span>
                                <input type="text"
                                    class="form-control"
                                    id="formNombre"
                                    name="nombre"
                                    value="{{ Auth::user()->sistema->nombre ?? '' }}"
                                    disabled
                                    required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="formGestion" class="form-label">Gestión</label>
                            <div class="input-group">
                                <span class="input-group-text bg-info">
                                    <i class="ri ri-calendar-line"></i>
                                </span>
                                <select name="gestion_id" id="formGestion" class="form-select">
                                    <option value="" disabled required></option>
                                    @foreach($gestiones as $gestion)
                                        <option value="{{ $gestion->id }}">{{ $gestion->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
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
                $(document).on('click', '.edit-data', function () {
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
