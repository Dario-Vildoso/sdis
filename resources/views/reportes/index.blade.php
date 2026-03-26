@extends('layouts.materio')

@section('title', 'Reportes consolidados')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <h4 class="card-title mb-3">Filtros de reporte</h4>

        <form method="GET" action="{{ route('reportes.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
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

            <div class="col-md-2">
                <label class="form-label">Periodo desde</label>
                <select name="periodo_desde_id" class="form-select">
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id }}" {{ (int) $periodoDesde === $periodo->id ? 'selected' : '' }}>
                            {{ $periodo->descripcion }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Periodo hasta</label>
                <select name="periodo_hasta_id" class="form-select">
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id }}" {{ (int) $periodoHasta === $periodo->id ? 'selected' : '' }}>
                            {{ $periodo->descripcion }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Establecimientos</label>
                <select name="establecimiento_ids[]" class="form-select" multiple size="4">
                    @foreach($establecimientosDisponibles as $establecimiento)
                        <option value="{{ $establecimiento->id }}" {{ $establecimientosSeleccionados->contains($establecimiento->id) ? 'selected' : '' }}>
                            {{ $establecimiento->descripcion }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Mantén Ctrl para selección múltiple.</small>
            </div>

            @if($scopeTipo === 'municipio')
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="mostrar_consolidado" value="1" id="mostrarConsolidado" {{ $mostrarConsolidado ? 'checked' : '' }}>
                        <label class="form-check-label" for="mostrarConsolidado">
                            Mostrar consolidado municipal
                        </label>
                    </div>
                </div>
            @endif

            <div class="col-12 d-flex gap-2">
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
            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="heading-{{ $index }}">
                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}">
                        {{ $row['variable']->descripcion }}
                    </button>
                </h2>

                <div id="collapse-{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#acordeonVariables">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Subvariable</th>
                                        <th>Establecimiento</th>
                                        @foreach($row['cabeceras'] as $cabecera)
                                            <th class="text-end">{{ $cabecera->descripcion }}</th>
                                        @endforeach
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($row['subvariables'] as $subData)
                                        @foreach($subData['establecimientos'] as $detalle)
                                            <tr>
                                                <td>{{ $subData['subvariable']->descripcion }}</td>
                                                <td>{{ $detalle['establecimiento']->descripcion }}</td>
                                                @foreach($row['cabeceras'] as $cabecera)
                                                    <td class="text-end">{{ number_format($detalle['totales'][$cabecera->id] ?? 0, 0, ',', '.') }}</td>
                                                @endforeach
                                                <td class="text-end fw-bold">{{ number_format($detalle['total_general'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach

                                        @if($subData['consolidado'])
                                            <tr class="table-primary">
                                                <td>{{ $subData['subvariable']->descripcion }}</td>
                                                <td><strong>Consolidado municipal</strong></td>
                                                @foreach($row['cabeceras'] as $cabecera)
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
@endsection
