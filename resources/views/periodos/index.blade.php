@extends('layouts.materio')

@section('title', 'Usuarios')

@section('content')

<!-- @if(session('success'))
    <div id="successAlert" class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
    </div>

    <script>
        setTimeout(function() {
            var alert = new bootstrap.Alert(document.getElementById('successAlert'));
            alert.close();
        }, 3000);
    </script>
@endif -->
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: "{{ session('success') }}",
        timer: 1000,
        showConfirmButton: false
    });
</script>
@endif

<button id="btnNewData" type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#dataModal">
    <i class="ri ri-add-line fs-4"></i>
    Nuevo Periodo
</button>

    <div class="table-responsive">
        <table id="dataTable" class="table table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Formulario</th>
                <th>Descripcion</th>
                <th>Fecha de Inicio</th>
                <th>Fecha de Fin</th>
                <th>Estado</th>
                <th class="actions">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($periodos as $periodo)
                <tr>
                    <td class="id-cell">
                        <div class="text-center d-flex align-items-center justify-content-center">
                            <strong>{{ $periodo->id }}</strong>
                        </div>
                    </td>
                    <td class="data">{{ $formularios->where('id', $periodo->formulario_id)->first()->nombre ?? 'Sin formulario' }} - {{ $gestiones->where('id', $formularios->where('id', $periodo->formulario_id)->first()->gestion_id ?? 0)->first()->nombre ?? 'Sin gestión' }}</td>
                    <td class="data">{{ $periodo->descripcion }}</td>
                    <td class="data">{{ $periodo->fecha_inicio }}</td>
                    <td class="data">{{ $periodo->fecha_fin }}</td>
                    <td class="data">
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-estado" type="checkbox" role="switch" id="toggle-estado-{{ $periodo->id }}" data-id="{{ $periodo->id }}" {{ $periodo->estado ? 'checked' : '' }}>
                        </div>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-icon edit-data"
                                data-id="{{ $periodo->id }}"
                                data-formulario="{{ $periodo->formulario_id }}"
                                data-descripcion="{{ $periodo->descripcion }}"
                                data-fecha-inicio="{{ $periodo->fecha_inicio }}"
                                data-fecha-fin="{{ $periodo->fecha_fin }}"
                                data-estado="{{ $periodo->estado }}">
                            <i class="ri ri-pencil-line fs-4 bg-success"></i>
                        </button>

                        <form action="{{ route('periodos.destroy', $periodo) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-icon" type="submit"
                                onclick="return confirm('¿Eliminar periodo?')">
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
            var url = "{{ url('periodos') }}" + '/' + id + '/toggle';

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
                        title: (res.estado ? 'Periodo activado' : 'Periodo desactivado'),
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
                    <h5 class="modal-title" id="ModalLabel">Nuevo formulario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        {{-- Formulario --}}
                        <div class="col-md-12 mb-3">
                            <label for="periodoForm" class="form-label">Formulario</label>
                            <select class="form-select" name="form_id" id="periodoForm">
                                <option value="" disabled>Seleccione un formulario</option>
                                @foreach($formularios as $formulario)
                                    <option value="{{ $formulario->id }}">
                                        {{ $formulario->nombre }} - {{ $gestiones->where('id', $formulario->gestion_id)->first()->nombre ?? 'Sin gestión' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Descripción --}}
                        <div class="col-md-12 mb-3">
                            <label for="periodoDescripcion" class="form-label">Periodo</label>
                            <select class="form-select" name="descripcion" id="periodoDescriocion" required>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                        {{-- Fecha de Inicio --}}
                        <div class="col-md-6 mb-3">
                            <label for="periodoFechaInicio" class="form-label">Fecha de Inicio</label>
                            <input type="datetime-local"
                                class="form-control"
                                id="periodoFechaInicio"
                                name="fecha_inicio"
                                required>
                        </div>
                        {{-- Fecha de Fin --}}
                        <div class="col-md-6 mb-3">
                            <label for="periodoFechaFin" class="form-label">Fecha de Fin</label>
                            <input type="datetime-local"
                                class="form-control"
                                id="periodoFechaFin"
                                name="fecha_fin"
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
                var baseUrl = "{{ url('periodos') }}";

                // New establishment: reset form
                $('#btnNewData').on('click', function () {
                        $('#ModalLabel').text('Nuevo periodo');
                        $('#dataForm').attr('action', "{{ route('periodos.store') }}");
                        $('#formMethod').val(''); // Limpia el método para creación
                        $('#periodoForm').val('');
                        $('#periodoDescripcion').val('');
                        $('#periodoFechaInicio').val('{{ now()->format('Y-m-d\TH:i') }}');
                        $('#periodoFechaFin').val('{{ now()->format('Y-m-d\TH:i') }}');
                        $('#dataSubmit').text('Crear');
                });

                // Edit establishment: populate and set action
                $('.edit-data').on('click', function () {
                        var id = $(this).data('id');
                        var formularioId = $(this).data('formulario');
                        var descripcion = $(this).data('descripcion');
                        var fechaInicio = $(this).data('fecha-inicio');
                        var fechaFin = $(this).data('fecha-fin');
                        $('#ModalLabel').text('Editar formulario');
                        $('#dataForm').attr('action', baseUrl + '/' + id);
                        $('#formMethod').val('PUT'); // Cambia el método a PUT para actualización
                        $('#periodoForm').val(formularioId);
                        $('#periodoDescripcion').val(descripcion);
                        $('#periodoFechaInicio').val(fechaInicio);
                        $('#periodoFechaFin').val(fechaFin);
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
    