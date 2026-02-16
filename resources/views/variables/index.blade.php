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

<button id="btnNewData" type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#dataModal">
    <i class="ri ri-add-line fs-4"></i>
    Nueva Variable
</button>

    <div class="table-responsive">
        <table id="dataTable" class="table table-striped table-materio">
            <thead>
            <tr>
                <th>Descripcion</th> 
                <th>Subvariables</th>
                <th>Cabeceras</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($variables as $variable)
                <tr class="variable-row" data-variable-id="{{ $variable->id }}" style="cursor: pointer;">
                    <td>{{ $variable->descripcion }}</td>
                    <td class="subvariables-cell" id="subvariables-{{ $variable->id }}">
                        <span class="text-muted">Clic para cargar...</span>
                    </td>
                    <td class="cabeceras-cell" id="cabeceras-{{ $variable->id }}">
                        <span class="text-muted">Clic para cargar...</span>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-icon edit-data"
                                data-id="{{ $variable->id }}"
                                data-descripcion="{{ $variable->descripcion }}"
                                onclick="event.stopPropagation();">
                            <i class="ri ri-pencil-line fs-4 bg-success"></i>
                        </button>

                        <form action="{{ route('variables.destroy', $variable) }}" method="POST" class="d-inline" onclick="event.stopPropagation();">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-icon" type="submit"
                                onclick="return confirm('¿Eliminar variable?')">
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

<script>
    // Handle row click to load subvariables and cabeceras
    $(document).on('click', '.variable-row', function () {
        var variableId = $(this).data('variable-id');
        var subvariablesCell = $('#subvariables-' + variableId);
        var cabecerasCell = $('#cabeceras-' + variableId);

        // Load subvariables
        $.ajax({
            url: "{{ url('variables') }}/" + variableId + "/subvariables",
            type: 'GET',
            success: function (res) {
                if (res.subvariables.length > 0) {
                    var html = '<ul class="mb-0 list-unstyled">';
                    res.subvariables.forEach(function (subvar) {
                        html += '<li><small>' + subvar.descripcion + '</small></li>';
                    });
                    html += '</ul>';
                    subvariablesCell.html(html);
                } else {
                    subvariablesCell.html('<span class="text-muted">Sin subvariables</span>');
                }
            },
            error: function () {
                subvariablesCell.html('<span class="text-danger">Error al cargar</span>');
            }
        });

        // Load cabeceras
        $.ajax({
            url: "{{ url('variables') }}/" + variableId + "/cabeceras",
            type: 'GET',
            success: function (res) {
                if (res.cabeceras.length > 0) {
                    var html = '<ul class="mb-0 list-unstyled">';
                    res.cabeceras.forEach(function (cabecera) {
                        html += '<li><small>' + cabecera.descripcion + '</small></li>';
                    });
                    html += '</ul>';
                    cabecerasCell.html(html);
                } else {
                    cabecerasCell.html('<span class="text-muted">Sin cabeceras</span>');
                }
            },
            error: function () {
                cabecerasCell.html('<span class="text-danger">Error al cargar</span>');
            }
        });
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
