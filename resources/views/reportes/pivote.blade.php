@extends('layouts.materio')

@section('title', 'Reporte dinámico')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.css">

<style>
    /* Selectors */
    .select2-selection__choice{
        border: none !important;
        border-radius: 100px !important;
    }
    
    span.select2-selection__choice__display{
        color: #139b92 !important;
        background-color: #d8f3f1;
    }

    button.select2-selection__choice__remove{
        background-color: #139b92 !important;
        border: none !important;
        color: #fff !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border: none !important;
        outline: none !important;
        box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.25);
    }

    span.select2-selection.select2-selection--multiple{
        padding: .5rem;
    }
    button.btn {
        border: none;
        border-radius: 40px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }

    button.btn:hover {
        background-color: #17a2b8;
        color: white;
        box-shadow: 0 0 15px #17a2b8;
    }
    /* Tabla */
    table.pvtUi select{
        border: none;
        box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.25);
        border-radius: 1rem;
        padding-left: .25rem;
    }

    .pvtRows {
        background-color: #f5f5f5 !important;
        border: none !important;
        border-radius: 0 0 0 1rem;
    }
    .pvtCols {
        background-color: #f5f5f5 !important;
        border: none !important;
        border-radius: 0 0 0 0;
    }
    .pvtUnused {
        background-color: #eaeaea !important;
        border: none !important;
        border-radius: 1rem 1rem 0 0 !important;
    }
    .pvtVals{
        background-color: #eaeaea !important;
        border: none !important;
        border-radius: 1rem 0 0 0;
    }
    .pvtRendererArea{
        padding: 0 !important;
    }
    .pvtAttr{
        border: none !important;
        background-color: #ffffff !important;
        color: #139b92 !important;
        border-radius: 1rem !important;
        box-shadow: 0 0 5px rgba(0, 61, 63, 0.25);
    }
    .pvtTriangle{
        color: #139b92;
    }
    .pvtFilterBox{
        border: none !important;
        border-radius: 1rem !important;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.25);
    }
    .pvtFilterBox button{
        border: none !important;
        border-radius: 1rem !important;
        background-color: #139b92 !important;
        color: #fff !important;´
        transition: all 0.3s ease;
    }
    .pvtFilterBox button:hover{
        background-color: #17a2b8 !important;
        box-shadow: 0 0 10px #17a2b8;
    }

    .pivot-report-card .select2-container { width: 100% !important; }
    .variable-pivot-output .pvtUi { width: 100%; }
    .variable-pivot-output .pvtTable { font-size: .82rem; }

    .variable-pivot-output .pvtTable thead th {
        background: #086375;
        color: #fff;
        text-align: center;
    }
    .variable-pivot-output .pvtTable thead th.pvtColLabel {
        background: #139b92;
        color: #fff;
        text-align: center;
    }

    .variable-pivot-output .pvtTable tbody th {
        background: #657881 !important;
        color: #fff;
        vertical-align: middle;
        text-align: center;
    }

    .variable-pivot-output .pvtTable tbody td.pvtVal {
        border-left: none;
        border-right: none;
        text-align: center;
    }
    .variable-pivot-output .pvtVal,
    .variable-pivot-output .pvtTotal,
    .variable-pivot-output .pvtGrandTotal {
        text-align: right;
    }
    .variable-pivot-output .pvtTotal,
    .variable-pivot-output .pvtGrandTotal {
        background: #eef8f7;
        font-weight: 700;
    }
</style>

<div class="card pivot-report-card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <p class="text-muted mb-0">Ámbito disponible: <strong>{{ $scopeNombre ?: 'Cobertura del usuario' }}</strong>.</p>
            </div>
        </div>

        <form id="pivotReportForm">
            @csrf
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label">Formularios</label>
                        <select id="formularioSelect" name="formulario_ids[]" class="form-select" multiple>
                            @foreach($formularios as $formulario)
                            <option value="{{ $formulario->id }}" {{ $autoFormularioIds->contains($formulario->id) ? 'selected' : '' }}>
                                {{ $formulario->sistema->nombre ?? 'Sin sistema' }} - {{ $formulario->gestion->nombre ?? 'Sin gestión' }} {{ ($formulario->descripcion ?? $formulario->nombre ?? '') ? '- '.($formulario->descripcion ?? $formulario->nombre) : '' }}
                            </option>
                            @endforeach
                        </select>
                </div>

                <div class="col-lg-3">
                    <label class="form-label">Periodo desde</label>
                    <select id="periodoDesdeSelect" name="periodo_desde_id" class="form-select" disabled></select>
                </div>

                <div class="col-lg-3">
                    <label class="form-label">Periodo hasta</label>
                    <select id="periodoHastaSelect" name="periodo_hasta_id" class="form-select" disabled></select>
                </div>

                <div class="col-12">
                    <label class="form-label">Variables</label>
                    <select id="variableSelect" name="variable_ids[]" class="form-select" multiple disabled></select>
                </div>

                @if($showRedSelect)
                    <div class="col-lg-4">
                        <label class="form-label">Redes</label>
                        <select id="redSelect" name="red_ids[]" class="form-select" multiple>
                            @foreach($redesDisponibles as $red)
                                <option value="{{ $red['id'] }}">{{ $red['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($showMunicipioSelect)
                    <div class="col-lg-4">
                        <label class="form-label">Municipios</label>
                        <select id="municipioSelect" name="municipio_ids[]" class="form-select" multiple>
                            @foreach($municipiosDisponibles as $municipio)
                                <option value="{{ $municipio['id'] }}" data-red-id="{{ $municipio['red_id'] }}">
                                    {{ $municipio['nombre'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($showEstablecimientoSelect)
                    <div class="col-lg-4">
                        <label class="form-label">Establecimientos</label>
                        <select id="establecimientoSelect" name="establecimiento_ids[]" class="form-select" multiple>
                            @foreach($establecimientosDisponibles as $establecimiento)
                                <option
                                    value="{{ $establecimiento['id'] }}"
                                    data-red-id="{{ $establecimiento['red_id'] }}"
                                    data-municipio-id="{{ $establecimiento['municipio_id'] }}"
                                >
                                    {{ $establecimiento['nombre'] }} - {{ $establecimiento['nivel'] }}º Nivel
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div class="mt-3" id="variablesResumen"></div>

            <div class="d-flex flex-wrap gap-2 mt-4">
                <button type="button" id="clearFilters" class="btn">
                    <i class="ri ri-eraser-line"></i> Limpiar filtros
                </button>
                <button type="submit" id="generateReport" class="btn text-white" style="background-color:#139b92;">
                    <i class="ri ri-table-line"></i> Generar reporte
                </button>
            </div>
        </form>
    </div>
</div>

<div id="reportStatus" class="alert d-none"></div>


<div class="card d-none" id="reportCard">
    <div class="card-body" style="padding: 0%;">
        <div class="d-flex justify-content-between align-items-center mb-3">
        </div>
        <div id="reportAccordion" class="accordion"></div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.es.min.js"></script>
<script>
    $(function () {
        const metadataUrl = @json(route('reportes.pivote.metadata'));
        const dataUrl = @json(route('reportes.pivote.datos'));
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        $("#formularioSelect, #variableSelect").select2({ width: 'auto', placeholder: 'Seleccionar...', allowClear: false });
        $('#redSelect, #municipioSelect, #establecimientoSelect').select2({ width: 'auto', placeholder: 'Todo', allowClear: false });
        $('#periodoDesdeSelect, #periodoHastaSelect').select2({ width: '100%', placeholder: 'Seleccione un periodo' });

        clearFilterSelects();

        function ensureSpanishPivotLocale() {
            const utilities = $.pivotUtilities;

            if (!utilities || utilities.locales?.es) {
                return;
            }

            const aggregators = {};
            const renderers = {};
            const addAggregator = (spanish, english) => {
                if (utilities.aggregators?.[english]) {
                    aggregators[spanish] = utilities.aggregators[english];
                }
            };
            const addRenderer = (spanish, english) => {
                if (utilities.renderers?.[english]) {
                    renderers[spanish] = utilities.renderers[english];
                }
            };

            addAggregator('Cuenta', 'Count');
            addAggregator('Cuenta valores únicos', 'Count Unique Values');
            addAggregator('Lista valores únicos', 'List Unique Values');
            addAggregator('Suma', 'Sum');
            addAggregator('Suma entera', 'Integer Sum');
            addAggregator('Promedio', 'Average');
            addAggregator('Mínimo', 'Minimum');
            addAggregator('Máximo', 'Maximum');
            addRenderer('Tabla', 'Table');
            addRenderer('Tabla con barras', 'Table Barchart');
            addRenderer('Mapa de calor', 'Heatmap');
            addRenderer('Mapa de calor por filas', 'Row Heatmap');
            addRenderer('Mapa de calor por columnas', 'Col Heatmap');

            utilities.locales = utilities.locales || {};
            utilities.locales.es = {
                localeStrings: {
                    renderError: 'Ocurrió un error renderizando la tabla dinámica.',
                    computeError: 'Ocurrió un error calculando la tabla dinámica.',
                    uiRenderError: 'Ocurrió un error renderizando la interfaz de tabla dinámica.',
                    selectAll: 'Seleccionar todo',
                    selectNone: 'Deseleccionar todo',
                    tooMany: '(demasiados valores para listar)',
                    filterResults: 'Filtrar valores',
                    totals: 'Totales',
                    vs: 'vs',
                    by: 'por',
                },
                aggregators,
                renderers,
            };
        }

        ensureSpanishPivotLocale();

        function showStatus(type, message) {
            $('#reportStatus')
                .removeClass('d-none alert-info alert-warning alert-danger alert-success')
                .addClass('alert-' + type)
                .text(message);
        }

        function hideStatus() {
            $('#reportStatus').addClass('d-none').text('');
        }

        function selectedValues(selector) {
            return ($(selector).val() || []).map(String);
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>'"]/g, function (char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    "'": '&#039;',
                    '"': '&quot;'
                }[char];
            });
        }

        function uniqueValues(values) {
            return [...new Set(values.filter(value => value !== undefined && value !== null && value !== ''))];
        }

        function updatePeriodoSelects(periodos) {
            const desde = $('#periodoDesdeSelect');
            const hasta = $('#periodoHastaSelect');
            desde.empty();
            hasta.empty();

            periodos.forEach(periodo => {
                const label = periodo.descripcion;
                const option = new Option(label, periodo.id, false, false);
                $(option).attr('data-formulario-id', periodo.formulario_id);
                desde.append(option);

                const optionHasta = new Option(label, periodo.id, false, false);
                $(optionHasta).attr('data-formulario-id', periodo.formulario_id);
                hasta.append(optionHasta);
            });

            desde.prop('disabled', periodos.length === 0);
            hasta.prop('disabled', periodos.length === 0);

            if (periodos.length > 0) {
                desde.val(periodos[0].id).trigger('change.select2');
                hasta.val(periodos[periodos.length - 1].id).trigger('change.select2');
            } else {
                desde.val(null).trigger('change.select2');
                hasta.val(null).trigger('change.select2');
            }
        }

        function updateVariableSelect(variables) {
            const wrapper = $('#variablesResumen').empty();
            const variableSelect = $('#variableSelect');
            variableSelect.empty();

            if (!variables.length) {
                variableSelect.prop('disabled', true).val(null).trigger('change.select2');
                wrapper.html('<div class="alert alert-warning mb-0">Los formularios seleccionados no tienen variables asignadas.</div>');
                return;
            }

            variables.forEach(variable => {
                const label = `${variable.descripcion}`;
                variableSelect.append(new Option(label, variable.id, false, false));
            });

            variableSelect.prop('disabled', false).val(null).trigger('change.select2');
            wrapper.html(`<div class="small text-muted">${variables.length} variables disponibles.</div>`);
        }

        function populateFilterOptions(select, items) {
            if (!select.length) {
                return;
            }

            select.empty();

            items.forEach(item => {
                const option = new Option(item.nombre, item.id, false, false);

                if (item.red_id !== undefined) {
                    $(option).attr('data-red-id', item.red_id);
                }

                if (item.municipio_id !== undefined) {
                    $(option).attr('data-municipio-id', item.municipio_id);
                }

                select.append(option);
            });

            select.prop('disabled', items.length === 0).trigger('change.select2');
        }

        function clearFilterSelects() {
            const selects = ['#redSelect', '#municipioSelect', '#establecimientoSelect'];
            selects.forEach(selector => {
                const select = $(selector);
                if (!select.length) {
                    return;
                }
                select.empty().val(null).prop('disabled', true).trigger('change.select2');
            });
        }

        function updateFilterSelects(metadata) {
            populateFilterOptions($('#redSelect'), metadata.redes || []);
            populateFilterOptions($('#municipioSelect'), metadata.municipios || []);
            populateFilterOptions($('#establecimientoSelect'), metadata.establecimientos || []);

            filterMunicipios();
            filterEstablecimientos();
        }

        function filterMunicipios() {
            if (!$('#municipioSelect').length) {
                filterEstablecimientos();
                return;
            }

            const redes = selectedValues('#redSelect');
            const municipiosValidos = [];

            $('#municipioSelect option').each(function () {
                const redId = String($(this).data('red-id'));
                const visible = redes.length === 0 || redes.includes(redId);
                $(this).prop('disabled', !visible);
                if (visible) municipiosValidos.push(String(this.value));
            });

            const seleccionActual = selectedValues('#municipioSelect').filter(id => municipiosValidos.includes(id));
            $('#municipioSelect').val(seleccionActual).trigger('change.select2');
            filterEstablecimientos();
        }

        function filterEstablecimientos() {
            if (!$('#establecimientoSelect').length) {
                return;
            }

            const redes = selectedValues('#redSelect');
            const municipios = selectedValues('#municipioSelect');
            const establecimientosValidos = [];

            $('#establecimientoSelect option').each(function () {
                const redId = String($(this).data('red-id'));
                const municipioId = String($(this).data('municipio-id'));
                const matchRed = redes.length === 0 || redes.includes(redId);
                const matchMunicipio = municipios.length === 0 || municipios.includes(municipioId);
                const visible = matchRed && matchMunicipio;
                $(this).prop('disabled', !visible);
                if (visible) establecimientosValidos.push(String(this.value));
            });

            const seleccionActual = selectedValues('#establecimientoSelect').filter(id => establecimientosValidos.includes(id));
            $('#establecimientoSelect').val(seleccionActual).trigger('change.select2');
        }

        function clearRenderedReport() {
            $('#reportCard').addClass('d-none');
            $('#reportAccordion').empty();
        }

        function getPivotColumns(table) {
            const maxLevels = Math.max(0, ...(table.cabeceras_leaf || []).map(cabecera => cabecera.niveles.length));
            return ['Periodo', ...Array.from({ length: maxLevels }, (_, index) => `Cabecera ${index + 1}`)];
        }

        function buildPivotRows(table) {
            const leafCabeceras = table.cabeceras_leaf || [];
            const periodos = table.periodos || [];
            const columns = getPivotColumns(table).filter(column => column !== 'Periodo');

            if (leafCabeceras.length === 0) {
                return (table.filas || []).flatMap(fila => periodos.map(periodo => ({
                    Subvariable: fila.subvariable.descripcion,
                    Red: fila.establecimiento.red_nombre,
                    Municipio: fila.establecimiento.municipio_nombre,
                    Establecimiento: fila.establecimiento.descripcion,
                    Periodo: periodo.label,
                    Valor: Number(fila.total || 0),
                })));
            }

            return (table.filas || []).flatMap(fila => periodos.flatMap(periodo => leafCabeceras.map(cabecera => {
                const row = {
                    Subvariable: fila.subvariable.descripcion,
                    Red: fila.establecimiento.red_nombre,
                    Municipio: fila.establecimiento.municipio_nombre,
                    Establecimiento: fila.establecimiento.descripcion,
                    Periodo: periodo.label,
                    Cabecera: cabecera.descripcion,
                    Valor: Number((fila.valores[periodo.id] || {})[cabecera.id] || 0),
                };

                columns.forEach((column, index) => {
                    row[column] = cabecera.niveles[index] || '';
                });

                return row;
            })));
        }

        function buildPivotSorters(table) {
            const sorters = {};
            const pivotUtilities = $.pivotUtilities || {};

            if (!pivotUtilities.sortAs) {
                return sorters;
            }

            sorters.Subvariable = pivotUtilities.sortAs(uniqueValues((table.filas || []).map(fila => fila.subvariable.descripcion)));
            sorters.Red = pivotUtilities.sortAs(uniqueValues((table.filas || []).map(fila => fila.establecimiento.red_nombre)));
            sorters.Municipio = pivotUtilities.sortAs(uniqueValues((table.filas || []).map(fila => fila.establecimiento.municipio_nombre)));
            sorters.Establecimiento = pivotUtilities.sortAs(uniqueValues((table.filas || []).map(fila => fila.establecimiento.descripcion)));
            sorters.Periodo = pivotUtilities.sortAs((table.periodos || []).map(periodo => periodo.label));

            getPivotColumns(table).filter(column => column !== 'Periodo').forEach((column, index) => {
                sorters[column] = pivotUtilities.sortAs(uniqueValues((table.cabeceras_leaf || []).map(cabecera => cabecera.niveles[index] || '')));
            });

            return sorters;
        }

        function renderVariableAccordion(table, index) {
            const collapseId = `variable-report-${index}`;
            const headingId = `variable-heading-${index}`;
            const openClass = index === 0 ? 'show' : '';
            const buttonClass = index === 0 ? '' : 'collapsed';
            const pivotId = `variable-pivot-${index}`;

            return `
                <div class="accordion-item mb-3">
                    <h2 class="accordion-header" id="${headingId}">
                        <button class="accordion-button ${buttonClass}" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="${index === 0 ? 'true' : 'false'}" aria-controls="${collapseId}">
                            ${escapeHtml(table.variable.descripcion)}
                        </button>
                    </h2>
                    <div id="${collapseId}" class="accordion-collapse collapse ${openClass}" aria-labelledby="${headingId}" data-bs-parent="#reportAccordion">
                        <div class="accordion-body">
                            <div class="d-flex gap-2 justify-content-end mb-2">
                                <button type="button" class="btn btn-sm bg-success text-white export-pivot-excel" data-pivot-id="${pivotId}" data-variable="${escapeHtml(table.variable.descripcion)}">
                                    <i class="ri ri-file-excel-2-line"></i> Exportar Excel
                                </button>
                                <button type="button" class="btn btn-sm bg-danger text-white export-pivot-pdf" data-pivot-id="${pivotId}" data-variable="${escapeHtml(table.variable.descripcion)}">
                                    <i class="ri ri-file-pdf-2-line"></i> Exportar PDF
                                </button>
                            </div>
                            <div id="${pivotId}" class="variable-pivot-output table-responsive"></div>
                        </div>
                    </div>
                </div>`;
        }

        function renderVariablePivot(table, index) {
            const rows = buildPivotRows(table);
            const cols = getPivotColumns(table);
            const target = $(`#variable-pivot-${index}`);

            if (rows.length === 0) {
                target.html('<div class="alert alert-warning mb-0">No hay filas para esta variable.</div>');
                return;
            }

            const pivotUtilities = $.pivotUtilities || {};
            const hasSpanishLocale = Boolean(pivotUtilities.locales?.es);
            const pivotOptions = {
                rows: ['Red', 'Municipio', 'Establecimiento', 'Subvariable'],
                cols: cols,
                vals: ['Valor'],
                aggregatorName: hasSpanishLocale ? 'Suma de enteros' : 'Integer Sum',
                rendererName: hasSpanishLocale ? 'Tabla' : 'Table',
                sorters: buildPivotSorters(table),
                unusedAttrsVertical: false,
            };

            if (hasSpanishLocale) {
                target.empty().pivotUI(rows, pivotOptions, true, 'es');
                return;
            }

            target.empty().pivotUI(rows, pivotOptions, true);
        }

        function renderReport(response) {
            const tables = response.tables || [];

            if (!tables.length) {
                clearRenderedReport();
                showStatus('warning', 'No hay pivots para mostrar con los filtros seleccionados.');
                return;
            }

            $('#reportAccordion').html(tables.map(renderVariableAccordion).join(''));
            $('#reportCard').removeClass('d-none');
            tables.forEach(renderVariablePivot);
            showStatus('success', 'Reporte generado correctamente.');
        }

        function safeFilename(value, extension) {
            const base = String(value || 'reporte').replace(/[^a-z0-9_-]+/gi, '_').replace(/^_+|_+$/g, '').toLowerCase();
            return `${base || 'reporte'}.${extension}`;
        }

        function exportPivotExcel(pivotId, variableName) {
            const tableHtml = $(`#${pivotId} table.pvtTable`).prop('outerHTML');

            if (!tableHtml) {
                showStatus('warning', 'No hay tabla para exportar.');
                return;
            }

            const html = `<!doctype html><html><head><meta charset="utf-8"></head><body>${tableHtml}</body></html>`;
            const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = safeFilename(variableName, 'xls');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }

        function exportPivotPdf(pivotId, variableName) {
            const tableHtml = $(`#${pivotId} table.pvtTable`).prop('outerHTML');

            if (!tableHtml) {
                showStatus('warning', 'No hay tabla para exportar.');
                return;
            }

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>${escapeHtml(variableName)}</title><style>body{font-family:Arial,sans-serif;font-size:11px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #555;padding:4px;text-align:right}th{text-align:center;background:#139b92;color:#fff}</style></head><body><h3>${escapeHtml(variableName)}</h3>${tableHtml}</body></html>`);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }

        $('#redSelect').on('change', filterMunicipios);
        $('#municipioSelect').on('change', filterEstablecimientos);
        $('#variableSelect').on('change', clearRenderedReport);
        $(document).on('click', '.export-pivot-excel', function () {
            exportPivotExcel($(this).data('pivot-id'), $(this).data('variable'));
        });
        $(document).on('click', '.export-pivot-pdf', function () {
            exportPivotPdf($(this).data('pivot-id'), $(this).data('variable'));
        });

        $('#formularioSelect').on('change', function () {
            const formularioIds = selectedValues('#formularioSelect');
            clearRenderedReport();
            clearFilterSelects();

            if (formularioIds.length === 0) {
                updatePeriodoSelects([]);
                updateVariableSelect([]);
                hideStatus();
                return;
            }

            showStatus('info', 'Cargando periodos, variables y establecimientos...');
            $.get(metadataUrl, { formulario_ids: formularioIds })
                .done(function (response) {
                    updatePeriodoSelects(response.periodos || []);
                    updateVariableSelect(response.variables || []);
                    updateFilterSelects(response);
                    showStatus('info', 'Selecciona una o más variables y presiona Generar reporte.');
                })
                .fail(function () {
                    showStatus('danger', 'No se pudieron cargar los datos del formulario seleccionado.');
                });
        });

        $('#clearFilters').on('click', function () {
            $('#pivotReportForm')[0].reset();
            $('#formularioSelect, #variableSelect').val(null).trigger('change');
            clearFilterSelects();
            updatePeriodoSelects([]);
            updateVariableSelect([]);
            clearRenderedReport();
            hideStatus();
        });

        $('#pivotReportForm').on('submit', function (event) {
            event.preventDefault();

            if (selectedValues('#formularioSelect').length === 0) {
                showStatus('warning', 'Selecciona uno o más formularios.');
                return;
            }

            if (!$('#periodoDesdeSelect').val() || !$('#periodoHastaSelect').val()) {
                showStatus('warning', 'Selecciona el periodo desde y hasta.');
                return;
            }

            if (selectedValues('#variableSelect').length === 0) {
                showStatus('warning', 'Selecciona una o más variables para generar el reporte.');
                return;
            }

            $('#generateReport').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Generando...');
            showStatus('info', 'Consultando datos y construyendo los PivotTables por variable...');

            $.ajax({
                url: dataUrl,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: $(this).serialize(),
            }).done(renderReport).fail(function (xhr) {
                const message = xhr.responseJSON?.message || 'No se pudo generar el reporte.';
                clearRenderedReport();
                showStatus('danger', message);
            }).always(function () {
                $('#generateReport').prop('disabled', false).html('<i class="ri ri-table-line"></i> Generar reporte');
            });
        });

        if (selectedValues('#formularioSelect').length > 0) {
            $('#formularioSelect').trigger('change');
        }

        filterMunicipios();
    });
</script>
@endsection
