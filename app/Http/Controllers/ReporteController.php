<?php

namespace App\Http\Controllers;

use App\Models\Dato;
use App\Models\Establecimiento;
use App\Models\Formulario;
use App\Models\Municipio;
use App\Models\Periodo;
use App\Models\Red;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scope = $user->alcances()->first();

        $scopeData = $this->resolveScope($scope?->tipo, (int) ($scope?->alcance_id));
        $establecimientosScope = $scopeData['establecimientos'];

        $formularios = Formulario::query()->orderBy('nombre')->get();

        $redesDisponibles = $establecimientosScope
            ->groupBy('red_id')
            ->map(fn ($items) => [
                'id' => (int) $items->first()->red_id,
                'nombre' => $items->first()->red_nombre,
            ])
            ->values();

        $municipiosDisponibles = $establecimientosScope
            ->groupBy('id_mun')
            ->map(fn ($items) => [
                'id' => (int) $items->first()->id_mun,
                'nombre' => $items->first()->municipio_nombre,
                'red_id' => (int) $items->first()->red_id,
            ])
            ->values();

        $redesSeleccionadas = collect($request->input('red_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $municipiosSeleccionados = collect($request->input('municipio_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($scope?->tipo === 'red' && $redesSeleccionadas->isEmpty()) {
            $redesSeleccionadas = collect([(int) $scope->alcance_id]);
        }

        $establecimientosDisponibles = $establecimientosScope
            ->when($redesSeleccionadas->isNotEmpty(), fn ($col) => $col->whereIn('red_id', $redesSeleccionadas))
            ->when($municipiosSeleccionados->isNotEmpty(), fn ($col) => $col->whereIn('id_mun', $municipiosSeleccionados))
            ->values();

        $formularioId = (int) $request->integer('formulario_id');
        $formulario = null;

        if ($formularioId > 0) {
            $formulario = Formulario::with([
                'variables' => function ($query) {
                    $query->with([
                        'subvariables' => function ($subQuery) {
                            $subQuery->where('estado', 'activo');
                        },
                        'cabeceras',
                    ]);
                },
            ])->find($formularioId);
        }

        $periodos = collect();
        $periodoDesde = null;
        $periodoHasta = null;

        if ($formulario) {
            $periodos = Periodo::query()
                ->where('formulario_id', $formulario->id)
                ->orderBy('fecha_inicio')
                ->get();

            $periodoDesde = $request->integer('periodo_desde_id') ?: optional($periodos->first())->id;
            $periodoHasta = $request->integer('periodo_hasta_id') ?: optional($periodos->last())->id;
        }

        $establecimientosSeleccionados = collect($request->input('establecimiento_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $mostrarConsolidado = $request->boolean(
            'mostrar_consolidado'
        );


        $sinEstablecimientosSeleccionados = $establecimientosSeleccionados->isEmpty();

        $rows = [];
        if ($formulario && $periodoDesde && $periodoHasta) {
            $periodosEnRango = $periodos
                ->filter(fn ($periodo) => $periodo->id >= $periodoDesde && $periodo->id <= $periodoHasta)
                ->pluck('id');

            if ($periodosEnRango->isNotEmpty()) {
                $establecimientosParaConsulta = $sinEstablecimientosSeleccionados
                    ? $establecimientosDisponibles->pluck('id')
                    : $establecimientosSeleccionados;

                $datos = Dato::query()
                    ->whereIn('periodo_id', $periodosEnRango)
                    ->whereIn('establecimiento_id', $establecimientosParaConsulta)
                    ->whereIn('subvariable_id', $formulario->variables->flatMap->subvariables->pluck('id'))
                    ->whereIn('cabecera_id', $formulario->variables->flatMap->cabeceras->pluck('id'))
                    ->get();

                $rows = $this->buildRows(
                    $formulario,
                    $datos,
                    $establecimientosDisponibles,
                    $establecimientosSeleccionados,
                    $mostrarConsolidado,
                    $sinEstablecimientosSeleccionados
                );
            }
        }

        return view('reportes.index', [
            'formularios' => $formularios,
            'formulario' => $formulario,
            'periodos' => $periodos,
            'periodoDesde' => $periodoDesde,
            'periodoHasta' => $periodoHasta,
            'redesDisponibles' => $redesDisponibles,
            'municipiosDisponibles' => $municipiosDisponibles,
            'redesSeleccionadas' => $redesSeleccionadas,
            'municipiosSeleccionados' => $municipiosSeleccionados,
            'establecimientosDisponibles' => $establecimientosDisponibles,
            'establecimientosSeleccionados' => $establecimientosSeleccionados,
            'rows' => $rows,
            'mostrarConsolidado' => $mostrarConsolidado,
            'scopeTipo' => $scope?->tipo,
            'scopeNombre' => $scopeData['nombre'],
        ]);
    }

    private function resolveScope(?string $tipo, int $alcanceId): array
    {
        if ($tipo === 'establecimiento') {
            $establecimiento = $this->establecimientosBaseQuery()->where('establecimientos.id', $alcanceId)->first();

            return [
                'establecimientos' => $establecimiento ? collect([$establecimiento]) : collect(),
                'nombre' => $establecimiento?->descripcion,
            ];
        }

        if ($tipo === 'municipio') {
            $establecimientos = $this->establecimientosBaseQuery()
                ->where('establecimientos.id_mun', $alcanceId)
                ->orderBy('establecimientos.descripcion')
                ->get();

            return [
                'establecimientos' => $establecimientos,
                'nombre' => optional(Municipio::find($alcanceId))->nombre ?? 'Municipio',
            ];
        }

        if ($tipo === 'red') {
            $establecimientos = $this->establecimientosBaseQuery()
                ->where('municipios.red_id', $alcanceId)
                ->orderBy('municipios.nombre')
                ->orderBy('establecimientos.descripcion')
                ->get();

            return [
                'establecimientos' => $establecimientos,
                'nombre' => optional(Red::find($alcanceId))->nombre ?? 'Red',
            ];
        }

        if ($tipo === 'sedes') {
            return [
                'establecimientos' => $this->establecimientosBaseQuery()->orderBy('reds.nombre')->orderBy('municipios.nombre')->orderBy('establecimientos.descripcion')->get(),
                'nombre' => 'SEDES',
            ];
        }

        return [
            'establecimientos' => $this->establecimientosBaseQuery()->orderBy('reds.nombre')->orderBy('municipios.nombre')->orderBy('establecimientos.descripcion')->get(),
            'nombre' => 'Cobertura completa',
        ];
    }

    private function establecimientosBaseQuery()
    {
        return Establecimiento::query()
            ->select([
                'establecimientos.*',
                'municipios.nombre as municipio_nombre',
                'municipios.red_id as red_id',
                'reds.nombre as red_nombre',
            ])
            ->join('municipios', 'municipios.id', '=', 'establecimientos.id_mun')
            ->join('reds', 'reds.id', '=', 'municipios.red_id');
    }

    private function buildRows(
        Formulario $formulario,
        Collection $datos,
        Collection $establecimientosDisponibles,
        Collection $establecimientosSeleccionados,
        bool $mostrarConsolidado,
        bool $sinEstablecimientosSeleccionados
    ): array {
        $datosAgrupados = $datos->groupBy(fn ($dato) => implode('-', [
            $dato->establecimiento_id,
            $dato->subvariable_id,
            $dato->cabecera_id,
        ]));

        $establecimientosParaValores = $sinEstablecimientosSeleccionados
            ? collect()
            : $establecimientosDisponibles->whereIn('id', $establecimientosSeleccionados);

        $establecimientosParaConsolidados = $sinEstablecimientosSeleccionados
            ? $establecimientosDisponibles
            : $establecimientosDisponibles->whereIn('id', $establecimientosSeleccionados);

        return $formulario->variables->map(function ($variable) use ($datosAgrupados, $establecimientosParaValores, $establecimientosParaConsolidados, $mostrarConsolidado) {
            $leafCabeceras = $this->leafCabeceras($variable->cabeceras);

            $subvariables = $variable->subvariables->map(function ($subvariable) use ($datosAgrupados, $establecimientosParaValores, $establecimientosParaConsolidados, $leafCabeceras, $mostrarConsolidado) {
                $valoresPorEstablecimiento = $establecimientosParaValores->map(function ($establecimiento) use ($datosAgrupados, $subvariable, $leafCabeceras) {
                    $totales = $leafCabeceras->mapWithKeys(function ($cabecera) use ($datosAgrupados, $subvariable, $establecimiento) {
                        $key = implode('-', [$establecimiento->id, $subvariable->id, $cabecera->id]);
                        $suma = (float) $datosAgrupados->get($key, collect())->sum('valor');

                        return [$cabecera->id => $suma];
                    });

                    return [
                        'establecimiento' => $establecimiento,
                        'totales' => $totales,
                        'total_general' => $totales->sum(),
                    ];
                });

                $consolidadoPorMunicipio = collect();
                $consolidadoPorRed = collect();
                if ($mostrarConsolidado) {
                    $consolidadoPorMunicipio = $establecimientosParaConsolidados
                        ->groupBy('id_mun')
                        ->map(function ($items) use ($datosAgrupados, $subvariable, $leafCabeceras) {
                            $totales = $leafCabeceras->mapWithKeys(function ($cabecera) use ($items, $datosAgrupados, $subvariable) {
                                $valor = $items->sum(function ($establecimiento) use ($datosAgrupados, $subvariable, $cabecera) {
                                    $key = implode('-', [$establecimiento->id, $subvariable->id, $cabecera->id]);
                                    return (float) $datosAgrupados->get($key, collect())->sum('valor');
                                });
                                return [$cabecera->id => $valor];
                            });

                            $primero = $items->first();

                            return [
                                'municipio_nombre' => $primero->municipio_nombre,
                                'red_nombre' => $primero->red_nombre,
                                'totales' => $totales,
                                'total_general' => $totales->sum(),
                            ];
                        })->values();

                    $consolidadoPorRed = $establecimientosParaConsolidados
                        ->groupBy('red_id')
                        ->map(function ($items) use ($datosAgrupados, $subvariable, $leafCabeceras) {
                            $totales = $leafCabeceras->mapWithKeys(function ($cabecera) use ($items, $datosAgrupados, $subvariable) {
                                $valor = $items->sum(function ($establecimiento) use ($datosAgrupados, $subvariable, $cabecera) {
                                    $key = implode('-', [$establecimiento->id, $subvariable->id, $cabecera->id]);
                                    return (float) $datosAgrupados->get($key, collect())->sum('valor');
                                });
                                return [$cabecera->id => $valor];
                            });

                            $primero = $items->first();

                            return [
                                'red_nombre' => $primero->red_nombre,
                                'totales' => $totales,
                                'total_general' => $totales->sum(),
                            ];
                        })->values();
                }

                return [
                    'subvariable' => $subvariable,
                    'establecimientos' => $valoresPorEstablecimiento,
                    'consolidado_municipio' => $consolidadoPorMunicipio,
                    'consolidado_red' => $consolidadoPorRed,
                ];
            });

            return [
                'variable' => $variable,
                'cabeceras' => $leafCabeceras,
                'subvariables' => $subvariables,
            ];
        })->toArray();
    }

    private function leafCabeceras(Collection $cabeceras): Collection
    {
        $cabeceras = $cabeceras->values();
        $parentIds = $cabeceras->pluck('cabecera_id')->filter()->unique();

        return $cabeceras
            ->whereNotIn('id', $parentIds)
            ->sortBy('descripcion')
            ->values();
    }
}