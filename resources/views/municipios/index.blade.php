@extends('layouts.materio')

@section('title', 'Municipios')

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
                <h5 class="card-title">Crear un nuevo municipio</h5>
            </div>
            <div class="card-footer">
                <button id="btnNewMun" type="button" class="btn btn-card-action" data-bs-toggle="modal" data-bs-target="#munModal">
                    <i class="ri ri-add-line fs-5"></i>&nbsp;&nbsp;&nbsp;Nuevo Municipio
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card" style="padding: 20px;">
    <div class="table-responsive">
        <table id="munsTable" class="table table-sm table-bordered table-stripped small table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Nombre</th>
                <th>Red</th>
                <th>Codigo</th>
                @if (Auth::user()->hasPermissionTo('municipios.management'))
                <th class="actions">Acciones</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($municipios as $municipio)
                <tr>
                    <td class="id-cell">
                        <div class="text-center d-flex align-items-center justify-content-center">
                            <strong>{{ $municipio->id }}</strong>
                        </div>
                    </td>
                    <td>{{ $municipio->nombre }}</td>
                    <td>{{ $municipio->red->nombre ?? '-' }}</td>
                    <td>{{ $municipio->codigo ?? '-' }}</td>
                    @if (Auth::user()->hasPermissionTo('municipios.management'))
                    <td>
                        <button type="button"
                                class="btn-icon edit-mun"
                                data-id="{{ $municipio->id }}"
                                data-nombre="{{ $municipio->nombre }}"
                                data-red="{{ $municipio->red_id }}"
                                data-codigo="{{ $municipio->codigo }}">
                            <i class="ri ri-pencil-line fs-4 bg-success"></i>
                        </button>   
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
</script>
<script>
$(function () {
    var table = $("#munsTable").DataTable({
        "pageLength": 10,
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
        
        "pagingType": "full_numbers",
        "scrollX": true,
    });

    table.buttons().container().appendTo('#munsTable_wrapper .col-md-6:eq(0)');
});
</script>

<!-- Modal: Crear / Editar Municipio -->
<div class="modal fade" id="munModal" tabindex="-1" aria-labelledby="munModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="munForm" action="{{ route('municipios.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="munModalLabel">Nuevo municipio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        {{-- Nombre --}}
                        <div class="col-md-12 mb-3">
                            <label for="munNombre" class="form-label">Nombre</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success"><i class="ri ri-edit-line"></i></span>
                                <input type="text"
                                    class="form-control"
                                    id="munNombre"
                                    name="nombre"
                                    required>
                            </div>
                        </div>

                        {{-- Red --}}
                        <div class="col-md-12 mb-3">
                            <label for="munRed" class="form-label">Red</label>
                            <div class="input-group">
                                <span class="input-group-text bg-warning"><i class="ri ri-map-pin-2-line"></i></span>
                                <select class="form-select"
                                        id="munRed"
                                        name="red_id"
                                        required>
                                    <option value="" disabled selected>Seleccione una red</option>
                                    @foreach($redes as $red)
                                        <option value="{{ $red->id }}">
                                            {{ $red->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Código --}}
                        <div class="col-md-12 mb-3">
                            <label for="munCodigo" class="form-label">Código</label>
                            <div class="input-group">
                                <span class="input-group-text bg-info"><i class="ri ri-shield-check-line"></i></span>
                                <input type="text"
                                    class="form-control"
                                    id="munCodigo"
                                    name="codigo">
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="munSubmit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
        $(function () {
                // Base URL for update action
                var baseUrl = "{{ url('municipios') }}";

                // New municipality: reset form
                $("#btnNewMun").click(function () {
                    $("#munForm")[0].reset();
                    $("#munForm").attr("action", baseUrl);
                    $("#formMethod").val("");
                    $("#munForm").find("input[name='_method']").remove();
                    $("#munModalLabel").text("Nuevo municipio");
                    $("#munSubmit").text("Crear");
                });

                // Edit municipality: load form
                $(document).on("click", ".edit-mun", function () {
                    var id = $(this).data("id");
                    var nombre = $(this).data("nombre");
                    var red = $(this).data("red");
                    var codigo = $(this).data("codigo");

                    $("#munForm").attr("action", baseUrl + "/" + id);
                    $("#formMethod").val("PUT");

                    $("#munNombre").val(nombre);
                    $("#munRed").val(red);
                    $("#munCodigo").val(codigo);

                    $("#munModalLabel").text("Editar municipio");
                    $("#munSubmit").text("Actualizar");

                    $("#munModal").modal("show");
                });
        });
    </script>

@endsection
