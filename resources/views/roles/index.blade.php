@extends('layouts.materio')

@section('title', 'Gestión de Roles')

@section('content')

                        <a href="{{ route('roles.create') }}" class="btn btn-sm btn-primary">
                            <i class="ri-add-line"></i> Nuevo Rol
                        </a>
                    
                    <div class="table-responsive text-nowrap">
                        <table id="dataTable" class="table small table-sm table-materio">
                            <thead>
                                <tr>
                                    <th class="id-header"></th>
                                    <th>Nombre</th>
                                    <th>Slug</th>
                                    <th>Descripción</th>
                                    <th>Permisos</th>
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
                                        <td class="data"><span class="badge bg-label-info">{{ $role->slug }}</span></td>
                                        <td class="data">{{ $role->description ?? 'N/A' }}</td>
                                        <td class="data">
                                            <span class="badge bg-label-secondary">
                                                {{ $role->permissions->count() }} permisos
                                            </span>
                                        </td>
                                        <td class="data">
                                            <span class="badge bg-label-warning">
                                                {{ $role->users->count() }} usuarios
                                            </span>
                                        </td>
                                        <td class=>
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" data-bs-toggle="dropdown">
                                                    <i class="ri-more-2-line bg-secondary"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('roles.show', $role) }}">
                                                        <i class="ri-eye-line bg-info"></i> Ver
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('roles.edit', $role) }}">
                                                        <i class="ri-edit-line bg-warning"></i> Editar
                                                    </a>
                                                    @if($role->slug !== 'admin')
                                                        <form action="{{ route('roles.destroy', $role) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item" onclick="return confirm('¿Estás seguro?')">
                                                                <i class="ri-delete-bin-line"></i> Eliminar
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
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

                @if($roles->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $roles->links() }}
                    </div>
                @endif
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
@endsection
