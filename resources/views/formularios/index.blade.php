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

<button id="btnNewUser" type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#dataModal">
    <i class="ri ri-add-line fs-4"></i>
    Nuevo Formulario
</button>

    <div class="table-responsive">
        <table id="dataTable" class="table table-sm small table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Nombre</th>
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
                    <td class="data">{{ $formulario->nombre }}</td>
                    <td class="data">{{ $gestiones->where('id', $formulario->gestion_id)->first()->nombre ?? 'Sin gestión' }}</td>
                    <td class="data">{{ $formulario->created_at->format('d/m/Y') }}</td>
                    <td>
                        <!-- <button type="button"
                                class="btn btn-icon edit-data"
                                data-id="{{ $formulario->id }}"
                                data-nombre="{{ $formulario->nombre }}"
                                data-gestion_id="{{ $formulario->gestion_id }}">
                            <i class="ri ri-pencil-line fs-4 bg-success"></i>
                        </button> -->
                        <button type="button"
                                class="btn btn-icon"> 
                            <a href="{{ route('formularios.asignarVariables', $formulario) }}" class="btn btn-icon">
                                <i class="ri ri-pencil-line fs-4 bg-success"></i>
                            </a>
                        </button>

                        <form action="{{ route('formularios.destroy', $formulario) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-icon" type="submit"
                                onclick="return confirm('¿Eliminar formulario?')">
                                <i class="ri ri-delete-bin-line fs-4 bg-danger"></i>
                            </button>
                        </form>
                        <button class="btn btn-icon">
                            <a href="{{ route('formularios.show', $formulario) }}" class="btn btn-icon">
                                <i class="ri ri-eye-line fs-4 bg-info"></i>
                            </a>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
<script>
    $(function () {
        $("#dataTable").DataTable({
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

        }).buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');
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
