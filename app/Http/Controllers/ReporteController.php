<?php

namespace App\Http\Controllers;

use App\Models\Dato;
use App\Models\Establecimiento;
use App\Models\Formulario;
use App\Models\Periodo;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scope = $user->alcances()->first();

        $scopeData = $this->resolveScope($scope?->tipo, (int) ($scope?->alcance_id));
        $establecimientosDisponibles = $scopeData['establecimientos'];

        $formularios = Formulario::query()->orderBy('nombre')->get();

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

        if ($establecimientosSeleccionados->isEmpty()) {
            $establecimientosSeleccionados = $establecimientosDisponibles->pluck('id')->values();
        }

        $mostrarConsolidado = $request->boolean('mostrar_consolidado', $scope?->tipo === 'municipio');

        $rows = [];
        if ($formulario && $periodoDesde && $periodoHasta && $establecimientosSeleccionados->isNotEmpty()) {
            $periodosEnRango = $periodos
                ->filter(fn ($periodo) => $periodo->id >= $periodoDesde && $periodo->id <= $periodoHasta)
                ->pluck('id');

            if ($periodosEnRango->isNotEmpty()) {
                $datos = Dato::query()
                    ->whereIn('periodo_id', $periodosEnRango)
                    ->whereIn('establecimiento_id', $establecimientosSeleccionados)
                    ->whereIn('subvariable_id', $formulario->variables->flatMap->subvariables->pluck('id'))
                    ->whereIn('cabecera_id', $formulario->variables->flatMap->cabeceras->pluck('id'))
                    ->get();

                $rows = $this->buildRows(
                    $formulario,
                    $datos,
                    $establecimientosDisponibles,
                    $establecimientosSeleccionados,
                    $mostrarConsolidado
                );
            }
        }

        return view('reportes.index', [
            'formularios' => $formularios,
            'formulario' => $formulario,
            'periodos' => $periodos,
            'periodoDesde' => $periodoDesde,
            'periodoHasta' => $periodoHasta,
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
            $establecimiento = Establecimiento::query()->find($alcanceId);

            return [
                'establecimientos' => $establecimiento ? collect([$establecimiento]) : collect(),
                'nombre' => $establecimiento?->descripcion,
            ];
        }

        if ($tipo === 'municipio') {
            $establecimientos = Establecimiento::query()
                ->where('id_mun', $alcanceId)
                ->orderBy('descripcion')
                ->get();

            return [
                'establecimientos' => $establecimientos,
                'nombre' => optional(Municipio::find($alcanceId))->descripcion ?? 'Municipio',
            ];
        }

        return [
            'establecimientos' => Establecimiento::query()->orderBy('descripcion')->get(),
            'nombre' => 'Cobertura completa',
        ];
    }

    private function buildRows(
        Formulario $formulario,
        Collection $datos,
        Collection $establecimientosDisponibles,
        Collection $establecimientosSeleccionados,
        bool $mostrarConsolidado
    ): array {
        $datosAgrupados = $datos->groupBy(fn ($dato) => implode('-', [
            $dato->establecimiento_id,
            $dato->subvariable_id,
            $dato->cabecera_id,
        ]));

        $establecimientos = $establecimientosDisponibles
            ->whereIn('id', $establecimientosSeleccionados)
            ->values();

        return $formulario->variables->map(function ($variable) use ($datosAgrupados, $establecimientos, $mostrarConsolidado) {
            $leafCabeceras = $this->leafCabeceras($variable->cabeceras);

            $subvariables = $variable->subvariables->map(function ($subvariable) use ($datosAgrupados, $establecimientos, $leafCabeceras, $mostrarConsolidado) {
                $valoresPorEstablecimiento = $establecimientos->map(function ($establecimiento) use ($datosAgrupados, $subvariable, $leafCabeceras) {
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

                $consolidado = null;
                if ($mostrarConsolidado) {
                    $totalesConsolidados = $leafCabeceras->mapWithKeys(function ($cabecera) use ($valoresPorEstablecimiento) {
                        return [$cabecera->id => (float) $valoresPorEstablecimiento->sum(fn ($item) => $item['totales'][$cabecera->id] ?? 0)];
                    });

                    $consolidado = [
                        'totales' => $totalesConsolidados,
                        'total_general' => $totalesConsolidados->sum(),
                    ];
                }

                return [
                    'subvariable' => $subvariable,
                    'establecimientos' => $valoresPorEstablecimiento,
                    'consolidado' => $consolidado,
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
