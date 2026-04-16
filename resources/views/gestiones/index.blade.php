@extends('layouts.materio')

@section('title', 'Usuarios')

@section('content')

@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: "{{ session('success') }}",
            timer: 2500,
            showConfirmButton: false
        });
    </script>
@endif

<div class="col-md-4 mb-4">
        <div class="card card-action">
            <div class="card-body">
                <h5 class="card-title">Crear nueva gestión</h5>
            </div>
            <div class="card-footer">
                <button id="btnNewData" type="button" class="btn btn-card-action" data-bs-toggle="modal" data-bs-target="#dataModal">
                    <i class="ri ri-add-line fs-5"></i>&nbsp;Nueva Gestion
                </button>
            </div>
        </div>
    </div>
<div class="card" style="padding: 20px">
    <div class="table-responsive">
        <table id="dataTable" class="table table-sm table-bordered table-stripped small table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Año</th>
                <th class="actions">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($gestiones as $gestion)
                <tr>
                    <td class="id-cell">
                        <div class="text-center d-flex align-items-center justify-content-center">
                            <strong>{{ $gestion->id }}</strong>
                        </div>
                    </td>
                    <td class="data">{{ $gestion->nombre }}</td>
                    <td>
                        <button type="button"
                                class="btn-icon edit-data"
                                data-id="{{ $gestion->id }}"
                                data-nombre="{{ $gestion->nombre }}">
                            <i class="ri ri-pencil-line fs-4 bg-success"></i>
                        </button>

                        <form action="{{ route('gestiones.destroy', $gestion) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn-icon" type="submit"
                                onclick="return confirm('¿Eliminar gestion?')">
                                <i class="ri ri-delete-bin-line fs-4 bg-danger"></i>
                            </button>
                        </form>
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
        "pageLength": 5,                    // Cambié de 6 a 10 (más estándar)
        "lengthMenu": [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todos"]],
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
                        <div class="col-md-12 mb-3">
                            <label for="gestionNombre" class="form-label">Año</label>
                            <select name="nombre" id="gestionNombre" class="form-select">
                                <option value="" disabled></option>
                                @for ($i = now()->year+1; $i >= now()->year-5; $i--)
                                    <option value="{{ $i }}" {{ $i === now()->year ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
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
                $('#btnNewData').on('click', function () {
                        $('#formModalLabel').text('Nueva Gestión');
                        $('#dataForm').attr('action', "{{ route('gestiones.store') }}");
                        $('#formMethod').val(''); // Limpia el método para creación
                        $('#formNombre').val('');
                        $('#dataSubmit').text('Crear');
                });

                // Edit establishment: populate and set action
                $('.edit-data').on('click', function () {
                        var id = $(this).data('id');
                        var nombre = $(this).data('nombre');

                        $('#formModalLabel').text('Editar gestión');
                        $('#dataForm').attr('action', baseUrl + '/' + id);
                        $('#formMethod').val('PUT'); // Cambia el método a PUT para actualización
                        $('#formNombre').val(nombre);
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
