@extends('layouts.materio')

@section('title', 'Exportación')

@section('content')
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-3">Exportación de datos consolidados</h4>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('exportaciones.index') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Formulario</label>
                    <select name="formulario_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Seleccione...</option>
                        @foreach($formularios as $item)
                            <option value="{{ $item->id }}" {{ (int) request('formulario_id') === $item->id ? 'selected' : '' }}>
                                {{ $item->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('exportaciones.export') }}" id="exportForm">
            @csrf
            <input type="hidden" name="formulario_id" value="{{ (int) request('formulario_id') }}">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Periodo desde</label>
                    <select name="periodo_desde_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        @foreach($periodos as $periodo)
                            <option value="{{ $periodo->id }}" {{ (int) old('periodo_desde_id') === $periodo->id ? 'selected' : '' }}>
                                {{ $periodo->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Periodo hasta</label>
                    <select name="periodo_hasta_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        @foreach($periodos as $periodo)
                            <option value="{{ $periodo->id }}" {{ (int) old('periodo_hasta_id') === $periodo->id ? 'selected' : '' }}>
                                {{ $periodo->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Redes (selección múltiple)</label>
                    <select name="red_ids[]" id="redSelect" class="form-select scope-select" multiple size="8">
                        @foreach($redesDisponibles as $red)
                            <option value="{{ $red['id'] }}" {{ collect(old('red_ids', []))->contains($red['id']) ? 'selected' : '' }}>
                                {{ $red['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Municipios (selección múltiple)</label>
                    <select name="municipio_ids[]" id="municipioSelect" class="form-select scope-select" multiple size="8">
                        @foreach($municipiosDisponibles as $municipio)
                            <option value="{{ $municipio['id'] }}" {{ collect(old('municipio_ids', []))->contains($municipio['id']) ? 'selected' : '' }}>
                                {{ $municipio['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Establecimientos (selección múltiple)</label>
                    <select name="establecimiento_ids[]" id="establecimientoSelect" class="form-select scope-select" multiple size="8">
                        @foreach($establecimientosDisponibles as $establecimiento)
                            <option value="{{ $establecimiento['id'] }}" {{ collect(old('establecimiento_ids', []))->contains($establecimiento['id']) ? 'selected' : '' }}>
                                {{ $establecimiento['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary" type="submit">
                    <i class="ri ri-file-excel-2-line"></i> Exportar a Excel
                </button>
                <a href="{{ route('exportaciones.index') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selects = Array.from(document.querySelectorAll('.scope-select'));

        const toggleBySelection = () => {
            const selected = selects.filter(select => Array.from(select.selectedOptions).length > 0);

            if (selected.length === 0) {
                selects.forEach(select => select.disabled = false);
                return;
            }

            const selectedId = selected[0].id;
            selects.forEach(select => {
                select.disabled = select.id !== selectedId;
            });
        };

        selects.forEach(select => {
            select.addEventListener('change', toggleBySelection);
        });

        toggleBySelection();
    });
</script>
@endsection
