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
    Nueva Sub-Variable
</button>

    <div class="table-responsive">
        <table id="dataTable" class="table table-sm small table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Descripcion</th> 
                <th>Variable</th>
                <th class="actions">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @php
                // Agrupar subvariables por variable_id
                $subvariablesGrouped = $subvariables->groupBy('variable_id');
                
                // Array de colores para badges
                $badgeColors = ['bg-label-primary', 'bg-label-success', 'bg-label-warning', 'bg-label-danger', 'bg-label-info', 'bg-label-secondary'];
                $colorIndex = 0;
                $variableColors = [];
            @endphp
            
            @foreach($subvariablesGrouped as $variableId => $subs)
                @php
                    // Asignar color a esta variable si no lo tiene
                    if (!isset($variableColors[$variableId])) {
                        $variableColors[$variableId] = $badgeColors[$colorIndex % count($badgeColors)];
                        $colorIndex++;
                    }
                    $color = $variableColors[$variableId];
                    $variableDesc = $subs->first()->variable_descripcion;
                @endphp
                
                <!-- Filas de subvariables -->
                @foreach($subs as $subvariable)
                    <tr class="variable-row" data-variable-id="{{ $subvariable->id }}">
                        <td class="text-muted small id-cell">
                            <div class="text-center d-flex align-items-center justify-content-center">
                                <strong>{{ $subvariable->id }}</strong>
                            </div>
                        </td>
                        <td class="ps-5 data">{{ $subvariable->descripcion }}</td>
                        <td class="data">
                            <span class="badge {{ $color }}">{{ $variableDesc }}</span>
                        </td>
                        <td>
                            <button type="button"
                                    class="btn btn-icon edit-data"
                                    data-id="{{ $subvariable->id }}"
                                    data-descripcion="{{ $subvariable->descripcion }}"
                                    data-variable-id="{{ $subvariable->variable_id }}"
                                    onclick="event.stopPropagation();">
                                <i class="ri ri-pencil-line fs-4 bg-success"></i>
                            </button>

                            <form action="{{ route('subvariables.destroy', $subvariable) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-icon" type="submit"
                                    onclick="return confirm('¿Eliminar sub-variable?')">
                                    <i class="ri ri-delete-bin-line fs-4 bg-danger"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
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
            <form id="dataForm" action="{{ route('subvariables.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="dataModalLabel">Nueva Sub-variable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        {{-- Descripción --}}
                        <div class="col-md-12 mb-3">
                            <label for="varDescripcion" class="form-label">Descripción</label>
                            <input type="text"
                                class="form-control"
                                id="subvarDescripcion"
                                name="descripcion"
                                required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="subvarVariable" class="form-label">Variable</label>
                            <select name="variable_id" id="subvarVariable" class="form-select" required>
                                <option value="" disabled>Seleccione una variable</option>
                                @foreach($variables as $variable)
                                    <option value="{{ $variable->id }}">{{ $variable->descripcion }}</option>
                                @endforeach
                            </select>

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
                var baseUrl = "{{ url('subvariables') }}";

                // New establishment: reset form
                $('#btnNewData').on('click', function () {
                        $('#dataModalLabel').text('Nueva Sub-variable');
                        $('#dataForm').attr('action', "{{ route('subvariables.store') }}");
                        $('#formMethod').val(''); // Limpia el método para creación
                        $('#subvarDescripcion').val('');
                        $('#subvarVariable').val('');
                        $('#dataSubmit').text('Crear');
                });

                // Edit establishment: populate and set action
                $('.edit-data').on('click', function () {
                        var id = $(this).data('id');
                        var descripcion = $(this).data('descripcion');
                        var variableId = $(this).data('variable-id');

                        $('#dataModalLabel').text('Editar Sub-variable');
                        $('#dataForm').attr('action', baseUrl + '/' + id);
                        $('#formMethod').val('PUT'); // Cambia el método a PUT para actualización
                        $('#subvarDescripcion').val(descripcion);
                        $('#subvarVariable').val(variableId);
                        $('#dataSubmit').text('Actualizar');

                        $('#dataModal').modal('show');
                });
                $('#dataForm').on('submit', function() {
                    $('#dataSubmit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
                    
                });
        });
</script>
@endsection
