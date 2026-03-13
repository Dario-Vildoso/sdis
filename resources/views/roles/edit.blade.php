@extends('layouts.materio')

@section('title', 'Editar Rol')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Editar Rol: {{ $role->name }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('roles.update', $role) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre del Rol</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $role->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" value="{{ old('slug', $role->slug) }}" required
                                       {{ $role->slug === 'admin' ? 'disabled' : '' }}>
                                <small class="form-text text-muted">
                                    Solo letras minúsculas, números, guiones y guiones bajos
                                </small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3">{{ old('description', $role->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Permisos</label>
                                <div class="card border-light">
                                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                        @foreach($permissions as $permission)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="permission_{{ $permission->id }}" 
                                                       name="permissions[]" value="{{ $permission->id }}"
                                                       {{ in_array($permission->id, $rolePermissions) || old('permissions') && in_array($permission->id, old('permissions')) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                    <strong>{{ $permission->name }}</strong>
                                                    <small class="text-muted d-block">{{ $permission->description }}</small>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('permissions')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line"></i> Actualizar Rol
                                </button>
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                    <i class="ri-close-line"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
