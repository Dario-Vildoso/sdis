@extends('layouts.materio')

@section('title', 'Exportación')

@section('content')
<div class="card mb-4">
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

        <form class="reports" method="GET" action="{{ route('exportaciones.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Formulario</label>
                    <div class="input-group">
                        <span class="input-group-text bg-warning">
                            <i class="ri ri-file-list-3-line"></i>
                        </span>
                        <select name="formulario_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Seleccione...</option>
                            @foreach($formularios as $item)
                                <option value="{{ $item->id }}" {{ (int) request('formulario_id') === $item->id ? 'selected' : '' }}>
                                    {{ $item->sistema->nombre }} - {{ $item->gestion->nombre }}: {{ $item->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <small class="text-muted">Selecciona un formulario para cargar los periodos disponibles.</small>
                </div>
            </div>
        </form>

        <form class="reports" method="POST" action="{{ route('exportaciones.export') }}" id="exportForm">
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

                @if (Auth::user()->alcances?->tipo == 'sedes' || Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin'))
                    <div class="col-md-4">
                        <label class="form-label">Redes (selección múltiple)</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-black text-white">
                                <i class="ri ri-search-line"></i>
                            </span>
                            <input
                                type="text"
                                class="form-control filter-search"
                                id="redSearch"
                                data-target="#redSelect"
                                placeholder="Buscar redes..."
                            >
                        </div>
                        <select name="red_ids[]" id="redSelect" class="form-select scope-select" multiple size="8">
                            @foreach($redesDisponibles as $red)
                                <option value="{{ $red['id'] }}" {{ collect(old('red_ids', []))->contains($red['id']) ? 'selected' : '' }}>
                                    {{ $red['nombre'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (Auth::user()->alcances?->tipo == 'red' || Auth::user()->alcances?->tipo == 'sedes' || Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin'))
                    <div class="col-md-4">
                        <label class="form-label">Municipios (selección múltiple)</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-black text-white">
                                <i class="ri ri-search-line"></i>
                            </span>
                            <input
                                type="text"
                                class="form-control filter-search"
                                id="municipioSearch"
                                data-target="#municipioSelect"
                                placeholder="Buscar municipios..."
                            >
                        </div>
                        <select name="municipio_ids[]" id="municipioSelect" class="form-select scope-select" multiple size="8">
                            @foreach($municipiosDisponibles as $municipio)
                                <option value="{{ $municipio['id'] }}" {{ collect(old('municipio_ids', []))->contains($municipio['id']) ? 'selected' : '' }}>
                                    {{ $municipio['nombre'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-4">
                    <label class="form-label">Establecimientos (selección múltiple)</label>
                    <div class="input-group mb-2">
                        <span class="input-group-text bg-black text-white">
                            <i class="ri ri-search-line"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control filter-search"
                            id="establecimientoSearch"
                            data-target="#establecimientoSelect"
                            placeholder="Buscar establecimientos..."
                        >
                    </div>
                    <select name="establecimiento_ids[]" id="establecimientoSelect" class="form-select scope-select" multiple size="8">
                        @foreach($establecimientosDisponibles as $establecimiento)
                            <option value="{{ $establecimiento['id'] }}" {{ collect(old('establecimiento_ids', []))->contains($establecimiento['id']) ? 'selected' : '' }}>
                                {{ $establecimiento['nombre'] }} - {{ $establecimiento['nivel'] }}º Nivel
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn text-white" type="submit" style="background-color: #139b92;">
                    <i class="ri ri-file-excel-2-line"></i> Exportar a Excel
                </button>
                <a href="{{ route('exportaciones.index') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterInputs = Array.from(document.querySelectorAll('.filter-search'));

        filterInputs.forEach(input => {
            const target = document.querySelector(input.dataset.target);
            if (!target) {
                return;
            }

            input.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();

                Array.from(target.options).forEach(option => {
                    option.hidden = query !== '' && !option.text.toLowerCase().includes(query);
                });
            });
        });

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
