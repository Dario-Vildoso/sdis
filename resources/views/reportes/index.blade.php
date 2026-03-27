@extends('layouts.materio')

@section('title', 'Reportes consolidados')

@section('content')
@php
    $countLeaves = function ($cabecera, $childrenByParent) use (&$countLeaves) {
        $children = $childrenByParent->get($cabecera->id, collect());

        if ($children->isEmpty()) {
            return 1;
        }

        return $children->sum(fn ($child) => $countLeaves($child, $childrenByParent));
    };

    $maxDepth = function ($cabeceras, $childrenByParent) use (&$maxDepth) {
        if ($cabeceras->isEmpty()) {
            return 0;
        }

        return 1 + $cabeceras->max(function ($cabecera) use (&$maxDepth, $childrenByParent) {
            return $maxDepth($childrenByParent->get($cabecera->id, collect()), $childrenByParent);
        });
    };

    $buildRows = function ($cabeceras, $childrenByParent, $nivel = 0, &$rows = []) use (&$buildRows) {
        foreach ($cabeceras as $cabecera) {
            $rows[$nivel][] = $cabecera;
            $children = $childrenByParent->get($cabecera->id, collect());
            if ($children->isNotEmpty()) {
                $buildRows($children, $childrenByParent, $nivel + 1, $rows);
            }
        }

        return $rows;
    };
@endphp

<div class="card mb-4">
    <div class="card-body">
        <h4 class="card-title mb-3">Filtros de reporte</h4>

        <form method="GET" action="{{ route('reportes.index') }}">
            
            <div class="row">
                
                <!-- 🔹 COLUMNA IZQUIERDA -->
                <div class="col-md-6 d-flex flex-column justify-content-start pe-4 border-end">
                    
                    <div class="mb-3">
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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periodo desde</label>
                            <select name="periodo_desde_id" class="form-select">
                                @foreach($periodos as $periodo)
                                    <option value="{{ $periodo->id }}" {{ (int) $periodoDesde === $periodo->id ? 'selected' : '' }}>
                                        {{ $periodo->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periodo hasta</label>
                            <select name="periodo_hasta_id" class="form-select">
                                @foreach($periodos as $periodo)
                                    <option value="{{ $periodo->id }}" {{ (int) $periodoHasta === $periodo->id ? 'selected' : '' }}>
                                        {{ $periodo->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if($scopeTipo === 'municipio')
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="mostrar_consolidado" value="1" id="mostrarConsolidado" {{ $mostrarConsolidado ? 'checked' : '' }}>
                            <label class="form-check-label" for="mostrarConsolidado">
                                Mostrar consolidado municipal
                            </label>
                        </div>
                    @endif

                </div>

                <!-- 🔹 COLUMNA DERECHA -->
                <div class="col-md-6 ps-4">
                    
                    <div class="mb-2">
                        <label class="form-label">Buscar establecimiento</label>
                        <input
                            type="text"
                            class="form-control"
                            id="establecimientoSearch"
                            placeholder="Escribe para filtrar establecimientos..."
                        >
                    </div>

                    <div>
                        <label class="form-label">Establecimientos</label>
                        <select name="establecimiento_ids[]" id="establecimientoSelect" class="form-select" multiple size="8">
                            @foreach($establecimientosDisponibles as $establecimiento)
                                <option value="{{ $establecimiento->id }}" {{ $establecimientosSeleccionados->contains($establecimiento->id) ? 'selected' : '' }}>
                                    {{ $establecimiento->descripcion }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Mantén Ctrl/Cmd para selección múltiple.</small>
                    </div>

                </div>

            </div>

            <!-- 🔹 BOTONES -->
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit">
                    <i class="ri ri-filter-3-line"></i> Aplicar filtros
                </button>
                <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>

        </form>
    </div>
</div>

@if(!$formulario)
    <div class="alert alert-info">Selecciona un formulario para generar el reporte.</div>
@elseif(empty($rows))
    <div class="alert alert-warning">No hay datos para los filtros seleccionados.</div>
@else
    <div class="alert alert-primary">
        Mostrando datos para <strong>{{ $scopeNombre ?: 'ámbito del usuario' }}</strong>.
    </div>

    <div class="accordion" id="acordeonVariables">
        @foreach($rows as $index => $row)
            @php
                $cabeceras = $row['variable']->cabeceras;
                $childrenByParent = $cabeceras->groupBy('cabecera_id');
                $rootCabeceras = $childrenByParent->get(null, collect())->sortBy('id')->values();
                $niveles = $maxDepth($rootCabeceras, $childrenByParent);
                $headerRows = $buildRows($rootCabeceras, $childrenByParent);
                $leafCabeceras = collect(end($headerRows) ?: []);
            @endphp

            <div class="accordion-item mb-3" data-variable-container="{{ $row['variable']->id }}">
                <h2 class="accordion-header" id="heading-{{ $index }}">
                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}">
                        {{ $row['variable']->descripcion }}
                    </button>
                </h2>

                <div id="collapse-{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#acordeonVariables">
                    <div class="accordion-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Buscar subvariable</label>
                                <input
                                    type="text"
                                    class="form-control subvariable-search"
                                    placeholder="Filtrar subvariables en esta variable..."
                                    data-variable-id="{{ $row['variable']->id }}"
                                >
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th rowspan="{{ max($niveles, 1) }}">Subvariable</th>
                                        <th rowspan="{{ max($niveles, 1) }}">Establecimiento</th>
                                        @foreach($headerRows[0] ?? [] as $cabecera)
                                            @php
                                                $children = $childrenByParent->get($cabecera->id, collect());
                                            @endphp

                                            @if($children->isEmpty())
                                                <th rowspan="{{ $niveles }}" class="text-center">{{ $cabecera->descripcion }}</th>
                                            @else
                                                <th colspan="{{ $countLeaves($cabecera, $childrenByParent) }}" class="text-center">{{ $cabecera->descripcion }}</th>
                                            @endif
                                        @endforeach
                                        <th rowspan="{{ max($niveles, 1) }}" class="text-end">Total</th>
                                    </tr>

                                    @for($i = 1; $i < $niveles; $i++)
                                        <tr>
                                            @foreach($headerRows[$i] ?? [] as $cabecera)
                                                @php
                                                    $children = $childrenByParent->get($cabecera->id, collect());
                                                @endphp

                                                @if($children->isEmpty())
                                                    <th rowspan="{{ $niveles - $i }}" class="text-center">{{ $cabecera->descripcion }}</th>
                                                @else
                                                    <th colspan="{{ $countLeaves($cabecera, $childrenByParent) }}" class="text-center">{{ $cabecera->descripcion }}</th>
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endfor
                                </thead>
                                <tbody>
                                    @foreach($row['subvariables'] as $subData)
                                        @php
                                            $subName = mb_strtolower($subData['subvariable']->descripcion);
                                        @endphp

                                        @foreach($subData['establecimientos'] as $detalle)
                                            <tr class="subvariable-row" data-variable-id="{{ $row['variable']->id }}" data-subvariable-label="{{ $subName }}">
                                                <td>{{ $subData['subvariable']->descripcion }}</td>
                                                <td>{{ $detalle['establecimiento']->descripcion }}</td>
                                                @foreach($leafCabeceras as $cabecera)
                                                    <td class="text-end">{{ number_format($detalle['totales'][$cabecera->id] ?? 0, 0, ',', '.') }}</td>
                                                @endforeach
                                                <td class="text-end fw-bold">{{ number_format($detalle['total_general'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach

                                        @if($subData['consolidado'])
                                            <tr class="table-primary subvariable-row" data-variable-id="{{ $row['variable']->id }}" data-subvariable-label="{{ $subName }}">
                                                <td>{{ $subData['subvariable']->descripcion }}</td>
                                                <td><strong>Consolidado municipal</strong></td>
                                                @foreach($leafCabeceras as $cabecera)
                                                    <td class="text-end"><strong>{{ number_format($subData['consolidado']['totales'][$cabecera->id] ?? 0, 0, ',', '.') }}</strong></td>
                                                @endforeach
                                                <td class="text-end"><strong>{{ number_format($subData['consolidado']['total_general'], 0, ',', '.') }}</strong></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<script>
    $(function () {
        $('#establecimientoSearch').on('input', function () {
            const term = $(this).val().toLowerCase().trim();

            $('#establecimientoSelect option').each(function () {
                const text = $(this).text().toLowerCase();
                const match = term === '' || text.includes(term);

                $(this).prop('disabled', !match);
                this.hidden = !match;
            });
        });

        $('.subvariable-search').on('input', function () {
            const term = $(this).val().toLowerCase().trim();
            const variableId = $(this).data('variable-id');

            $(`tr.subvariable-row[data-variable-id="${variableId}"]`).each(function () {
                const label = $(this).data('subvariable-label') || '';
                const match = term === '' || label.includes(term);
                $(this).toggle(match);
            });
        });
    });
</script>
@endsection
