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
@if (Auth::user()->hasPermissionTo('periodos.management'))
    <div class="col-md-4 mb-4">
        <div class="card card-action">
            <div class="card-body">
                <h5 class="card-title">Crear un nuevo periodo</h5>
            </div>
            <div class="card-footer">
                <button id="btnNewData" type="button" class="btn btn-card-action" data-bs-toggle="modal" data-bs-target="#dataModal">
                    <i class="ri ri-add-line fs-5"></i>&nbsp;&nbsp;&nbsp;Nuevo Periodo
                </button>
            </div>
        </div>
    </div>
@endif
<div class="card" style="padding: 20px">
    <div class="table-responsive">
        <table id="dataTable" class="table table-sm table-bordered table-stripped small table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Formulario</th>
                <th>Descripcion</th>
                <th>Fecha de Inicio</th>
                <th>Fecha de Fin</th>
                @if (Auth::user()->hasPermissionTo('periodos.management'))
                <th>Activar</th>
                <th class="actions">Acciones</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($periodos as $periodo)
                <tr>
                    <td class="id-cell">
                        <div class="text-muted small id-cell">
                            <strong>{{ $periodo->id }}</strong>
                        </div>
                    </td>
                    <td class="data">{{ $periodo->formulario->sistema->nombre ?? 'Sin sistema' }} - {{ $periodo->formulario->gestion->nombre ?? 'Sin gestión' }}: {{ $periodo->formulario->descripcion ?? 'Sin descripción' }}</td>
                    <td class="data">{{ $periodo->descripcion }}</td>
                    <td class="data">{{ $periodo->fecha_inicio }}</td>
                    <td class="data">{{ $periodo->fecha_fin }}</td>
                    @if (Auth::user()->hasPermissionTo('periodos.management'))
                    <td class="data">
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-estado" type="checkbox" role="switch" id="toggle-estado-{{ $periodo->id }}" data-id="{{ $periodo->id }}" {{ $periodo->estado ? 'checked' : '' }}>
                        </div>
                    </td>
                    <td>
                        <button type="button"
                                class="btn-icon edit-data"
                                data-id="{{ $periodo->id }}"
                                data-formulario="{{ $periodo->formulario_id }}"
                                data-descripcion="{{ $periodo->descripcion }}"
                                data-numero="{{ $periodo->numero }}"
                                data-fecha-inicio="{{ $periodo->fecha_inicio }}"
                                data-fecha-fin="{{ $periodo->fecha_fin }}"
                                data-estado="{{ $periodo->estado }}">
                            <i class="ri ri-pencil-line fs-4 bg-success"></i>
                        </button>

                        <form action="{{ route('periodos.destroy', $periodo) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn-icon" type="submit"
                                onclick="return confirm('¿Eliminar periodo?')">
                                <i class="ri ri-delete-bin-line fs-4 bg-danger"></i>
                            </button>
                        </form>
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
    var table = $("#dataTable").DataTable({
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
                    <h5 class="modal-title" id="ModalLabel">Nuevo formulario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        {{-- Formulario --}}
                        <div class="col-md-12 mb-3">
                            <label for="periodoForm" class="form-label">Formulario</label>
                            <div class="input-group">
                                <span class="input-group-text bg-warning"><i class="ri ri-survey-line"></i></span>
                                <select class="form-select" required name="form_id" id="periodoForm">
                                    <option value="" disabled>Seleccione un formulario</option>
                                    @foreach($formularios as $formulario)
                                        <option value="{{ $formulario->id }}">
                                            {{ $formulario->sistema->nombre }} - {{ $formulario->gestion->nombre }}: {{ $formulario->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- Descripción --}}
                        <div class="col-md-4 mb-3">
                            <label for="periodoDescripcion" class="form-label">Tipo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success"><i class="ri ri-time-line"></i></span>
                                <select class="form-select" name="tipo" id="periodoTipo" onchange="setPeriodoSelector()">
                                    <option value="1">Mensual</option>
                                    <option value="2">Semanal</option>
                                    <option value="3">Trimestral</option>
                                    <option value="4">Semestral</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="periodoDescripcion" class="form-label">Periodo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-info"><i class="ri ri-calendar-line"></i></span>
                                <select class="form-select" name="descripcion" id="periodoDescripcion" required>
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
    let meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
    let semanas = []
    for(let i = 1; i <= 53; i++) {
        semana = 'Semana ' + i
        semanas.push(semana)
    }
    let trimestres = ['Trimestre 1', 'Trimestre 2', 'Trimestre 3', 'Trimestre 4']
    let semestres = ['Semestre 1', 'Semestre 2']
    function setPeriodoSelector() {
        var tipo = $('#periodoTipo').val()
        switch(tipo){
            case '1':
                $('#periodoDescripcion').html(meses.map(mes => `<option value="${meses.indexOf(mes) + 1}">${mes}</option>`).join(''))
                break
            case '2':
                $('#periodoDescripcion').html(semanas.map(semana => `<option value="${semanas.indexOf(semana) + 1}">${semana}</option>`).join(''))
                break
            case '3':
                $('#periodoDescripcion').html(trimestres.map(trimestre => `<option value="${trimestres.indexOf(trimestre) + 1}">${trimestre}</option>`).join(''))
                break
            case '4':
                $('#periodoDescripcion').html(semestres.map(semestre => `<option value="${semestres.indexOf(semestre) + 1}">${semestre}</option>`).join(''))
                break
        }
    }
</script>

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
                        $('#periodoTipo').val('1');
                        setPeriodoSelector()
                        $('#periodoDescripcion').val('');
                        $('#periodoFechaInicio').val('{{ now()->format('Y-m-d\TH:i') }}');
                        $('#periodoFechaFin').val('{{ now()->format('Y-m-d\TH:i') }}');
                        $('#dataSubmit').text('Crear');
                });

                // Edit establishment: populate and set action
                // Use event delegation for dynamically loaded rows from DataTable pagination
                $(document).on('click', '.edit-data', function () {
                        var id = $(this).data('id');
                        var formularioId = $(this).data('formulario');
                        var descripcion = $(this).data('descripcion');
                        var numero = $(this).data('numero');
                        if (meses.includes(descripcion)) {
                            $('#periodoTipo').val('1');
                        } else if (semanas.includes(descripcion)) {
                            $('#periodoTipo').val('2');
                        } else if (trimestres.includes(descripcion)) {
                            $('#periodoTipo').val('3');
                        } else if (semestres.includes(descripcion)) {
                            $('#periodoTipo').val('4');
                        }
                        setPeriodoSelector()
                        var fechaInicio = $(this).data('fecha-inicio');
                        var fechaFin = $(this).data('fecha-fin');
                        $('#ModalLabel').text('Editar formulario');
                        $('#dataForm').attr('action', baseUrl + '/' + id);
                        $('#formMethod').val('PUT'); // Cambia el método a PUT para actualización
                        $('#periodoForm').val(formularioId);
                        $('#periodoDescripcion').val(numero);
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
    