@extends('layouts.materio')

@section('title', 'Establecimientos')

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

    <div class="col-md-4 mb-4">
        <div class="card card-action">
            <div class="card-body">
                <h5 class="card-title">Crear un nuevo establecimiento</h5>
            </div>
            <div class="card-footer">
                <button id="btnNewEst" type="button" class="btn btn-card-action" data-bs-toggle="modal" data-bs-target="#estModal">
                    <i class="ri ri-add-line fs-5"></i>&nbsp;&nbsp;&nbsp;Nuevo Establecimiento
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card" style="padding: 20px;">
    <div class="table-responsive">
        <table id="estsTable" class="table table-sm table-bordered table-stripped small table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Nombre</th>
                <th>Municipio</th>
                <th>Codigo</th>
                <th>Nivel</th>
                @if (Auth::user()->hasPermissionTo('establecimientos.management'))
                <th>Estado</th>
                <th class="actions">Acciones</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($establecimientos as $establecimiento)
                <tr>
                    <td class="id-cell">
                        <div class="text-center d-flex align-items-center justify-content-center">
                            <strong>{{ $establecimiento->id }}</strong>
                        </div>
                    </td>
                    <td>{{ $establecimiento->descripcion }}</td>
                    <td>{{ $establecimiento->municipio->nombre }}</td>
                    <td>{{ $establecimiento->codigo_est }}</td>
                    <td>{{ $establecimiento->nivel }}º Nivel</td>
                    @if (Auth::user()->hasPermissionTo('establecimientos.management'))
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-estado" type="checkbox" role="switch" id="toggle-estado-{{ $establecimiento->id }}" data-id="{{ $establecimiento->id }}" {{ $establecimiento->estado ? 'checked' : '' }}>
                        </div>
                    </td>
                    <td>
                        <button type="button"
                                class="btn-icon edit-est"
                                data-id="{{ $establecimiento->id }}"
                                data-descripcion="{{ $establecimiento->descripcion }}"
                                data-municipio="{{ $establecimiento->id_mun }}"
                                data-codigo="{{ $establecimiento->codigo_est }}"
                                data-nivel="{{ $establecimiento->nivel }}">
                            <i class="ri ri-pencil-line fs-4 bg-success"></i>
                        </button>
                        @if (Auth::user()->hasRole('super-admin'))
                        <form action="{{ route('establecimientos.destroy', $establecimiento) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn-icon" type="submit"
                                onclick="return confirm('¿Eliminar establecimiento?')">
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
    // AJAX setup for CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(function () {
        // Handle toggle change
        $(document).on('change', '.toggle-estado', function () {
            var checkbox = $(this);
            var id = checkbox.data('id');
            var url = "{{ url('establecimientos') }}" + '/' + id + '/toggle';

            // Optimistic UI: disable during request
            checkbox.prop('disabled', true);

            var token = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: token,
                    _method: 'PATCH'
                },
                success: function (res) {
                    Swal.fire({
                        toast: true,
                        position: 'bottom-end',
                        icon: 'success',
                        title: 'Estado actualizado',
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function (xhr) {
                    // Revert checkbox on error
                    checkbox.prop('checked', !checkbox.prop('checked'));
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo actualizar el estado.'
                    });
                },
                complete: function () {
                    checkbox.prop('disabled', false);
                }
            });
        });
    });
</script>
<script>
$(function () {
    var table = $("#estsTable").DataTable({
        "pageLength": 10,                    // Cambié de 6 a 10 (más estándar)
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        "order": [],
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
        // "stateSave": true,                  // Recuerda la página, filtros y orden al recargar
    });

    // Mover los botones a la izquierda (opcional)
    table.buttons().container().appendTo('#estsTable_wrapper .col-md-6:eq(0)');
});
</script>

<!-- Modal: Crear / Editar Establecimiento -->
<div class="modal fade" id="estModal" tabindex="-1" aria-labelledby="estModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="estForm" action="{{ route('establecimientos.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="estModalLabel">Nuevo establecimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        {{-- Descripción --}}
                        <div class="col-md-8 mb-3">
                            <label for="estDescripcion" class="form-label">Nombre</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success"><i class="ri ri-edit-line"></i></span>
                                <input type="text"
                                    class="form-control"
                                    id="estDescripcion"
                                    name="descripcion"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="estMunicipio" class="form-label">Municipio</label>
                            <div class="input-group">
                                <span class="input-group-text bg-warning"><i class="ri ri-map-pin-2-line"></i></span>
                                <select class="form-select"
                                        id="estMunicipio"
                                        name="id_mun"
                                        required>
                                    <option value="" disabled selected>Seleccione un municipio</option>
                                    @foreach($municipios as $municipio)
                                        <option value="{{ $municipio->id }}">
                                            {{ $municipio->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="estNivel" class="form-label">Nivel</label>
                            <div class="input-group">
                                <span class="input-group-text bg-warning"><i class="ri ri-star-line"></i></span>
                                <select class="form-select" id="estNivel" name="nivel" required>
                                    <option value="" disabled selected>Nivel</option>
                                    <option value="1">Nivel 1</option>
                                    <option value="2">Nivel 2</option>
                                    <option value="3">Nivel 3</option>
                                    <option value="4">Nivel 4</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {{-- Código y Gestión en la misma fila --}}
                        <div class="col-md-6 mb-3">
                            <label for="estCodigo" class="form-label">Código</label>
                            <div class="input-group">
                                <span class="input-group-text bg-info"><i class="ri ri-shield-check-line"></i></span>
                                <input type="text"
                                    class="form-control"
                                    id="estCodigo"
                                    name="codigo_est"
                                    required>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="estSubmit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
        $(function () {
                // Base URL for update action
                var baseUrl = "{{ url('establecimientos') }}";

                // New establishment: reset form
                $('#btnNewEst').on('click', function () {
                        $('#estModalLabel').text('Nuevo establecimiento');
                        $('#estForm').attr('action', "{{ route('establecimientos.store') }}");
                        $('#formMethod').val(''); // Limpia el método para creación
                        $('#estDescripcion').val('');
                        $('#estMunicipio').val('');
                        $('#estCodigo').val('');
                        $('#estSubmit').text('Crear');
                });

                // Edit establishment: populate and set action
                $(document).on('click', '.edit-est', function () {
                        var id = $(this).data('id');
                        var descripcion = $(this).data('descripcion');
                        var municipio = $(this).data('municipio');
                        var codigo = $(this).data('codigo');
                        var nivel = $(this).data('nivel');

                        $('#estModalLabel').text('Editar establecimiento');
                        $('#estForm').attr('action', baseUrl + '/' + id);
                        $('#formMethod').val('PUT'); // Cambia el método a PUT para actualización
                        $('#estDescripcion').val(descripcion);
                        $('#estMunicipio').val(municipio);
                        $('#estCodigo').val(codigo);
                        $('#estNivel').val(nivel);
                        $('#estSubmit').text('Actualizar');

                        $('#estModal').modal('show');
                });
                $('#estForm').on('submit', function() {
                    $('#estSubmit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
                    
                });
        });
</script>
@endsection
