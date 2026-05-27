@extends('layouts.materio')

@section('title', 'Reportes consolidados con Tabulator')

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

        <form class="reports" method="GET" action="{{ route('reportes.tabulator') }}">

            <div class="row">

                <div class="col-md-6 border-end pe-4">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Formularios</label>
                            <div class="input-group">
                                <span class="input-group-text bg-warning">
                                    <i class="ri ri-file-list-3-line"></i>
                                </span>
                                <select id="formularioSelect" name="formulario_ids[]" class="form-select" multiple size="1">
                                    @foreach($formularios as $item)
                                        <option value="{{ $item->id }}" {{ $formularioIdsSeleccionados->contains($item->id) ? 'selected' : '' }}>
                                            {{ $item->sistema->nombre ?? 'Sin sistema' }} - {{ $item->gestion->nombre ?? 'Sin gestión' }}: {{ $item->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="text-muted">Selecciona uno o más formularios (Ctrl + clic).</small>
                        </div>
                        
                        @if($formulariosSeleccionados->isNotEmpty())
                            <div class="col-12">
                                <label class="form-label">Variables</label>
                                <select name="variable_ids[]" id="variableSelect" class="form-select" multiple size="6">
                                    @foreach($variablesDisponibles as $variable)
                                        <option value="{{ $variable->id }}" {{ $variableIdsSeleccionadas->contains($variable->id) ? 'selected' : '' }}>
                                            {{ $variable->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Selecciona una o más variables.</small>
                            </div>
                        @endif

                        @foreach($formulariosSeleccionados as $formSeleccionado)
                            @php
                                $periodosFormulario = $periodosPorFormulario->get($formSeleccionado->id, collect())->sortBy('numero');
                            @endphp
                            <div class="col-12">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold mb-2">
                                        {{ $formSeleccionado->sistema->nombre ?? 'Sin sistema' }} - {{ $formSeleccionado->gestion->nombre ?? 'Sin gestión' }}
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label mb-1">Desde</label>
                                            <select name="periodo_desde_ids[{{ $formSeleccionado->id }}]" class="form-select">
                                                @foreach($periodosFormulario as $periodo)
                                                    <option value="{{ $periodo->id }}" {{ (int) ($periodoDesdePorFormulario[$formSeleccionado->id] ?? 0) === $periodo->id ? 'selected' : '' }}>
                                                        {{ $periodo->descripcion }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label mb-1">Hasta</label>
                                            <select name="periodo_hasta_ids[{{ $formSeleccionado->id }}]" class="form-select">
                                                @foreach($periodosFormulario as $periodo)
                                                    <option value="{{ $periodo->id }}" {{ (int) ($periodoHastaPorFormulario[$formSeleccionado->id] ?? 0) === $periodo->id ? 'selected' : '' }}>
                                                        {{ $periodo->descripcion }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>

                <div class="col-md-6">
                    <div class="row g-3">

                        @if(in_array($scopeTipo, ['red', 'sedes']) || Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin'))
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

                        @if(in_array($scopeTipo, ['municipio', 'red', 'sedes']) || Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin'))
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
                            <div class="col-md-6 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-black">
                                        <i class="ri ri-search-line"></i>
                                    </span>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="establecimientoSearch"
                                        placeholder="Buscar..."
                                    >
                                </div>
                            </div>
                            <select name="establecimiento_ids[]" id="establecimientoSelect" class="form-select" multiple size="6">
                                @foreach($establecimientosDisponibles as $establecimiento)
                                    <option value="{{ $establecimiento->id }}" data-municipio-id="{{ $establecimiento->id_mun }}" {{ $establecimientosSeleccionados->contains($establecimiento->id) ? 'selected' : '' }}>
                                        {{ $establecimiento->descripcion }} - {{ $establecimiento->nivel }}Âº Nivel
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
                <button class="btn text-white" type="submit" style="background-color: #139b92;">
                    <i class="ri ri-filter-3-line"></i> Aplicar filtros
                </button>
                <a href="{{ route('reportes.tabulator') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>

        </form>
    </div>
</div>

@if($formulariosSeleccionados->isEmpty())
    <div class="alert alert-info">Selecciona uno o más formularios para generar el reporte.</div>
@elseif(!$hayVariablesDisponibles)
    <div class="alert alert-warning">No hay variables disponibles para los formularios seleccionados.</div>
@elseif(!$hayVariablesSeleccionadas)
    <div class="alert alert-info">Selecciona al menos una variable para generar el reporte.</div>
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

                $buildTabulatorColumns = function ($cabeceras) use (&$buildTabulatorColumns, $childrenByParent) {
                    return $cabeceras->map(function ($cabecera) use (&$buildTabulatorColumns, $childrenByParent) {
                        $children = $childrenByParent->get($cabecera->id, collect())->sortBy('id')->values();
                        $column = [
                            'title' => mb_strtoupper($cabecera->descripcion, 'UTF-8'),
                            'headerHozAlign' => 'center',
                        ];

                        if ($children->isEmpty()) {
                            $column['field'] = 'cabecera_' . $cabecera->id;
                            $column['hozAlign'] = 'right';
                            $column['sorter'] = 'number';
                            $column['formatter'] = 'localizedNumber';
                        } else {
                            $column['columns'] = $buildTabulatorColumns($children)->all();
                        }

                        return $column;
                    })->values();
                };

                $baseColumns = [[
                    'title' => 'Subvariable',
                    'field' => 'subvariable',
                    'headerFilter' => 'input',
                    'minWidth' => 220,
                ]];

                if (in_array($scopeTipo, ['red', 'sedes'])) {
                    $baseColumns[] = [
                        'title' => 'Red',
                        'field' => 'red',
                        'headerFilter' => 'input',
                        'minWidth' => 160,
                    ];
                }

                if (in_array($scopeTipo, ['municipio', 'red', 'sedes'])) {
                    $baseColumns[] = [
                        'title' => 'Municipio',
                        'field' => 'municipio',
                        'headerFilter' => 'input',
                        'minWidth' => 160,
                    ];
                }

                $baseColumns[] = [
                    'title' => 'Establecimiento',
                    'field' => 'establecimiento',
                    'headerFilter' => 'input',
                    'minWidth' => 220,
                ];

                $reportColumns = array_merge(
                    $baseColumns,
                    $buildTabulatorColumns($rootCabeceras)->all(),
                    [[
                        'title' => 'Total',
                        'field' => 'total_general',
                        'hozAlign' => 'right',
                        'sorter' => 'number',
                        'formatter' => 'localizedNumber',
                    ]]
                );

                $leafCabeceras = $cabeceras->filter(function ($cabecera) use ($childrenByParent) {
                    return $childrenByParent->get($cabecera->id, collect())->isEmpty();
                })->sortBy('id')->values();

                $reportData = [];

                foreach ($row['subvariables'] as $subData) {
                    $subvariableDescripcion = $subData['subvariable']->descripcion;
                    $subvariableSearch = mb_strtolower($subvariableDescripcion);

                    foreach ($subData['establecimientos'] as $detalle) {
                        $dataRow = [
                            'row_type' => 'establecimiento',
                            'subvariable' => $subvariableDescripcion,
                            'subvariable_search' => $subvariableSearch,
                            'establecimiento' => $detalle['establecimiento']->descripcion,
                            'total_general' => (float) $detalle['total_general'],
                        ];

                        if (in_array($scopeTipo, ['red', 'sedes'])) {
                            $dataRow['red'] = $detalle['establecimiento']->red_nombre;
                        }

                        if (in_array($scopeTipo, ['municipio', 'red', 'sedes'])) {
                            $dataRow['municipio'] = $detalle['establecimiento']->municipio_nombre;
                        }

                        foreach ($leafCabeceras as $cabecera) {
                            $dataRow['cabecera_' . $cabecera->id] = (float) ($detalle['totales'][$cabecera->id] ?? 0);
                        }

                        $reportData[] = $dataRow;
                    }

                    foreach ($subData['consolidado_municipio'] as $consolidadoMunicipio) {
                        $dataRow = [
                            'row_type' => 'consolidado_municipio',
                            'subvariable' => $subvariableDescripcion,
                            'subvariable_search' => $subvariableSearch,
                            'municipio' => $consolidadoMunicipio['municipio_nombre'],
                            'establecimiento' => 'Consolidado municipal',
                            'total_general' => (float) $consolidadoMunicipio['total_general'],
                        ];

                        if (in_array($scopeTipo, ['red', 'sedes'])) {
                            $dataRow['red'] = $consolidadoMunicipio['red_nombre'];
                        }

                        foreach ($leafCabeceras as $cabecera) {
                            $dataRow['cabecera_' . $cabecera->id] = (float) ($consolidadoMunicipio['totales'][$cabecera->id] ?? 0);
                        }

                        $reportData[] = $dataRow;
                    }

                    if (in_array($scopeTipo, ['red', 'sedes'])) {
                        foreach ($subData['consolidado_red'] as $consolidadoRed) {
                            $dataRow = [
                                'row_type' => 'consolidado_red',
                                'subvariable' => $subvariableDescripcion,
                                'subvariable_search' => $subvariableSearch,
                                'red' => $consolidadoRed['red_nombre'],
                                'municipio' => '',
                                'establecimiento' => 'Consolidado de Red',
                                'total_general' => (float) $consolidadoRed['total_general'],
                            ];

                            foreach ($leafCabeceras as $cabecera) {
                                $dataRow['cabecera_' . $cabecera->id] = (float) ($consolidadoRed['totales'][$cabecera->id] ?? 0);
                            }

                            $reportData[] = $dataRow;
                        }
                    }
                }
            @endphp

            <div class="accordion-item mb-3" data-variable-container="{{ $row['variable']->id }}">
                <h2 class="accordion-header" id="heading-{{ $index }}">
                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}">
                        {{ $row['variable']->descripcion }}
                    </button>
                </h2>

                <div id="collapse-{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#acordeonVariables">
                    <div class="accordion-body">
                        <div class="row mb-3 g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">Buscar subvariable</label>
                                <input
                                    type="text"
                                    class="form-control subvariable-search"
                                    placeholder="Buscar..."
                                    data-variable-id="{{ $row['variable']->id }}"
                                >
                            </div>
                            <div class="col-md-6 text-md-end">
                                @if($formulariosSeleccionados->count() === 1)
                                    @php
                                        $formIdExport = (int) $formulariosSeleccionados->first()->id;
                                        $exportParams = [
                                            'variable' => $row['variable']->id,
                                            'formulario_id' => $formIdExport,
                                            'periodo_desde_id' => $periodoDesdePorFormulario[$formIdExport] ?? null,
                                            'periodo_hasta_id' => $periodoHastaPorFormulario[$formIdExport] ?? null,
                                            'red_ids' => $redesSeleccionadas->all(),
                                            'municipio_ids' => $municipiosSeleccionados->all(),
                                            'establecimiento_ids' => $establecimientosSeleccionados->all(),
                                            'mostrar_consolidado' => $mostrarConsolidado ? 1 : 0,
                                        ];
                                    @endphp
                                    <a href="{{ route('reportes.export', $exportParams) }}" class="btn btn-success btn-sm">
                                        <i class="ri ri-file-excel-2-line"></i> Exportar Excel
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div
                            id="tabulator-variable-{{ $row['variable']->id }}"
                            class="report-tabulator"
                            data-variable-id="{{ $row['variable']->id }}"
                        ></div>
                        <script type="application/json" id="tabulator-columns-{{ $row['variable']->id }}">
                            {!! json_encode($reportColumns, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
                        </script>
                        <script type="application/json" id="tabulator-rows-{{ $row['variable']->id }}">
                            {!! json_encode($reportData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
                        </script>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/tabulator-tables@6.4.0/dist/css/tabulator_bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tabulator-tables@6.4.0/dist/js/tabulator.min.js"></script>
@endpush

<style>
    .report-tabulator .tabulator-header .tabulator-col {
        background: hsl(213, 10%, 75%);
        color: #1f2937;
        font-weight: 700;
    }

    .report-tabulator .tabulator-row.report-row-consolidado-municipio {
        background: #cfe2ff;
        font-weight: 700;
    }

    .report-tabulator .tabulator-row.report-row-consolidado-red {
        background: #d1e7dd;
        font-weight: 700;
    }
</style>

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

        const tabulatorTables = {};
        const numberFormatter = function (cell) {
            const value = Number(cell.getValue() || 0);

            return value.toLocaleString('es-BO', {
                maximumFractionDigits: 0,
                minimumFractionDigits: 0,
            });
        };

        Tabulator.extendModule('format', 'formatters', {
            localizedNumber: numberFormatter,
        });

        $('.report-tabulator').each(function () {
            const element = this;
            const variableId = String($(element).data('variable-id'));
            const columns = JSON.parse($(`#tabulator-columns-${variableId}`).text() || '[]');
            const rows = JSON.parse($(`#tabulator-rows-${variableId}`).text() || '[]');

            tabulatorTables[variableId] = new Tabulator(element, {
                data: rows,
                columns,
                layout: 'fitDataStretch',
                height: '600px',
                movableColumns: true,
                pagination: true,
                paginationSize: 25,
                paginationSizeSelector: [10, 25, 50, 100, true],
                placeholder: 'No hay datos para mostrar',
                lang: {
                    pagination: {
                        first: 'Primera',
                        first_title: 'Primera página',
                        last: 'Última',
                        last_title: 'Última página',
                        prev: 'Anterior',
                        prev_title: 'Página anterior',
                        next: 'Siguiente',
                        next_title: 'Página siguiente',
                    },
                    headerFilters: {
                        default: 'filtrar columna...',
                    },
                },
                rowFormatter(row) {
                    const type = row.getData().row_type;

                    if (type === 'consolidado_municipio') {
                        row.getElement().classList.add('report-row-consolidado-municipio');
                    }

                    if (type === 'consolidado_red') {
                        row.getElement().classList.add('report-row-consolidado-red');
                    }
                },
            });
        });

        $('.subvariable-search').on('input', function () {
            const term = $(this).val().toLowerCase().trim();
            const variableId = String($(this).data('variable-id'));
            const table = tabulatorTables[variableId];

            if (! table) {
                return;
            }

            if (term === '') {
                table.clearFilter();
                return;
            }

            table.setFilter('subvariable_search', 'like', term);
        });

        $('#formularioSelect').on('change', function () {
            this.form.submit();
        });
    });
</script>
@endsection
