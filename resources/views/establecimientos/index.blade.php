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

<button id="btnNewUser" type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#estModal">
    <i class="ri ri-add-line fs-4"></i>
    Nuevo Establecimiento
</button>

    <div class="table-responsive">
        <table id="estsTable" class="table table-sm small table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Nombre</th>
                <th>Municipio</th>
                <th>Codigo</th>
                <th>Gestion</th>
                <th class="actions">Acciones</th>
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
                    <td class="data">{{ $establecimiento->descripcion }}</td>
                    <td class="data">{{ $municipios[$establecimiento->id_mun-1]->nombre }}</td>
                    <td class="data">{{ $establecimiento->codigo_est }}</td>
                    <td class="data">{{ $establecimiento->gestion }}</td>
                    <td>
                        <button type="button"
                                class="btn btn-icon edit-est"
                                data-id="{{ $establecimiento->id }}"
                                data-descripcion="{{ $establecimiento->descripcion }}"
                                data-municipio="{{ $establecimiento->id_mun }}"
                                data-codigo="{{ $establecimiento->codigo_est }}"
                                data-gestion="{{ $establecimiento->gestion }}">
                            <i class="ri ri-pencil-line fs-4 bg-success"></i>
                        </button>

                        <form action="{{ route('establecimientos.destroy', $establecimiento) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-icon" type="submit"
                                onclick="return confirm('¿Eliminar establecimiento?')">
                                <i class="ri ri-delete-bin-line fs-4 bg-danger"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
<script>
    $(function () {
        $("#estsTable").DataTable({
            "pageLength": 6,
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

        }).buttons().container().appendTo('#estsTable_wrapper .col-md-6:eq(0)');
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
                            <label for="estDescripcion" class="form-label">Descripción</label>
                            <input type="text"
                                class="form-control"
                                id="estDescripcion"
                                name="descripcion"
                                required>
                        </div>

                        {{-- Municipio --}}
                        <div class="col-md-6 mb-3">
                            <label for="estMunicipio" class="form-label">Municipio</label>
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
                    <div class="row">
                        {{-- Código y Gestión en la misma fila --}}
                        <div class="col-md-6 mb-3">
                            <label for="estCodigo" class="form-label">Código</label>
                            <input type="text"
                                class="form-control"
                                id="estCodigo"
                                name="codigo_est"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="estGestion" class="form-label">Gestión</label>
                            <input type="text"
                                class="form-control"
                                id="estGestion"
                                name="gestion"
                                required>
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
                        $('#estGestion').val('');
                        $('#estSubmit').text('Crear');
                });

                // Edit establishment: populate and set action
                $('.edit-est').on('click', function () {
                        var id = $(this).data('id');
                        var descripcion = $(this).data('descripcion');
                        var municipio = $(this).data('municipio');
                        var codigo = $(this).data('codigo');
                        var gestion = $(this).data('gestion');

                        $('#estModalLabel').text('Editar establecimiento');
                        $('#estForm').attr('action', baseUrl + '/' + id);
                        $('#formMethod').val('PUT'); // Cambia el método a PUT para actualización
                        $('#estDescripcion').val(descripcion);
                        $('#estMunicipio').val(municipio);
                        $('#estCodigo').val(codigo);
                        $('#estGestion').val(gestion);
                        $('#estSubmit').text('Actualizar');

                        var estModal = new bootstrap.Modal(document.getElementById('estModal'));
                        estModal.show();
                });
                $('#estForm').on('submit', function() {
                    $('#estSubmit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
                    
                });
        });
</script>
@endsection
