@extends('layouts.materio')

@section('title', 'Editar usuario')

@section('content')

<div class="card">
    <div class="card-body">

        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input name="name" value="{{ $user->name }}" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input name="email" value="{{ $user->email }}" class="form-control">
            </div>

            <button class="btn btn-primary">Actualizar</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>

    </div>
</div>

@endsection
