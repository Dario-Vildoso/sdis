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
        <table id="dataTable" class="table small table-sm table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Descripcion</th> 
                <th>Subvariables</th>
                <th>Cabeceras</th>
                <th class="actions">Acciones</th>
            </tr>
            </thead>
            <tbody class="table-border-bottom-0">
            @foreach($variables as $variable)
                <tr class="variable-row">
                    <td class="text-muted small id-cell">
                        <div class="text-center d-flex align-items-center justify-content-center">
                            <strong>{{ $variable->id }}</strong>
                        </div>
                    </td>
                    <td class="data">{{ $variable->descripcion }}</td>
                    <td class="data">
                        <span class="badge bg-label-info cursor-pointer" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#subvars-{{ $variable->id }}" 
                        aria-expanded="false" 
                        aria-controls="subvars-{{ $variable->id }}">
                            {{ $variable->subvariables->count() }} subvariables  <i class="ri ri-arrow-down-s-fill"></i>
                        </span>
                        <div class="collapse mt-2" id="subvars-{{ $variable->id }}">
                            <div class="p-2 bg-light rounded small">
                                @forelse($variable->subvariables as $sub)
                                    <div>• {{ $sub->descripcion }}</div>
                                @empty
                                    <div class="text-muted">Sin subvariables</div>
                                @endforelse
                            </div>
                        </div>
                    </td>
                    <td class="data">
                        <span class="badge bg-label-success cursor-pointer"
                        data-bs-toggle="collapse"
                        data-bs-target="#cabs-{{ $variable->id }}"
                        aria-expanded="false"
                        aria-controls="cabs-{{ $variable->id }}">
                            {{ $variable->cabeceras->count() }} cabeceras   <i class="ri ri-arrow-down-s-fill"></i>
                        </span>
                        <div class="collapse mt-2" id="cabs-{{ $variable->id }}">
                            <div class="p-2 bg-light rounded small">
                                @php
                                    // Construir árbol de cabeceras
                                    $cabecerasHijas = $variable->cabeceras->groupBy('cabecera_id');
                                    
                                    $renderTree = function($parentId = null, $level = 0) use (&$renderTree, $cabecerasHijas, $variable) {
                                        $cabeceras = $cabecerasHijas->get($parentId) ?? collect();
                                        $count = count($cabeceras);
                                        
                                        foreach ($cabeceras as $index => $cab) {
                                            $isLast = ($index === $count - 1);
                                            $prefix = '';
                                            
                                            // Crear prefijo tipo árbol
                                            if ($level > 0) {
                                                for ($i = 0; $i < $level - 1; $i++) {
                                                    $prefix .= '│&nbsp;&nbsp;&nbsp;';
                                                }
                                                $prefix .= $isLast ? '└─ ' : '├─ ';
                                            }
                                            
                                            echo '<div>' . $prefix . htmlspecialchars($cab->descripcion, ENT_QUOTES, 'UTF-8') . '</div>';
                                            
                                            // Renderizar recursivamente hijos
                                            $renderTree($cab->id, $level + 1);
                                        }
                                    };
                                    
                                    $renderTree();
                                @endphp
                                @if($variable->cabeceras->isEmpty())
                                    <div class="text-muted">Sin cabeceras</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('variables.tabla', $variable) }}" 
                           class="btn btn-icon" 
                           title="Ver tabla">
                            <i class="ri ri-table-2 fs-4 bg-info text-white rounded-2" style="padding: 6px;"></i>
                        </a>

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
