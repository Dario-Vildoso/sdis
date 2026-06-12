<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Exportación {{ $formulario->nombre }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    @php
        $periodoDesdeTexto = optional($periodos->firstWhere('id', $periodoDesde))->descripcion;
        $periodoHastaTexto = optional($periodos->firstWhere('id', $periodoHasta))->descripcion;
        $periodoRango = trim(($periodoDesdeTexto ?? '') . '-' . ($periodoHastaTexto ?? ''), '-');

        $redes = $resumenSeleccion['redes'] ?? collect();
        $municipios = $resumenSeleccion['municipios'] ?? collect();
        $establecimientos = $resumenSeleccion['establecimientos'] ?? collect();
    @endphp

    <h2 style="text-align: center;">Reporte - {{ $formulario->sistema->nombre }}: {{ $formulario->gestion->nombre }}</h2>
    <div style="font-size: large;">
        <strong>Formulario:</strong> {{ $formulario->descripcion }}
    </div>
    <div class="row">
        @if($redes->isNotEmpty() || $municipios->isNotEmpty() || $establecimientos->isNotEmpty())
            <div style="font-size: large;" class="col-md-4">
                <strong>Red:</strong> {{ $redes->implode(', ') }} &nbsp; <strong>Municipio:</strong> {{ $municipios->implode(', ') }} &nbsp; <strong>Establecimiento:</strong> {{ $establecimientos->take(5)->implode(', ') . ($establecimientos->count() > 5 ? '...' : '') }}
            </div>
        @endif

    </div>
    <div class="row">
        <div style="font-size: large;" class="col-md-4">
            <strong>Gestión:</strong> {{ $gestionNombre ?? 'Sin gestión' }} &nbsp; <strong>Periodo:</strong> {{ $periodoRango }}
        </div>
    </div>

    @foreach($rows as $row)
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

            $buildRows = function ($cabeceras, $childrenByParent, $nivel = 0, &$rowsBuild = []) use (&$buildRows) {
                foreach ($cabeceras as $cabecera) {
                    $rowsBuild[$nivel][] = $cabecera;
                    $children = $childrenByParent->get($cabecera->id, collect());

                    if ($children->isNotEmpty()) {
                        $buildRows($children, $childrenByParent, $nivel + 1, $rowsBuild);
                    }
                }

                return $rowsBuild;
            };

            $childrenByParent = $row['cabeceras_todas']->groupBy('cabecera_id');
            $rootCabeceras = $childrenByParent->get(null, collect())->sortBy('id')->values();
            $niveles = $maxDepth($rootCabeceras, $childrenByParent);
            $headerRows = $buildRows($rootCabeceras, $childrenByParent);
        @endphp

        <table cellspacing="0" cellpadding="4" style="border-collapse: collapse; margin-top: 8px; margin-bottom: 20px;">
            <thead>
                <tr>
                    <th style="border:1px solid #727272; background-color: #086375; color: white;" rowspan="{{ max($niveles, 1) }}" class="bg-warning">{{ $row['variable']->descripcion }}</th>
                    @foreach($headerRows[0] ?? [] as $cabecera)
                        @php
                            $children = $childrenByParent->get($cabecera->id, collect());
                        @endphp

                        @if($children->isEmpty())
                            <th style="border:1px solid #727272; background-color: #139b92; color: white;" rowspan="{{ max($niveles, 1) }}">{{ $cabecera->descripcion }}</th>
                        @else
                            <th style="border:1px solid #727272; background-color: #139b92; color: white;" colspan="{{ $countLeaves($cabecera, $childrenByParent) }}">{{ $cabecera->descripcion }}</th>
                        @endif
                    @endforeach
                    <th style="border:1px solid #727272; background-color: #086375; color: white;" rowspan="{{ max($niveles, 1) }}">Total</th>
                </tr>

                @for($i = 1; $i < $niveles; $i++)
                    <tr>
                        @foreach($headerRows[$i] ?? [] as $cabecera)
                            @php
                                $children = $childrenByParent->get($cabecera->id, collect());
                            @endphp

                            @if($children->isEmpty())
                                <th style="border:1px solid #727272; background-color: #139b92; color: white;" rowspan="{{ $niveles - $i }}" style="white-space: nowrap; overflow: hidden;">
                                    {{ $cabecera->descripcion }}
                                </th>
                            @else   
                                <th style="border:1px solid #727272; background-color: #139b92; color: white;" colspan="{{ $countLeaves($cabecera, $childrenByParent) }}" style="white-space: nowrap; overflow: hidden;">
                                    {{ $cabecera->descripcion }}
                                </th>
                            @endif
                        @endforeach
                    </tr>
                @endfor
            </thead>
            <tbody>
                @foreach($row['subvariables'] as $subData)
                    <tr>
                        <td style="border:1px solid #727272; background-color: #657881; color: white;">{{ $subData['subvariable']->descripcion }}</td>
                        @foreach($row['cabeceras_hoja'] as $cabecera)
                            <td style="border:1px solid #727272;">{{ $subData['totales']->get($cabecera->id, 0) }}</td>
                        @endforeach
                        <td style="border:1px solid #727272; background-color: #eef8f7;">{{ $subData['totales']->sum() }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="border:1px solid #727272; background-color: #086375; color: white;"><strong>Total</strong></td>
                    @foreach($row['cabeceras_hoja'] as $cabecera)
                        @php
                            $sum = 0;
                            foreach($row['subvariables'] as $subData) {
                                $sum += $subData['totales']->get($cabecera->id, 0);
                            }
                        @endphp
                        <td style="border:1px solid #727272; background-color: #eef8f7;"><strong>{{ $sum }}</strong></td>
                    @endforeach
                    @php
                        $totalSum = 0;
                        foreach($row['subvariables'] as $subData) {
                            $totalSum += $subData['totales']->sum();
                        }
                    @endphp
                    <td style="border:1px solid #727272; background-color: #eef8f7;"><strong>{{ $totalSum }}</strong></td>
                </tr>
                <tr></tr>
            </tbody>
        </table>
    @endforeach
</body>
</html>
