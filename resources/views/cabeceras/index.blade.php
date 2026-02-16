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
    Nueva Cabecera
</button>

    <div class="table-responsive">
        <table id="dataTable" class="table table-striped table-materio">
            <thead>
            <tr>
                <th>Descripcion</th> 
                <th>variables</th>
                <th>Cabeceras</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cabeceras as $cabecera)
                <tr class="variable-row" data-variable-id="{{ $cabecera->id }}" style="cursor: pointer;">
                    <td>{{ $cabecera->descripcion }}</td>
                    <td>{{ $cabecera->variable_descripcion }}</td>
                    <td>
                        @php
                            $cabeceraPadre = $cabeceras->firstWhere('id', $cabecera->cabecera_id);
                        @endphp
                        {{ $cabeceraPadre ? $cabeceraPadre->descripcion : 'Sin Cabecera Asignada' }}
                    <td>
                        <button type="button"
                                class="btn btn-icon edit-data"
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
                            <button class="btn btn-icon" type="submit"
                                onclick="return confirm('¿Eliminar cabecera?')">
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
                            <select name="cabecera_id" id="cabeceraCabecera" class="form-select">
                                <option value="" selected>Sin Cabecera Asignada</option>
                                @foreach($cabeceras as $cabecera)
                                    <option value="{{ $cabecera->id }}">{{ $cabecera->descripcion }}</option>
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
                var baseUrl = "{{ url('cabeceras') }}";

                // New establishment: reset form
                $('#btnNewData').on('click', function () {
                        $('#dataModalLabel').text('Nueva Cabecera');
                        $('#dataForm').attr('action', "{{ route('cabeceras.store') }}");
                        $('#formMethod').val(''); // Limpia el método para creación
                        $('#cabeceraDescripcion').val('');
                        $('#cabeceraVariable').val('');
                        $('#cabeceraCabecera').val('');
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
                        $('#formMethod').val('PUT'); // Cambia el método a PUT para actualización
                        $('#cabeceraDescripcion').val(descripcion);
                        $('#cabeceraVariable').val(variableId);
                        $('#cabeceraCabecera').val(cabeceraId);
                        $('#dataSubmit').text('Actualizar');

                        $('#dataModal').modal('show');
                });
                $('#dataForm').on('submit', function() {
                    $('#dataSubmit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
                    
                });
        });
</script>
@endsection
