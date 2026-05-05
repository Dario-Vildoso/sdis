@extends('layouts.materio')

@section('title', 'Redes')

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
                <h5 class="card-title">Crear una nueva red</h5>
            </div>
            <div class="card-footer">
                <button id="btnNewRed" type="button" class="btn btn-card-action" data-bs-toggle="modal" data-bs-target="#redModal">
                    <i class="ri ri-add-line fs-5"></i>&nbsp;&nbsp;&nbsp;Nueva Red
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card" style="padding: 20px;">
    <div class="table-responsive">
        <table id="redsTable" class="table table-sm table-bordered table-stripped small table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Nombre</th>
                <th>Codigo</th>
                @if (Auth::user()->hasPermissionTo('redes.management'))
                <th class="actions">Acciones</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($redes as $red)
                <tr>
                    <td class="id-cell">
                        <div class="text-center d-flex align-items-center justify-content-center">
                            <strong>{{ $red->id }}</strong>
                        </div>
                    </td>
                    <td>{{ $red->nombre }}</td>
                    <td>{{ $red->codigo ?? '-' }}</td>
                    @if (Auth::user()->hasPermissionTo('redes.management'))
                    <td>
                        <button type="button"
                                class="btn-icon edit-red"
                                data-id="{{ $red->id }}"
                                data-nombre="{{ $red->nombre }}"
                                data-codigo="{{ $red->codigo }}">
                            <i class="ri ri-pencil-line fs-4 bg-success"></i>
                        </button>

                        <form action="{{ route('redes.destroy', $red) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn-icon" type="submit"
                                onclick="return confirm('¿Eliminar red?')">
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
</script>
<script>
$(function () {
    var table = $("#redsTable").DataTable({
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

    table.buttons().container().appendTo('#redsTable_wrapper .col-md-6:eq(0)');
});
</script>

<!-- Modal: Crear / Editar Red -->
<div class="modal fade" id="redModal" tabindex="-1" aria-labelledby="redModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="redForm" action="{{ route('redes.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="redModalLabel">Nueva red</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">

                        {{-- Nombre --}}
                        <div class="col-md-12 mb-3">
                            <label for="redNombre" class="form-label">Nombre</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success"><i class="ri ri-edit-line"></i></span>
                                <input type="text"
                                    class="form-control"
                                    id="redNombre"
                                    name="nombre"
                                    required>
                            </div>
                        </div>

                        {{-- Código --}}
                        <div class="col-md-12 mb-3">
                            <label for="redCodigo" class="form-label">Código</label>
                            <div class="input-group">
                                <span class="input-group-text bg-info"><i class="ri ri-shield-check-line"></i></span>
                                <input type="text"
                                    class="form-control"
                                    id="redCodigo"
                                    name="codigo">
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="redSubmit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
        $(function () {
                // Base URL for update action
                var baseUrl = "{{ url('redes') }}";

                // New red: reset form
                $("#btnNewRed").click(function () {
                    $("#redForm")[0].reset();
                    $("#redForm").attr("action", baseUrl);
                    $("#formMethod").val("");
                    $("#redForm").find("input[name='_method']").remove();
                    $("#redModalLabel").text("Nueva red");
                    $("#redSubmit").text("Crear");
                });

                // Edit red: load form
                $(document).on("click", ".edit-red", function () {
                    var id = $(this).data("id");
                    var nombre = $(this).data("nombre");
                    var codigo = $(this).data("codigo");

                    $("#redForm").attr("action", baseUrl + "/" + id);
                    $("#formMethod").val("PUT");
                    $("#redForm").find("input[name='_method']").remove();
                    $('<input type="hidden" name="_method" value="PUT">').insertAfter("#formMethod");

                    $("#redNombre").val(nombre);
                    $("#redCodigo").val(codigo);

                    $("#redModalLabel").text("Editar red");
                    $("#redSubmit").text("Actualizar");

                    $("#redModal").modal("show");
                });
        });
    </script>

@endsection
