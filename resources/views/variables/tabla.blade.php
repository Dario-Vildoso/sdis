@extends('layouts.materio')

@section('title', 'Tabla de Variable - ' . $variable->descripcion)

@section('content')

@if(session('success'))
    <div id="successAlert" class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
    </div>

    <script>
        setTimeout(function() {
            var alert = new bootstrap.Alert(document.getElementById('successAlert'));
            alert.close();
        }, 3000);
    </script>
@endif

<div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
        @if (request()->input('form'))
        <a href="{{ route('formularios.asignarVariables', request()->input('form')) }}" class="btn btn-secondary btn-sm" title="Volver">
            <i class="ri ri-arrow-left-line"></i> <span>&nbsp;Volver</span>
        </a>
        @else
        <a href="{{ route('variables.index') }}" class="btn btn-secondary btn-sm" title="Volver">
            <i class="ri ri-arrow-left-line"></i> <span>&nbsp;Volver</span>
        </a>
        @endif
    </div>
    <div>
        <button type="button" class="btn btn-success btn-sm d-flex" id="btnGuardarCambios" title="Guardar todos los cambios">
            <i class="ri ri-save-fill"></i> <span>&nbsp;Guardar</span>
        </button>
    </div>
</div>

@php

// Devuelve cuántas hojas tiene debajo
function countLeaves($cabecera) {
    if ($cabecera->hijos->isEmpty()) {
        return 1;
    }

    return $cabecera->hijos->sum(fn($h) => countLeaves($h));
}

// Devuelve profundidad máxima
function maxDepth($cabeceras) {
    if ($cabeceras->isEmpty()) return 0;

    return 1 + $cabeceras->max(function ($c) {
        return maxDepth($c->hijos);
    });
}

@endphp

@php
    $niveles = maxDepth($variable->cabeceras);
    $rows = [];
@endphp

@php

$buildRows = function ($cabeceras, $nivel = 0, &$rows = []) use (&$buildRows) {

    foreach ($cabeceras as $cabecera) {

        $rows[$nivel][] = $cabecera;

        if ($cabecera->hijos->isNotEmpty()) {
            $buildRows($cabecera->hijos, $nivel + 1, $rows);
        }
    }

    return $rows;
};

$rows = $buildRows($variable->cabeceras);
@endphp

<form id="formTabla" method="POST" action="{{ route('variables.guardarTabla', $variable) }}">
    @csrf

    <table class="table table-sm small table-materio tabla-editable" id="tablaVariable">

        <thead>

            {{-- Primera fila: Variable + primer nivel --}}
            <tr>
                <th class="transparent-cell" style="display: table-cell; width: 40px;" rowspan="{{ $niveles }}" ></th>
                <th rowspan="{{ $niveles }}"
                    class="text-center align-middle text-white bg-info variable-header"
                    style="width: 50%">
                    <span id="nombreVariable" contenteditable="true" title="Editar nombre de la variable">{{ $variable->descripcion }}</span>
                </th>

                @foreach($rows[0] ?? [] as $cabecera)
                    <th colspan="{{ countLeaves($cabecera) }}"
                        @if($cabecera->hijos->isEmpty())
                            style="width: 10px"
                        @endif
                        class="text-center bg-secondary text-white cabecera-header"
                        data-cabecera-id="{{ $cabecera->id }}"
                        data-cabecera-padre="{{ $cabecera->cabecera_id }}"
                        data-level="0"
                        >
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            <span class="cabecera-nombre" contenteditable="true" title="Editar nombre">{{ $cabecera->descripcion }}</span>
                            <button type="button" class="btn btn-sm btn-icon btn-eliminar-cabecera" data-cabecera-id="{{ $cabecera->id }}" title="Eliminar cabecera">
                                <i class="ri fs-4 ri-delete-bin-fill" style="font-size: 0.8rem;"></i>
                            </button>
                        </div>
                    </th>
                @endforeach
                <th class="text-muted bg-secondary text-white button-cell-right transparent-cell" 
                    title="Agregar cabecera" 
                    data-level="0">
                    <div id="btnAdd0" style="background: linear-gradient(90deg, #018328, #05c63f); z-index: 2; position: absolute;"
                        onmouseover="this.style.background='linear-gradient(90deg, #018328, #1eef5d )'; this.style.width='50px'; this.style.transition='all 0.2s ease'" 
                        onmouseout="this.style.background='linear-gradient(90deg, #018328, #05c63f)'; this.style.width='40px'; this.style.transition='all 0.2s ease'"
                        data-bs-toggle="popover"
                        data-bs-html="true"
                        data-bs-placement="top"
                        data-bs-trigger="click"
                        data-bs-content='
                        <div class="d-flex align-items-center gap-2" style="min-width:200px;">
                            <input 
                                type="text" 
                                class="form-control form-control-sm input-nueva-cabecera" 
                                data-level="0"
                                placeholder="Nombre de cabecera"
                            >
                            <button type="button" class="btn btn-sm btn-success btnAgregarSubCabecera" data-level="0">
                                +
                            </button>
                        </div>
                        '
                        >
                            <i class="fs-5 ri ri-add-fill"></i>
                    </div>
                </th>
            </tr>

            {{-- Niveles siguientes --}}
            @for($i = 1; $i < $niveles; $i++)
                <tr>
                    <!-- <th class="transparent-cell"></th> -->
                    @foreach($rows[$i] ?? [] as $cabecera)

                        @if($cabecera->hijos->isEmpty())
                            <th rowspan="{{ $niveles - $i }}"
                                class="text-center bg-light cabecera-header"
                                data-cabecera-id="{{ $cabecera->id }}" 
                                data-cabecera-padre="{{ $cabecera->cabecera_id }}"
                                data-level="{{ $i }}"
                                style="width: 10px">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    <span class="cabecera-nombre" contenteditable="true" title="Editar nombre">{{ $cabecera->descripcion }}</span>
                                    <button type="button" class="btn btn-sm btn-icon btn-eliminar-cabecera" data-cabecera-id="{{ $cabecera->id }}" title="Eliminar cabecera">
                                        <i class="ri fs-4 ri-delete-bin-fill" style="font-size: 0.8rem;"></i>
                                    </button>
                                </div>
                            </th>
                        @else
                            <th colspan="{{ countLeaves($cabecera) }}"
                                class="text-center bg-light cabecera-header"
                                data-cabecera-id="{{ $cabecera->id }}"
                                data-cabecera-padre="{{ $cabecera->cabecera_id }}"
                                data-level="{{ $i }}"
                                >
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    <span class="cabecera-nombre" contenteditable="true" title="Editar nombre">{{ $cabecera->descripcion }}</span>
                                    <button type="button" class="btn btn-sm btn-icon btn-eliminar-cabecera" data-cabecera-id="{{ $cabecera->id }}" title="Eliminar cabecera">
                                        <i class="ri fs-4 ri-delete-bin-fill" style="font-size: 0.8rem;"></i>
                                    </button>
                                </div>
                            </th>
                        @endif

                    @endforeach
                    <th class="text-muted bg-secondary text-white button-cell-right transparent-cell" 
                        title="Agregar Cabecera" 
                        style="position: relative;"
                        data-level="{{ $i }}">
                        <div id="btnAdd{{ $i }}" style="background: linear-gradient(90deg, #018328, #05c63f); z-index: 2; position: absolute;"
                            onmouseover="this.style.background='linear-gradient(90deg, #018328, #1eef5d )'; this.style.width='50px'; this.style.transition='all 0.2s ease'" 
                            onmouseout="this.style.background='linear-gradient(90deg, #018328, #05c63f)'; this.style.width='40px'; this.style.transition='all 0.2s ease'"
                            data-bs-toggle="popover"
                            data-bs-html="true"
                            data-bs-placement="top"
                            data-bs-trigger="click"
                            data-bs-content='
                            <div class="d-flex align-items-center gap-2" style="min-width:200px;">
                                <input 
                                    type="text" 
                                    class="form-control form-control-sm input-nueva-cabecera" 
                                    data-level="{{ $i }}"
                                    placeholder="Nombre de cabecera"
                                >
                                <button class="btn btn-sm btn-success btnAgregarSubCabecera" data-level="{{ $i }}">
                                    +
                                </button>
                            </div>
                            '
                            >
                                <i class="fs-5 ri ri-add-fill"></i>
                        </div>
                    </th>
                </tr>
            @endfor

        </thead>

        <tbody>

            @foreach($variable->subvariables as $subvariable)

                <tr class="subvariable-row" data-subvariable-id="{{ $subvariable->id }}" data-estado="{{ $subvariable->estado ?? 'activo' }}">
                    <td class="button-cell-left" data-subvariable-id="{{ $subvariable->id }}" title="Eliminar subvariable" style="padding-right: 0; padding-left: 0; display: flex; flex-direction: row-reverse;">
                        <div class="btn-eliminar-subvariable" style="background: linear-gradient(90deg, #c63805, #830e01); align-items: right;"
                            onmouseover="this.style.background='linear-gradient(90deg, #e44810, #830e01)'; this.style.width='50px'; this.style.transition='all 0.2s ease'" 
                            onmouseout="this.style.background='linear-gradient(90deg, #c63805, #830e01)'; this.style.width='40px'; this.style.transition='all 0.2s ease'">
                            <i class="fs-5 ri ri-delete-bin-fill"></i>
                        </div>
                    </td>
                    {{-- Columna Subvariable --}}
                    <td class="fw-semibold data subvariable-nombre" contenteditable="true" title="Editar nombre">
                        {{ $subvariable->descripcion }}
                        <input type="checkbox" title="Ocultar" class="form-check-input estado-toggle ri ri-eye-fill" data-id="{{ $subvariable->id }}" {{ (isset($subvariable->estado) && $subvariable->estado !== 'activo') ? '' : 'checked' }}>
                    </td>

                    {{-- Columnas cabeceras (solo lectura) --}}
                    @for($i = 0; $i < $variable->total_leaf_columns; $i++)
                        <td class="data celda-dato"></td>
                    @endfor

                </tr>

            @endforeach

        </tbody>

    </table>

<div class="mt-3 mb-3">
    <button type="button" class="btn btn-primary btn-sm" id="btnAgregarSubvariable" title="Agregar subvariable">
        <i class="ri ri-add-line"></i> Agregar Subvariable
    </button>
</div>

</form>

<script>    
    $(function() {
        const variableId = {{ $variable->id }};
        const totalColumns = {{ $variable->total_leaf_columns }};
        // Insert 'Agregar Nivel' button into the last header row (only visible at deepest level)
        function ensureButtons() {
            const headerRows = $('#tablaVariable thead tr');
            if (!headerRows.length) return;
            const lastRow = headerRows.last();
            const lastCell = lastRow.find('th').last();
            // remove any existing add-level buttons from other rows and append to last row
            $('#btnLevel').remove();
            $('.btnEliminarNivel').remove();
            const btnAdd = $('<div id="btnLevel" style="background: linear-gradient(90deg, #017483, #056fc6); z-index: 1; position: absolute; width: 70px; align-items: right;"><i class="fs-5 ri ri-layout-row-fill"></i></div>');
            btnAdd.attr('onmouseover', "this.style.background='linear-gradient(90deg, #017483, #1384e1)'; this.style.width='80px'; this.style.transition='all 0.2s ease'");
            btnAdd.attr('onmouseout', "this.style.background='linear-gradient(90deg, #017483, #056fc6)'; this.style.width='70px'; this.style.transition='all 0.2s ease'");
            btnAdd.attr('data-bs-toggle', 'popover');
            btnAdd.attr('data-bs-html', 'true');
            btnAdd.attr('data-bs-trigger', 'click');
            btnAdd.attr('data-bs-placement', 'top');
            pop = `
                <div class="d-flex align-items-center gap-2" style="min-width:200px;">
                    <input 
                        id="inputNivel"
                        type="text" 
                        class="form-control form-control-sm input-nuevo-nivel" 
                        placeholder="Nombre de nivel"
                    >
                    <button id="btnAgregarNivel" class="btn btn-sm btn-success" data-level="0">
                        +
                    </button>
                </div>`;
            btnAdd.attr('data-bs-content', pop);
            const btnDel = $('<div class="btnEliminarNivel" style="background: linear-gradient(90deg, #830e01, #c63805); z-index: 0; position: absolute; width: 100px; align-items: right;" title="Eliminar Nivel"><i class="fs-5 ri ri-delete-bin-fill"></i></div>');
            btnDel.attr('onmouseover', "this.style.background='linear-gradient(90deg, #830e01, #e34a12)'; this.style.width='110px'; this.style.transition='all 0.2s ease'");
            btnDel.attr('onmouseout', "this.style.background='linear-gradient(90deg, #830e01, #c63805)'; this.style.width='100px'; this.style.transition='all 0.2s ease'");
            lastCell.append(btnAdd);
            lastCell.append(btnDel);
            $('[data-bs-toggle="popover"]').popover({
                html: true,
                sanitize: false
            })
        }
        ensureButtons();
        // Toggle estado: disable row
        $(document).on('change', '.estado-toggle', function() {
            const row = $(this).closest('tr');
            if ($(this).is(':checked')) {
                $(this).removeClass('ri-eye-close-fill');
                $(this).addClass('ri-eye-fill');
                row.removeClass('row-disabled');
                row.find('[contenteditable]').attr('contenteditable', 'true');
            } else {
                $(this).removeClass('ri-eye-fill');
                $(this).addClass('ri-eye-close-fill');
                row.addClass('row-disabled');
                row.find('[contenteditable]').removeAttr('contenteditable');
            }
        });

        // aplicar estado inicial
        $('.estado-toggle').each(function() {
            const toggle = $(this);
            if (!toggle.is(':checked')) {
                const row = toggle.closest('tr');
                row.addClass('row-disabled');
                row.find('[contenteditable]').removeAttr('contenteditable');
            }
        });
        
        var cabsToDelete = [];
        function obtenerDescendientes(cabeceraId) {

            let pendientes = [cabeceraId];
            let todos = [cabeceraId];

            while (pendientes.length > 0) {

                let actual = pendientes.shift();

                let hijos = $(`#tablaVariable thead th[data-cabecera-padre="${actual}"]`);

                hijos.each(function () {

                    let hijoId = $(this).attr('data-cabecera-id');

                    if (hijoId && !todos.includes(hijoId)) {
                        todos.push(hijoId);
                        pendientes.push(hijoId);
                    }

                });

            }
            return todos;
        }
        
        function eliminarCabecera(cabeceraId) {

            const thead = $('#tablaVariable thead');
            const tbody = $('#tablaVariable tbody');

            const idsEliminar = obtenerDescendientes(cabeceraId);
            const padreId = thead.find(`th[data-cabecera-id="${cabeceraId}"]`).attr('data-cabecera-padre');

            let columnasEliminar = parseInt(thead.find(`th[data-cabecera-id="${cabeceraId}"]`).attr('colspan')) || 1;

            let cabeceraPadre = thead.find(`th[data-cabecera-id="${padreId}"]`);

            while (cabeceraPadre.length > 0) {
                const colspan = parseInt(cabeceraPadre.attr('colspan')) || 1;
                cabeceraPadre.attr('colspan', colspan - columnasEliminar);
                cabeceraPadre = thead.find(`th[data-cabecera-id="${cabeceraPadre.attr('data-cabecera-padre')}"]`);
            }

            idsEliminar.forEach(function(id) {
                const th = thead.find(`th[data-cabecera-id="${id}"]`);

                th.each(function(){
                    $(this).remove();
                });

            });

            // eliminar columnas de datos
            tbody.find('tr').each(function () {

                const fila = $(this);

                for (let i = 0; i < columnasEliminar; i++) {
                    fila.find('td.data').last().remove();
                }

            });

            // guardar ids existentes para borrar en backend
            if (!isNaN(cabeceraId)) {
                cabsToDelete.push(cabeceraId);
            }
        }
        // Eliminar cabecera
        $(document).on('click', '.btn-eliminar-cabecera', function () {
            const cabeceraId = $(this).attr('data-cabecera-id');
            const cabecera = $('#tablaVariable thead th[data-cabecera-id="' + cabeceraId + '"]');
            let padreId = cabecera.attr('data-cabecera-padre');
            if ($('#tablaVariable thead th[data-cabecera-padre="' + padreId + '"]').length > 1) {
                if (!confirm('¿Eliminar esta cabecera y todas sus subcabeceras?')) {
                    return;
                }
                eliminarCabecera(cabeceraId);
            } else {
                alert('No se puede eliminar todas las subcabeceras');
                return;
            }
        });
        // Eliminar subvariable
        var subvarsToDelete = [];
        $(document).on('click', '.btn-eliminar-subvariable', function(e) {
            e.preventDefault();
            const row = $(this).closest('tr');
            if (confirm('¿Eliminar esta subvariable?')) {
                const id = row.data('subvariable-id');
                if (id && String(id).match(/^\d+$/)) {
                    subvarsToDelete.push(id);
                }
                row.remove();
            }
        });

        // Agregar subvariable
        $('#btnAgregarSubvariable').on('click', function() {
            var numCols = 0;
            const headerRows = $('#tablaVariable thead tr').first();
            const cabeceras = headerRows.find('th.cabecera-header, th.cabecera-header-new, th.subcabecera-header-new');
            cabeceras.each(function() {
                const colspan = parseInt($(this).attr('colspan')) || 1;
                numCols += colspan;
            })
            let unique = 'new-' + Date.now();
            let filaHtml = '<tr class="subvariable-row" data-subvariable-id="' + unique + '" data-estado="activo">';
            filaHtml += '<td class="button-cell-left" data-cabecera-id="' + unique + '" title="Eliminar subvariable" style="padding-right: 0; padding-left: 0; display: flex; flex-direction: row-reverse;">';
            filaHtml += `<div class="btn-eliminar-subvariable" style="background: linear-gradient(90deg, #c63805, #830e01); align-items: right;"
                            onmouseover="this.style.background='linear-gradient(90deg, #e44810, #830e01)'; this.style.width='50px'; this.style.transition='all 0.2s ease'" 
                            onmouseout="this.style.background='linear-gradient(90deg, #c63805, #830e01)'; this.style.width='40px'; this.style.transition='all 0.2s ease'">`;
            filaHtml += '<i class="fs-5 ri ri-delete-bin-fill"></i></div></td>';
            filaHtml += '<td class="fw-semibold data subvariable-nombre" contenteditable="true" title="Editar nombre">Nueva subvariable <input type="checkbox" title="Ocultar" class="form-check-input estado-toggle ri ri-eye-fill" checked></td>';
            
            for (let i = 0; i < numCols; i++) {
                filaHtml += '<td class="data celda-dato"></td>';
            }
            
            filaHtml += '</tr>';
            
            
            $('#tablaVariable tbody').append(filaHtml);
        });

        function actualizarColspanAncestros(cabecera, incremento) {

            let parentId = cabecera.attr('data-cabecera-padre');

            while (parentId) {

                const parentTh = $(
                    `#tablaVariable thead th[data-cabecera-id="${parentId}"]`
                );

                if (!parentTh.length) break;

                const actual = parseInt(parentTh.attr('colspan')) || 1;

                parentTh.attr('colspan', actual + incremento);

                parentId = parentTh.attr('data-cabecera-padre');
            }
        }

        function clonarDescendencia(nextLevel, plantillaPadre, origenId) {
            var i = new_subcabeceras_count
            const headerRows = $('#tablaVariable thead tr');
            if (nextLevel >= headerRows.length) return;

            const row = headerRows.eq(nextLevel);

            const hijos = row.find(
                `th[data-cabecera-padre="${plantillaPadre.attr('data-cabecera-id')}"]`
            );
            
            level = parseInt(headerRows.find('th[data-cabecera-id="' + origenId + '"]').attr('data-level'));
            var anchor = headerRows.find('th[data-cabecera-id="' + origenId + '"]');
            while (level < nextLevel) {
                anchor = headerRows.find('th[data-cabecera-padre="' + anchor.attr('data-cabecera-id') + '"]').last();
                level++
            }

            hijos.each(function() {
                new_subcabeceras_count++
                const hijo = $(this);
                const newId = 'new-' + new_subcabeceras_count;
                const cloned = hijo.clone(true);

                if (hijo.hasClass('cabecera-header')) {
                    cloned.removeClass('cabecera-header');
                    cloned.addClass('subcabecera-header-new');
                }

                cloned.attr('data-cabecera-id', newId)
                cloned.attr('data-cabecera-padre', 'new-' + i )
                cloned.find('.btn-eliminar-cabecera').attr('data-cabecera-id', newId)
                
                cloned.insertAfter(anchor);
                anchor = cloned
                clonarDescendencia(
                    nextLevel + 1,
                    hijo,   
                    origenId
                );
            });
        };

        var new_subcabeceras_count = 0

        $(document).on('click', '.btnAgregarSubCabecera', function() {
            const headerRows = $('#tablaVariable thead tr');
            const level = parseInt($(this).data('level'));
            const currentRow = headerRows.eq(level);
            const nombre = $('input[data-level="' + level + '"]').val().trim();
            const btnPop = $('#btnAdd' + level)
            btnPop.click()
            if (level != 0) {
                const parentRow = headerRows.eq(level - 1);
                var padres = parentRow.find('th.cabecera-header, th.cabecera-header-new, th.subcabecera-header-new');
            } else {
                var padres = currentRow.find('th.cabecera-header, th.cabecera-header-new, th.subcabecera-header-new').last().clone(true);
                padres.attr('data-cabecera-padre', '');
                padres.attr('data-cabecera-id', '');
            }
            
            padres.each(function() {
                new_subcabeceras_count++
                const padre = $(this);
                const padreId = padre.attr('data-cabecera-id');
                const colspanPadre = parseInt(padre.attr('colspan')) || 1;
                const hijos = currentRow.find(
                    `th[data-cabecera-padre="${padreId}"]`
                );

                if (!hijos.length) return;

                const plantilla = hijos.last();
                const plantillaId = plantilla.attr('data-cabecera-id');
                const leafCount = parseInt(plantilla.attr('colspan')) || 1;

                const newId = 'new-' + new_subcabeceras_count;

                const nuevo = plantilla.clone(true);

                if (nuevo.hasClass('cabecera-header')) {
                    nuevo.removeClass('cabecera-header');
                    nuevo.addClass('subcabecera-header-new');
                }

                nuevo.attr('data-cabecera-id', newId)
                    .find('.cabecera-nombre')
                    .text(nombre);
                nuevo.find('.btn-eliminar-cabecera').attr('data-cabecera-id', newId);

                nuevo.insertAfter(plantilla);
                
                if (padre.attr('data-cabecera-id')) {
                    padre.attr('colspan', colspanPadre + leafCount);
                    actualizarColspanAncestros(padre, leafCount);
                }
                clonarDescendencia(level + 1, plantilla, plantillaId);
                
                $('#tablaVariable tbody tr').each(function() {
                    const deleteCell = $(this).find('td:last');
                    for (let i = 0; i < leafCount; i++) {
                        $('<td class="data celda-dato"></td>')
                            .insertBefore(deleteCell);
                    }
                });

            });

        });

        $(document).on('click', '#btnAgregarNivel', function() {
            const headerRows = $('#tablaVariable thead tr');
            if (!headerRows.length) return;
            const lastRow = headerRows.last();

            const parents = lastRow.find('.cabecera-header, .cabecera-header-new, .subcabecera-header-new');
            if (!parents.length) return;

            const newRow = $('<tr></tr>');
            const newLevel = headerRows.length;
            const nombre = $('#inputNivel').val().trim();
            const btnPop = $('#btnLevel')
            btnPop.click()
            parents.each(function() {
                new_subcabeceras_count++
                const parent = $(this);
                const parentId = parent.attr('data-cabecera-id') || parent.attr('data-new-id') || '';

                const child = parent.clone(true);
                child.attr('data-cabecera-id', 'new-' + new_subcabeceras_count);
                child.attr('data-cabecera-padre', parentId);
                child.attr('data-level', newLevel);
                child.find('.cabecera-nombre').text(nombre);
                if (child.hasClass('cabecera-header')) {
                    child.removeClass('cabecera-header');
                    child.addClass('subcabecera-header-new');
                }
                child.find('.btn-eliminar-cabecera').attr('data-cabecera-id', 'new-' + new_subcabeceras_count);
                // const child = $(
                //     '<th class="text-center bg-light cabecera-header-new" ' +
                //     'data-cabecera-id="' + new_subcabeceras_count + '" ' +
                //     'data-cabecera-padre="' + parentId + '" ' +
                //     'data-level="' + newLevel + '">' +
                //         '<div class="d-flex justify-content-center align-items-center gap-1">' +
                //             '<span class="cabecera-nombre" contenteditable="true">Nueva Cabecera</span>' +
                //         '</div>' +
                //     '</th>'
                // );
                newRow.append(child);
            });

            // add a control cell at the end with 'Agregar Subcabecera' button for this new level
            const controlCell = $(
                `
                <th class="text-muted bg-secondary text-white button-cell-right transparent-cell" 
                        title="Agregar Cabecera" 
                        style="position: relative;"
                        data-level="${newLevel}">
                        <div id="btnAdd${newLevel}" style="background: linear-gradient(90deg, #018328, #05c63f); z-index: 2; position: absolute;"
                            data-bs-toggle="popover"
                            data-bs-html="true"
                            data-bs-placement="top"
                            data-bs-trigger="click"
                            data-bs-content='
                            <div class="d-flex align-items-center gap-2" style="min-width:200px;">
                                <input 
                                    type="text" 
                                    class="form-control form-control-sm input-nueva-cabecera" 
                                    data-level="${newLevel}"
                                    placeholder="Nombre de cabecera"
                                >
                                <button class="btn btn-sm btn-success btnAgregarSubCabecera" data-level="${newLevel}">
                                    +
                                </button>
                            </div>
                            '
                            >
                                <i class="fs-5 ri ri-add-fill"></i>
                        </div>
                    </th>
                `
            );
            newRow.append(controlCell);

            // increase rowspan of the variable cell in the first header column
            const varCell = $('#tablaVariable thead tr').first().find('th').slice(0, 2);
            const currentRowspan = parseInt(varCell.attr('rowspan')) || 1;
            varCell.each(function() {
                $(this).attr('rowspan', currentRowspan + 1);
            })

            // insert the new header row after the last header row
            lastRow.after(newRow);
            // ensure the add-level button moves to the new last row
            ensureButtons();
        });
        function recalcularColspans() {

            const rows = $('#tablaVariable thead tr');

            // recorrer desde penúltima fila hacia arriba
            for (let i = rows.length - 2; i >= 0; i--) {

                const row = rows.eq(i);
                const nextRow = rows.eq(i + 1);

                row.find('th').each(function () {
                    
                    const th = $(this);
    
                    var idCab = th.attr('data-cabecera-id');
                    
                    if (idCab) {
                        const child = nextRow.find('th[data-cabecera-padre="' + idCab + '"]');
                        var colspan = 0;
                        child.each(function () {
                            colspan += parseInt($(this).attr('colspan')) || 1;
                        })
                        th.attr('colspan', colspan);
                    }

                });

            }

        }
        $(document).on('click', '.btnEliminarNivel', function () {
            if (!confirm('¿Desea eliminar el nivel de cabecera?')) return;
            const tabla = $('#tablaVariable');
            const headerRows = tabla.find('thead tr');

            if (headerRows.length <= 1) return;

            const lastRow = headerRows.last();
            const prelastRow = headerRows.eq(headerRows.length - 2);

            lastRow.find('th').each(function () {
                cabId = $(this).data('cabecera-id');
                
                if (!isNaN(cabId)) {
                    cabsToDelete.push(cabId);
                }
            })
            // contar cuántas columnas hoja se eliminarán
            let columnasEliminar = (lastRow.find('th').length - prelastRow.find('th').length);
            // eliminar última fila de cabeceras
            lastRow.remove();

            
            // actualizar rowspan de las celdas de la primera columna
            const firstRow = tabla.find('thead tr').first();
            const varCells = firstRow.find('th').slice(0, 2);
            
            varCells.each(function () {
                const currentRowspan = parseInt($(this).attr('rowspan')) || 1;
                $(this).attr('rowspan', Math.max(1, currentRowspan - 1));
            });
            
            // eliminar columnas del tbody
            tabla.find('tbody tr').each(function () {
                
                const fila = $(this);
                
                for (let i = 0; i < columnasEliminar; i++) {
                    fila.find('td.data').last().remove();
                }
                
            });
            
            prelastRow.find('.cabecera-header, .subcabecera-header-new').each(function () {
                $(this).attr('colspan', 1);
            })
            // recalcular colspans de todos los padres
            recalcularColspans();

            // mover botones
            ensureButtons();

        });
        // Guardar cambios
        $('#btnGuardarCambios').on('click', function(e) {
            e.preventDefault();
            const cambios = {
                variable: {
                    descripcion: $('#nombreVariable').text().trim()
                },
                subvariables: [],
                cabeceras: [],
                cabeceras_new: [],
                deleted_subvariables: subvarsToDelete,
                deleted_cabeceras: cabsToDelete
            };

            // subvariables
            $('#tablaVariable tbody tr').each(function() {
                const fila = $(this);
                const subvarId = fila.data('subvariable-id');
                const nombre = fila.find('.subvariable-nombre').text().trim();
                const estado = fila.find('.estado-toggle').is(':checked') ? 'activo' : 'inactivo';
                
                if (nombre) {
                    cambios.subvariables.push({
                        id: subvarId,
                        descripcion: nombre,
                        estado: estado
                    });
                }
            });


            // cabeceras
            $('#tablaVariable .cabecera-header').each(function() {
                const header = $(this);
                const cabId = header.data('cabecera-id');
                const cabPadre = header.data('cabecera-padre')
                const nombre = header.find('.cabecera-nombre').text().trim();
                
                
                if (cabId && nombre) {
                    cambios.cabeceras.push({
                        id: cabId,
                        descripcion: nombre,
                        cabecera_id: cabPadre,
                    });
                }
            });


            //subcabeceras
            $('#tablaVariable .subcabecera-header-new').each(function() {
                const th = $(this);
                const nombre = th.find('.cabecera-nombre').text().trim();
                
                if(nombre){
                    cambios.cabeceras_new.push({
                        id: th.data('cabecera-id') ?? null,
                        descripcion: nombre,
                        cabecera_id: th.data('cabecera-padre') ?? null,
                    });
                }
            });
            // Enviar cambios por AJAX
            $.ajax({
                url: '{{ route("variables.guardarTabla", $variable) }}',
                method: 'POST',
                data: JSON.stringify(cambios),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('Cambios guardados exitosamente');
                        location.reload();
                    } else {
                        alert(response.message || 'Error al guardar');
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    let msg = 'Error al guardar los cambios';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                }
            });
        });
    });
</script>

<style>
    .tabla-editable td.data {
        min-height: 2rem;
    }

    .estado-toggle{
        height: 16px;
        width: 16px;
    }

    .editable-titulo {
        cursor: text;
        padding: 0 4px;
    }

    /* row disabled style */
    .row-disabled {
        opacity: 0.5;
    }

    #tablaVariable .cabecera-header{
        padding: 15px 5px;
    }
    
    #tablaVariable .subcabecera-header-new{
        padding: auto 5px;
        background: linear-gradient(0deg, #15dd51, #15dd51);
    }

    #tablaVariable .btn-icon{
        width: 25px;
        color: #d72411;
        box-shadow: none;
    }
</style>

@endsection
