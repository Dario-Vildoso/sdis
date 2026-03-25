@extends('layouts.materio')

@section('title', 'Usuarios')

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
<div class="row mb-3">

    <div class="col-md-6">
        <select id="selectorVariable" class="form-select">
            <option value="">Seleccione una variable</option>
    
            @foreach($formulario->variables as $variable)
                <option value="{{ $variable->id }}">
                    {{ $variable->descripcion }}
                </option>
            @endforeach
    
        </select>
    </div>
    @foreach ($formulario->variables as $variable)
    
    <div class="col-md-6 selectorSubvariable" data-variable-id="{{ $variable->id }}" style="display: none;" >
        <select class="form-select selectorSubvariable">
            <option value="">Seleccione una subvariable</option>
            
            @foreach($variable->subvariables as $subvariable)
            <option value="{{ $subvariable->id }}">
                {{ $subvariable->descripcion }}
            </option>
            @endforeach
            
        </select>
    </div>
    @endforeach
</div>
<div class="d-flex justify-content-end align-items-center mb-3">
    
    <span class="me-2">Tabla vertical</span>
    <i class="ri ri-table-2"></i>&nbsp;&nbsp;
    <div class="form-check form-switch m-0">
        <input class="form-check-input toggle-visualization" type="checkbox" role="switch">
    </div>
    <i class="ri ri-table-line"></i>
    <span>&nbsp;Tabla horizontal</span>
</div>
        <a href="{{ route('formularios.menu') }}" class="btn btn-secondary btn-sm" title="Volver">
            <i class="ri ri-arrow-left-line"></i> <span>&nbsp;Volver</span>
        </a>
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
@endphp
<div class="table-responsive table-horizontal" data-variable-id="{{ $variable->id }}" style="display: none;">


    <table class="table table-sm small table-materio">

        <thead>

            @foreach ($variable->subvariables as $subvariable)
            <tr>
                <th colspan="{{ countLeaves($rows[0][0]) }}" class="fw-semibold subvariable-cell text-white" data-subvariable-id="{{ $subvariable->id }}" style="display: none;">
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
                        style="background: {{ $loop->even ? '#9d9fa3 !important' : '#9d9fa3 !important' }}">
                        {{ $cabecera->descripcion }}
                    </th>
                @endforeach
            </tr>

            {{-- Niveles siguientes --}}
            @for($i = 1; $i < $niveles; $i++)
                <tr>
                    @foreach($rows[$i] ?? [] as $cabecera)

                        @php
                            $hue = 213 - ($i * 3);   
                            $saturation = 10;
                            $lightness = 82 - ($i * 5);
                        @endphp
                        <!-- hsl(213, 30%, 32%) -->
                        @if($cabecera->hijos->isEmpty())
                            <th rowspan="{{ $niveles - $i }}"
                                class="text-center subcabecera-cell-last"
                                style="background: hsl({{ $hue }}, {{ $saturation }}%, {{ $lightness }}%); width: 80px"
                                >
                                {{ $cabecera->descripcion }}
                            </th>
                        @else
                            <th colspan="{{ countLeaves($cabecera) }}"
                                class="text-center subcabecera-cell"
                                style="background: hsl({{ $hue }}, {{ $saturation }}%, {{ $lightness }}%)"
                                >
                                {{ $cabecera->descripcion }}
                            </th>
                        @endif

                    @endforeach
                </tr>
            @endfor

        </thead>

        <tbody>

            @foreach($variable->subvariables as $subvariable)

                <tr>
                    {{-- Columnas cabeceras vacías --}}
                    @foreach(end($rows) ?? [] as $cabecera)
                        <td class="data text-center justify-content-center align-items-center" style="height: 50px;" contenteditable="true" data-cabecera-id="{{ $cabecera->id }}" data-subvariable-id="{{ $subvariable->id }}" style="display: none">
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
                @foreach ($variable->subvariables as $subvariable)
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
                    @foreach($variable->subvariables as $subvariable)

                        <td class="data text-center"
                            contenteditable="true"
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
    <div>
        <button type="button" class="btn btn-success btn-sm" id="btnGuardarDatos" title="Guardar todos los cambios" style="display: none;">
            <i class="ri ri-save-fill"></i> <span>&nbsp;Guardar</span>
        </button>
    </div>  

<script>
    $(function () {
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

        $('#selectorVariable').on('change', function(){
            const variableId = $(this).val();
            // Ocultar todas
            $('.table-responsive').removeClass('active').hide();
            $('div.selectorSubvariable').hide();
            
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

        $('td.data').on('input', function(){
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
            let datos = [];
            $('div.table-horizontal td.data').filter(function () {
                return $(this).text().trim() !== '';
            }).each(function () {
                datos.push({
                    valor: $(this).text().trim(),
                    cabecera_id: $(this).data('cabecera-id'),
                    subvariable_id: $(this).data('subvariable-id')
                });
            });
            $.post('{{ route('formularios.guardarPeriodo', [$formulario, $periodo]) }}', {
                _token: '{{ csrf_token() }}',
                datos: JSON.stringify(datos)
            }).done(function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    timer: 2500,
                    showConfirmButton: false,
                });
                location.reload();
            })
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
        background: hsl(199, 12%, 45%) !important;
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
