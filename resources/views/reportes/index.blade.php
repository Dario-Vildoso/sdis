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

                <div class="col-md-6 border-end pe-4">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Formulario</label>
                            <select name="formulario_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Seleccione...</option>
                                @foreach($formularios as $item)
                                    <option value="{{ $item->id }}" {{ (int) request('formulario_id') === $item->id ? 'selected' : '' }}>
                                        {{ $item->sistema->nombre ?? 'Sin sistema' }} - {{ $item->gestion->nombre ?? 'Sin gestión' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label">Periodo desde</label>
                            <select name="periodo_desde_id" class="form-select">
                                @foreach($periodos as $periodo)
                                    <option value="{{ $periodo->id }}" {{ (int) $periodoDesde === $periodo->id ? 'selected' : '' }}>
                                        {{ $periodo->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6">
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
                </div>

                <div class="col-md-6">
                    <div class="row g-3">

                        @if(in_array($scopeTipo, ['red', 'sedes']))
                            <div class="col-md-6">
                                <label class="form-label">Red(es)</label>
                                <select name="red_ids[]" id="redSelect" class="form-select" multiple size="2">
                                    @foreach($redesDisponibles as $red)
                                        <option value="{{ $red['id'] }}" {{ $redesSeleccionadas->contains($red['id']) ? 'selected' : '' }}>
                                            {{ $red['nombre'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if(in_array($scopeTipo, ['municipio', 'red', 'sedes']))
                            <div class="col-md-6">
                                <label class="form-label">Municipio(s)</label>
                                <select name="municipio_ids[]" id="municipioSelect" class="form-select" multiple size="2">
                                    @foreach($municipiosDisponibles as $municipio)
                                        <option value="{{ $municipio['id'] }}" data-red-id="{{ $municipio['red_id'] }}" {{ $municipiosSeleccionados->contains($municipio['id']) ? 'selected' : '' }}>
                                            {{ $municipio['nombre'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-12">
                            
                            
                            <label class="form-label">Establecimientos</label>
                            <div class="col-md-6">
                                <input
                                    type="text"
                                    class="form-control mb-2"
                                    id="establecimientoSearch"
                                    placeholder="Buscar..."
                                >
                            </div>
                            <select name="establecimiento_ids[]" id="establecimientoSelect" class="form-select" multiple size="6">
                                @foreach($establecimientosDisponibles as $establecimiento)
                                    <option value="{{ $establecimiento->id }}" {{ $establecimientosSeleccionados->contains($establecimiento->id) || $establecimientosDisponibles->count() == 1 ? 'selected' : '' }}>
                                        {{ $establecimiento->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if(in_array($scopeTipo, ['municipio', 'red', 'sedes']))
                            <div class="col-12">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="mostrar_consolidado" value="1" id="mostrarConsolidado" {{ $mostrarConsolidado ? 'checked' : '' }}>
                                    <label class="form-check-label" for="mostrarConsolidado">
                                        Mostrar Consolidado
                                    </label>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

            </div>

            {{-- 🔹 BOTONES --}}
            <div class="d-flex gap-2 mt-4">
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

                <div id="collapse-{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#acordeonVariables">
                    <div class="accordion-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Buscar subvariable</label>
                                <input
                                    type="text"
                                    class="form-control subvariable-search"
                                    placeholder="Buscar..."
                                    data-variable-id="{{ $row['variable']->id }}"
                                >
                            </div>
                        </div>

                        @php
                            $exportParams = array_merge(['variable' => $row['variable']->id], request()->query());
                        @endphp
                        <div class="d-flex justify-content-end mb-3">
                            <a href="{{ route('reportes.export', $exportParams) }}" class="btn btn-success btn-sm">
                                <i class="ri ri-file-excel-2-line"></i> Exportar Excel
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th rowspan="{{ max($niveles, 1) }}">Subvariable</th>
                                        @if(in_array($scopeTipo, ['red', 'sedes']))
                                        <th rowspan="{{ max($niveles, 1) }}">Red</th>
                                        @endif
                                        @if(in_array($scopeTipo, ['municipio', 'red', 'sedes']))
                                        <th rowspan="{{ max($niveles, 1) }}">Municipio</th>
                                        @endif
                                        <th rowspan="{{ max($niveles, 1) }}">Establecimiento</th>
                                        @foreach($headerRows[0] ?? [] as $cabecera)
                                            @php
                                                $children = $childrenByParent->get($cabecera->id, collect());
                                            @endphp

                                            @if($children->isEmpty())
                                                <th rowspan="{{ $niveles }}" class="text-center" style="background: hsl(213, 10%, 75%)">{{ $cabecera->descripcion }}</th>
                                            @else
                                                <th colspan="{{ $countLeaves($cabecera, $childrenByParent) }}" class="text-center" style="background: hsl(213, 10%, 75%)">{{ $cabecera->descripcion }}</th>
                                            @endif
                                        @endforeach
                                        <th rowspan="{{ max($niveles, 1) }}" class="text-end">Total</th>
                                    </tr>

                                    @for($i = 1; $i < $niveles; $i++)
                                        <tr>
                                            @foreach($headerRows[$i] ?? [] as $index => $cabecera)
                                                @php
                                                    $children = $childrenByParent->get($cabecera->id, collect());
                                                    $hue = 213 - ($i * 3);
                                                    $saturation = 10;
                                                    $lightness = 75 - ($i * 5);
                                                @endphp

                                                @if($children->isEmpty())
                                                    <th rowspan="{{ $niveles - $i }}" 
                                                    class="text-center"
                                                    style="background: hsl({{ $hue }}, {{ $saturation }}%, {{ $lightness }}%)"
                                                    >{{ $cabecera->descripcion }}</th>
                                                @else
                                                    <th colspan="{{ $countLeaves($cabecera, $childrenByParent) }}" 
                                                    class="text-center"
                                                    style="background: hsl({{ $hue }}, {{ $saturation }}%, {{ $lightness }}%)"
                                                    >{{ $cabecera->descripcion }}</th>
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
                                                @if(in_array($scopeTipo, ['red', 'sedes']))
                                                <td>{{ $detalle['establecimiento']->red_nombre }}</td>
                                                @endif
                                                @if(in_array($scopeTipo, ['municipio', 'red', 'sedes']))
                                                <td>{{ $detalle['establecimiento']->municipio_nombre }}</td>
                                                @endif
                                                <td>{{ $detalle['establecimiento']->descripcion }}</td>
                                                @foreach($leafCabeceras as $cabecera)
                                                    <td class="text-end">{{ number_format($detalle['totales'][$cabecera->id] ?? 0, 0, ',', '.') }}</td>
                                                @endforeach
                                                <td class="text-end fw-bold">{{ number_format($detalle['total_general'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach

                                        @foreach($subData['consolidado_municipio'] as $consolidadoMunicipio)
                                            <tr class="table-primary subvariable-row" data-variable-id="{{ $row['variable']->id }}" data-subvariable-label="{{ $subName }}">
                                                <td>{{ $subData['subvariable']->descripcion }}</td>
                                                @if(in_array($scopeTipo, ['red', 'sedes']))
                                                <td><strong>{{ $consolidadoMunicipio['red_nombre'] }}</strong></td>
                                                @endif
                                                <td><strong>{{ $consolidadoMunicipio['municipio_nombre'] }}</strong></td>
                                                <td><strong>Consolidado municipal</strong></td>
                                                @foreach($leafCabeceras as $cabecera)
                                                    <td class="text-end"><strong>{{ number_format($consolidadoMunicipio['totales'][$cabecera->id] ?? 0, 0, ',', '.') }}</strong></td>
                                                @endforeach
                                                <td class="text-end"><strong>{{ number_format($consolidadoMunicipio['total_general'], 0, ',', '.') }}</strong></td>
                                            </tr>
                                        @endforeach
                                        @if (in_array($scopeTipo, ['red', 'sedes']))
                                        
                                        @foreach($subData['consolidado_red'] as $consolidadoRed)
                                        <tr class="table-success subvariable-row" data-variable-id="{{ $row['variable']->id }}" data-subvariable-label="{{ $subName }}">
                                            <td>{{ $subData['subvariable']->descripcion }}</td>
                                            <td><strong>{{ $consolidadoRed['red_nombre'] }}</strong></td>
                                            <td colspan="2"><strong>Consolidado de Red</strong></td>
                                            @foreach($leafCabeceras as $cabecera)
                                            <td class="text-end"><strong>{{ number_format($consolidadoRed['totales'][$cabecera->id] ?? 0, 0, ',', '.') }}</strong></td>
                                            @endforeach
                                            <td class="text-end"><strong>{{ number_format($consolidadoRed['total_general'], 0, ',', '.') }}</strong></td>
                                        </tr>
                                        @endforeach
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
        $('#redSelect').on('change', function () {
            const selectedRedes = $(this).val() || [];

            $('#municipioSelect option').each(function () {
                const redId = String($(this).data('red-id'));
                const match = selectedRedes.length === 0 || selectedRedes.includes(redId);

                $(this).prop('disabled', !match);
                this.hidden = !match;
            });
        });

        $('#establecimientoSearch').on('input', function () {
            const term = $(this).val().toLowerCase().trim();

            $('#establecimientoSelect option').each(function () {
                const text = $(this).text().toLowerCase();
                const match = term === '' || text.includes(term);

                $(this).prop('disabled', !match);
                this.hidden = !match;
            });
        });

        $('#redSelect').trigger('change');

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
