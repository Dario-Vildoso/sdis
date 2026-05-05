@extends('layouts.materio')

@section('title', 'Roles')

@section('content')
<div class="row justify-content-end">
    @if (Auth::user()->hasPermissionTo('roles.management'))
    <div class="col-md-4 mb-4">
        <div class="card card-action">
            <div class="card-body">
                <h5 class="card-title">Crear un nuevo Rol</h5>
            </div>
            <div class="card-footer">
                <a href="{{ route('roles.create') }}" class="btn btn-card-action">
                    
                    <i class="ri ri-add-line fs-5"></i>&nbsp;&nbsp;&nbsp;Nuevo Rol
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

                    <div class="card" style="padding: 20px">
                        <div class="table-responsive text-nowrap">
                            <table id="dataTable" class="table table-sm table-bordered table-stripped small table-materio">
                                <thead>
                                    <tr>
                                        <th class="id-header"></th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Usuarios</th>
                                        <th class="actions">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @forelse($roles as $role)
                                        <tr>
                                            <td class="id-cell">
                                                <div class="text-center d-flex align-items-center justify-content-center">
                                                    <strong>{{ $role->id }}</strong>
                                                </div>
                                            </td>
                                            <td class="data">{{ $role->name }}</td>
                                            <td class="data">{{ $role->description ?? 'N/A' }}</td>
                                            <td class="data">
                                                <span class="badge bg-label-warning">
                                                    {{ $role->users->count() }} usuarios
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                        <button type="button" class="btn-icon">
                                                            <a href="{{ route('roles.show', $role) }}">
                                                                <i class="ri ri-eye-line bg-info"></i>
                                                            </a>
                                                        </button>
                                                        @if (Auth::user()->hasPermissionTo('roles.management'))
                                                        <button type="button" class="btn-icon">
                                                            <a href="{{ route('roles.edit', $role) }}">
                                                                <i class="ri ri-pencil-line bg-success"></i>
                                                            </a>
                                                        </button>
                                                        @if (Auth::user()->hasRole('super-admin'))
                                                        <form action="{{ route('roles.destroy', $role) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-icon" onclick="return confirm('¿Estás seguro?')">
                                                                <i class="ri ri-delete-bin-line bg-danger"></i> 
                                                            </button>
                                                        </form>
                                                        @endif
                                                        @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No hay roles disponibles
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                @if($roles->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $roles->links() }}
                    </div>
                @endif
<script>
$(function () {
    var table = $("#dataTable").DataTable({
        "pageLength": 10,                    // Cambié de 6 a 10 (más estándar)
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
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
        "stateSave": true,                  // Recuerda la página, filtros y orden al recargar
    });

    // Mover los botones a la izquierda (opcional)
    table.buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');
});
</script>
@endsection
