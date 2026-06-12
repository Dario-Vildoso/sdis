@extends('layouts.materio')

@section('title', $formulario->descripcion)

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
<form class="reports">
    <div class="card" style="padding: 20px;">
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-warning">
                        <i class="ri ri-file-list-3-line"></i>
                    </span>
                    <select id="selectorVariable" class="form-select">
                        <option value="">Seleccione una variable</option>
                
                        @foreach($formulario->variables as $variable)
                            <option value="{{ $variable->id }}">
                            {{ $variable->descripcion }}
                            </option>
                        @endforeach    
                    </select>
                </div>
            </div>
            @foreach ($formulario->variables as $variable)
            
            <div class="col-md-6 selectorSubvariable" data-variable-id="{{ $variable->id }}" style="display: none;" >
                <div class="input-group">
                    <span class="input-group-text bg-warning">
                        <i class="ri ri-todo-line"></i>
                    </span>
                    <select class="form-select selectorSubvariable">
                        <option value="">Seleccione una subvariable</option>
                        
                        @foreach($variable->subvariables->where('estado', 'activo') as $subvariable)
                        <option value="{{ $subvariable->id }}">
                            {{ $subvariable->descripcion }}
                        </option>
                        @endforeach
                        
                    </select>
                </div>
            </div>
            @endforeach
        </div>
        <div class="row d-flex mb-3">
            <div class="col-md-6">
                <a href="{{ route('formularios.menu') }}" class="btn btn-secondary btn-sm" title="Volver">
                    <i class="ri ri-arrow-left-line"></i> <span>&nbsp;Volver</span>
                </a>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-end align-items-center mb-3">
                    
                    <span class="me-2">Tabla vertical</span>
                    <i class="ri ri-table-2"></i>&nbsp;&nbsp;
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input toggle-visualization" type="checkbox" role="switch">
                    </div>
                    <i class="ri ri-table-line"></i>
                    <span>&nbsp;Tabla horizontal</span>
                </div>
            </div>
        </div>
        
 
@foreach($formulario->variables as $variable)

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
$printed = [];
$nivelesVisibles = [];

foreach ($rows as $i => $nivel) {
    if (count($nivel) > 1 || count($rows) <= 1) {
        $nivelesVisibles[] = $i;
    }
}
$colspanVariable = 0;

foreach ($rows[0] as $cabecera) {
    $colspanVariable += countLeaves($cabecera);
}
@endphp
<div class="table-responsive table-horizontal" data-variable-id="{{ $variable->id }}" style="display: none;">

    <table class="table table-sm small table-materio">

        <thead>

            @foreach ($variable->subvariables->where('estado', 'activo') as $subvariable)
            <tr>
                <th colspan="{{ $colspanVariable }}" class="fw-semibold subvariable-cell text-white" data-subvariable-id="{{ $subvariable->id }}" style="display: none;">
                    {{ $variable->descripcion }}: {{ $subvariable->descripcion }}
                </th>
            </tr>
            @endforeach
            {{-- Primera fila --}}
            <tr>
                @foreach($rows[0] ?? [] as $cabecera)
                    <th colspan="{{ countLeaves($cabecera) }}"
                        @if($cabecera->hijos->isEmpty())
                            
                        @endif
                        class="text-center bg-secondary text-white cabecera-cell"
                        style="background-color: #139b92;">
                        {{ $cabecera->descripcion }}
                    </th>
                @endforeach
            </tr>

            {{-- Niveles siguientes --}}
            @for($i = 1; $i < $niveles; $i++)
                <tr>
                    @foreach($rows[$i] ?? [] as $cabecera)

                        @php
                            $hue = 176 + ($i * 3);
                            $saturation = 78 - ($i * 5);
                            $lightness = 34 - ($i * 2);
                        @endphp
                        @if($cabecera->hijos->isEmpty())
                            <th rowspan="{{ $niveles - $i }}"
                                class="text-center subcabecera-cell-last"
                                style="background: hsl({{ $hue }}, {{ $saturation }}%, {{ $lightness }}%) !important; width: 80px"
                                >
                                {{ $cabecera->descripcion }}
                            </th>
                        @else
                            <th colspan="{{ countLeaves($cabecera) }}"
                                class="text-center subcabecera-cell"
                                style="background: hsl({{ $hue }}, {{ $saturation }}%, {{ $lightness }}%) !important;"
                                >
                                {{ $cabecera->descripcion }}
                            </th>
                        @endif

                    @endforeach
                </tr>
            @endfor

        </thead>

        <tbody>

            @foreach($variable->subvariables->where('estado', 'activo') as $subvariable)
                <tr>
                    {{-- Columnas cabeceras vacías --}}
                    @foreach(end($rows) ?? [] as $cabecera)
                        <td class="data text-center justify-content-center align-items-center {{ $excepciones->where('subvariable_id', $subvariable->id)->where('cabecera_id', $cabecera->id)->first() ? 'disabled' : '' }}"
                        style="height: 50px;" 
                        contenteditable="{{ $excepciones->where('subvariable_id', $subvariable->id)->where('cabecera_id', $cabecera->id)->first() ? 'false' : 'true' }}" 
                        data-cabecera-id="{{ $cabecera->id }}" data-subvariable-id="{{ $subvariable->id }}" 
                        style="display: none">
                            @if($datos->where('subvariable_id', $subvariable->id)->where('cabecera_id', $cabecera->id)->first())
                                {{ $datos->where('subvariable_id', $subvariable->id)->where('cabecera_id', $cabecera->id)->first()->valor }}
                            @endif
                        </td>
                    @endforeach

                </tr>
            @endforeach

        </tbody>

    </table>

</div>

<div class="table-responsive table-vertical" data-variable-id="{{ $variable->id }}" style="display: none;">

    <table class="table table-sm small table-materio">

        <thead>
                {{-- Subvariables --}}
                @foreach ($variable->subvariables->where('estado', 'activo') as $subvariable)
                <tr>
                    <th style="display: none;" colspan="{{ count($nivelesVisibles) + 1 }}" class="fw-semibold subvariable-cell text-white" data-subvariable-id="{{ $subvariable->id }}">
                        
                            {{ $variable->descripcion }}: {{ $subvariable->descripcion }}
                    </th>
                </tr>
                @endforeach
                {{-- Columnas de cabeceras jerárquicas --}}
                @foreach($nivelesVisibles as $nivelIndex)
                    <th style="background: transparent; border: none; padding: 0px;">
                        <div style="width: {{ 80/(count($nivelesVisibles)+1) }}vw;">
        
                        </div>
                    </th>
                @endforeach
                    <th style="background: transparent; border: none; padding: 0px;">
                        <div style="width: {{ 80/(count($nivelesVisibles)+1) }}vw;">
        
                        </div>
                    </th>
                    
            </tr>
        </thead>

        <tbody>

            @foreach(end($rows) ?? [] as $leaf)

                <tr>

                    {{-- 🔹 Construir la ruta desde la raíz hasta la hoja --}}
                    @php
                        $path = [];
                        $current = $leaf;

                        while ($current) {
                            array_unshift($path, $current);
                            $current = $current->padre ?? null;
                        }
                    @endphp

                    {{-- 🔹 Pintar cabeceras con rowspan SOLO cuando corresponde --}}
                    @foreach($path as $nivel => $cabecera)
                    @if(in_array($nivel, $nivelesVisibles))
                        @php
                            $rowspan = countLeaves($cabecera);
                            // Control para evitar repetir celdas
                            $key = $cabecera->id;
                            $text = ((count($nivelesVisibles) >= 3) && ($nivel != max($nivelesVisibles))) ? 'writing-mode: sideways-lr;' : '';
                        @endphp

                        @if(!isset($printed[$key]))
                            <td rowspan="{{ $rowspan }}"
                                class="text-center fw-semibold text-uppercase"
                                style="background: hsl({{ 213 - ($nivel * 3) }}, 10%, {{ 85 - ($nivel * 5) }}%); {{ $text }}">
                                {{ $cabecera->descripcion }}
                            </td>

                            @php $printed[$key] = true; @endphp
                        @endif
                    @endif
                    @endforeach

                    {{-- 🔹 Datos --}}
                    @foreach($variable->subvariables->where('estado', 'activo') as $subvariable)
                        <td class="data text-center {{ $excepciones->where('subvariable_id', $subvariable->id)->where('cabecera_id', $cabecera->id)->first() ? 'disabled' : '' }}"
                            contenteditable="{{ $excepciones->where('subvariable_id', $subvariable->id)->where('cabecera_id', $cabecera->id)->first() ? 'false' : 'true' }}"
                            data-cabecera-id="{{ $leaf->id }}"
                            data-subvariable-id="{{ $subvariable->id }}">
                            @if($datos->where('subvariable_id', $subvariable->id)->where('cabecera_id', $cabecera->id)->first())
                                {{ $datos->where('subvariable_id', $subvariable->id)->where('cabecera_id', $cabecera->id)->first()->valor }}
                            @endif
                        </td>

                    @endforeach

                </tr>

            @endforeach

        </tbody>

    </table>

</div>
@endforeach
    <div class="mt-3">
        <button type="button" class="btn btn-sm text-white" style="background-color:#139b92;" id="btnGuardarDatos" title="Guardar todos los cambios" style="display: none;">
            <i class="ri ri-save-fill"></i> <span>&nbsp;Guardar</span>
        </button>
    </div>  
   </div>
</form>
<script>
    $(function () {
        var currentData = [];
        function setCurrentData() {
            currentData = [];
            $('div.table-horizontal td.data').filter(function () {
                return $(this).text().trim() !== '';
            }).each(function () {
                currentData.push({
                    valor: $(this).text().trim(),
                    cabecera_id: $(this).data('cabecera-id'),
                    subvariable_id: $(this).data('subvariable-id')
                });
            });
        }
        setCurrentData();
        var formChange = false;
        function setViualization() {
            const checkbox = $('input.toggle-visualization');
            // const activeTables = $('.table-vertical.active');
            if (checkbox.is(':checked')) {
                $('.table-vertical.active').hide();
                $('.table-horizontal.active').show();
            } else {
                $('.table-vertical.active').show();
                $('.table-horizontal.active').hide();
            }
        }
        $('input.toggle-visualization').on('change', setViualization);

        function restoreSelectedVariableState() {
            const savedVariableId = localStorage.getItem('selectedVariable');
            const savedSubvariableId = localStorage.getItem('selectedSubvariable');

            if (savedVariableId) {
                $('#selectorVariable').val(savedVariableId).trigger('change');

                if (savedSubvariableId) {
                    const currentSubvariableSelect = $('div.selectorSubvariable[data-variable-id="'+savedVariableId+'"]').find('select.selectorSubvariable');
                    if (currentSubvariableSelect.length) {
                        currentSubvariableSelect.val(savedSubvariableId).trigger('change');
                    }
                }
            }
        }

        function filterData() {
            const actualData = [];
            $('div.table-horizontal td.data').filter(function () {
                return $(this).text().trim() !== '';
            }).each(function () {
                actualData.push({
                    valor: $(this).text().trim(),
                    cabecera_id: $(this).data('cabecera-id'),
                    subvariable_id: $(this).data('subvariable-id')
                });
            });

            const originalDataMap = {};
            currentData.forEach(item => {
                const key = `${item.subvariable_id}_${item.cabecera_id}`;
                originalDataMap[key] = item.valor;
            });

            const actualDataMap = {};
            actualData.forEach(item => {
                const key = `${item.subvariable_id}_${item.cabecera_id}`;
                actualDataMap[key] = item.valor;
            });

            const filteredData = [];

            currentData.forEach(originalItem => {
                const key = `${originalItem.subvariable_id}_${originalItem.cabecera_id}`;
                const actualValue = actualDataMap[key] !== undefined ? actualDataMap[key] : '';

                if (actualValue === '' || actualValue !== originalItem.valor) {
                    filteredData.push({
                        valor: actualValue,
                        cabecera_id: originalItem.cabecera_id,
                        subvariable_id: originalItem.subvariable_id
                    });
                }
            });

            actualData.forEach(actualItem => {
                const key = `${actualItem.subvariable_id}_${actualItem.cabecera_id}`;
                if (!(key in originalDataMap) && actualItem.valor !== '') {
                    filteredData.push({
                        valor: actualItem.valor,
                        cabecera_id: actualItem.cabecera_id,
                        subvariable_id: actualItem.subvariable_id
                    });
                }
            });

            return filteredData;
        }

        filterData()

        function saveData() {
            const datos = filterData();
            $.post('{{ route('formularios.guardarPeriodo', [$formulario, $periodo]) }}', {
                _token: '{{ csrf_token() }}',
                datos: JSON.stringify(datos)
            }).done(function (response) {
                Swal.fire({
                    // poner toast
                    icon: 'success',
                    text: response.message,
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.startTimer)
                    }
                });
            })
            formChange = false;
            setCurrentData();
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (formChange) {
                    saveData();
                }
            }
        });

        $('#selectorVariable').on('change', function(){
            const variableId = $(this).val();
            // Ocultar todas
            $('.table-responsive').removeClass('active').hide();
            $('div.selectorSubvariable').hide();
            $('select.selectorSubvariable').val('');
            localStorage.setItem('selectedVariable', variableId || '');
            localStorage.setItem('selectedSubvariable', '');
            
            // Mostrar la seleccionada
            if(variableId){
                $('div.selectorSubvariable[data-variable-id="'+variableId+'"]').fadeIn();
            }
        })
        
        $('select.selectorSubvariable').on('change', function(){
    
            const variableId = $('#selectorVariable').val();
            const guardar = $('#btnGuardarDatos');
            const subvariableId = $(this).val();
            
            // Ocultar todas
            $('td.data, th.subvariable-cell').hide();
            localStorage.setItem('selectedSubvariable', subvariableId || '');
            
            // Mostrar la seleccionada
            if(subvariableId && variableId){
                $('.table-responsive[data-variable-id="'+variableId+'"]').addClass('active').slideDown();
                $('th.subvariable-cell[data-subvariable-id="'+subvariableId+'"]').fadeIn();
                $('td.data[data-subvariable-id="'+subvariableId+'"]').fadeIn();
                guardar.fadeIn();
            } else {
                $('.table-responsive[data-variable-id="'+variableId+'"]').removeClass('active').hide();
            }
            setViualization();
        });

        restoreSelectedVariableState();

        $('td.data').on('input', function(){
            formChange = true;
            const subvariableId = $(this).data('subvariable-id');
            const cabeceraId = $(this).data('cabecera-id');
            
            let el = this;
            
            // Obtener selección actual
            let selection = window.getSelection();
            let range = selection.getRangeAt(0);
            let cursorPosition = range.startOffset;
            
            let texto = el.innerText;
            let numeros = texto.replace(/\D/g, '');
            
            $('td.data[data-subvariable-id="'+subvariableId+'"][data-cabecera-id="'+cabeceraId+'"]').each(function(){
                $(this).text(numeros);
            });

            // Restaurar cursor
            let newRange = document.createRange();
            let textNode = el.firstChild;

            if (textNode) {
                let pos = Math.min(cursorPosition, numeros.length);
                newRange.setStart(textNode, pos);
                newRange.collapse(true);
                
                selection.removeAllRanges();
                selection.addRange(newRange);
            }
        });
        
        $('#btnGuardarDatos').on('click', function(){
            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');
            saveData();
            $(this).prop('disabled', false).html('<i class="ri ri-save-fill"></i> <span>&nbsp;Guardar</span>');
        });
        
        window.addEventListener('beforeunload', function (e) {
            if (!formChange) return;

            e.preventDefault();
            e.returnValue = '';

        });
    });
</script>
<style>
    table.table-materio th.cabecera-cell {
        border: none;
        border-radius: 0 0 0 0;
        padding: 8px 10px !important;
        
    }
    table.table-materio th.variable-cell {
        border: none;
        background: hsl(199, 15%, 61%) !important;
        
        border-radius: 0 20px 0 0;
        padding: 0;
    }
    table.table-materio th.subcabecera-cell {
        border: none;
        border-radius: 0 0 0 0;
        padding: 8px 10px !important;
    }
    table.table-materio th.subcabecera-cell-last {
        border: none;
        border-radius: 0 0 0 0;
        padding: 8px 10px !important;
        
    }
    table.table-materio th.subvariable-cell{
        background: #086375 !important;
        border: none;
        padding: 10px 10px !important;
        text-align: center;
    }
    table.table-materio td.subvariable-cell {
        background: hsl(199, 12%, 45%) !important;
        padding: 5px 10px !important;
    }
    table.table-materio td.data {
        padding: 10px 10px !important;
    }
    table.table-materio td.celda-apagada {
        background-color: black;
        opacity: 0.2;
    }

    .sticky-col {
        position: sticky;
        writing-mode: ;
        left: 0;
        background: #f8f9fa;
        z-index: 2;
        min-width: 140px;        /* espacio para el texto largo */
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }

</style>
@endsection
