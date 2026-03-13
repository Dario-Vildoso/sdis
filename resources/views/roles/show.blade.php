@extends('layouts.materio')

@section('title', 'Detalles del Rol')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $role->name }}</h5>
                        <div>
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-primary">
                                <i class="ri-edit-line"></i> Editar
                            </a>
                            <a href="{{ route('roles.index') }}" class="btn btn-sm btn-secondary">
                                <i class="ri-arrow-left-line"></i> Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Nombre:</strong>
                                <p>{{ $role->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Slug:</strong>
                                <p><code>{{ $role->slug }}</code></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <strong>Descripción:</strong>
                                <p>{{ $role->description ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Permisos ({{ $role->permissions->count() }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($role->permissions->chunk(2) as $chunk)
                                @foreach($chunk as $permission)
                                    <div class="col-md-6 mb-3">
                                        <div class="alert alert-light border mb-0" role="alert">
                                            <h6 class="alert-heading mb-2">{{ $permission->name }}</h6>
                                            <p class="text-muted small mb-0">{{ $permission->description }}</p>
                                            <small class="text-secondary"><code>{{ $permission->slug }}</code></small>
                                        </div>
                                    </div>
                                @endforeach
                            @empty
                                <div class="col-12">
                                    <p class="text-muted text-center">Este rol no tiene permisos asignados</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Usuarios con este Rol ({{ $role->users->count() }})</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($role->users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <form action="{{ route('roles.removeFromUser', $role) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Remover este rol del usuario?')">
                                                    <i class="ri-delete-bin-line"></i> Remover
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No hay usuarios con este rol
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
