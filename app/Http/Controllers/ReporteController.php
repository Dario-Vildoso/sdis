<?php

namespace App\Http\Controllers;

use App\Exports\ReporteVariableExport;
use App\Models\Dato;
use App\Models\Establecimiento;
use App\Models\Establecimiento_Formulario;
use App\Models\Formulario;
use App\Models\Municipio;
use App\Models\Periodo;
use App\Models\Red;
use App\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scope = $user->alcances()->first();

        $scopeData = $this->resolveScope($scope?->tipo, (int) ($scope?->alcance_id));
        $establecimientosScope = $scopeData['establecimientos'];

        $formularios = Formulario::query()->where('sistema_id', $user->sistema_id)->orderBy('id')->get();

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
        
        $formularioIds = $request->formulario_ids
            ? collect($request->formulario_ids)->map(fn ($id) => (int) $id)->filter()->values()
            : collect();

        $establecimientosAsignados = collect();
        if ($formularioIds->isNotEmpty()) {
            $establecimientosAsignados = Establecimiento_Formulario::query()
                ->whereIn('formulario_id', $formularioIds)
                ->pluck('establecimiento_id')
                ->map(fn ($id) => (int) $id)
                ->flip();
        }

        $establecimientosDisponibles = $establecimientosScope
            ->when($redesSeleccionadas->isNotEmpty(), fn ($col) => $col->whereIn('red_id', $redesSeleccionadas))
            ->when($municipiosSeleccionados->isNotEmpty(), fn ($col) => $col->whereIn('id_mun', $municipiosSeleccionados))
            ->when($establecimientosAsignados->isNotEmpty(), fn ($col) => $col->whereIn('id', $establecimientosAsignados->keys()))
            ->values();

        $formulariosSeleccionados = collect();
        $periodosPorFormulario = collect();
        $periodoDesdePorFormulario = [];
        $periodoHastaPorFormulario = [];
        $variablesComunes = collect();
        $formulario = null;

        if ($formularioIds->isNotEmpty()) {
            $formulariosSeleccionados = Formulario::with([
                'variables' => function ($query) {
                    $query->with([
                        'subvariables' => function ($subQuery) {
                            $subQuery->where('estado', 'activo');
                        },
                        'cabeceras',
                    ]);
                },
            ])->whereIn('id', $formularioIds)->where('sistema_id', $user->sistema_id)->get()->sortBy(fn($item) => $formularioIds->search($item->id))->values();
            $formulario = $formulariosSeleccionados->count() === 1 ? $formulariosSeleccionados->first() : null;

            $periodosPorFormulario = Periodo::query()->whereIn('formulario_id', $formulariosSeleccionados->pluck('id'))->orderBy('fecha_inicio')->get()->groupBy('formulario_id');
            foreach ($formulariosSeleccionados as $formularioSeleccionado) {
                $periodosFormulario = $periodosPorFormulario->get($formularioSeleccionado->id, collect());
                $periodoDesdePorFormulario[$formularioSeleccionado->id] = (int) ($request->input('periodo_desde_ids.' . $formularioSeleccionado->id)
                    ?: optional($periodosFormulario->first())->id);
                $periodoHastaPorFormulario[$formularioSeleccionado->id] = (int) ($request->input('periodo_hasta_ids.' . $formularioSeleccionado->id)
                    ?: optional($periodosFormulario->last())->id);
            }
            $commonVariableIds = $formulariosSeleccionados
                ->pluck('variables')
                ->map(fn ($variables) => $variables->pluck('id')->values())
                ->reduce(function ($carry, $ids) {
                    if ($carry === null) {
                        return $ids;
                    }
                    return $carry->intersect($ids)->values();
                });
                
            $variablesComunes = Variable::query()
                ->with([
                    'subvariables' => fn ($query) => $query->where('estado', 'activo'),
                    'cabeceras',
                ])
                ->whereIn('id', $commonVariableIds ?? collect())
                ->orderBy('descripcion')
                ->get();
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
        if ($formulariosSeleccionados->isNotEmpty() && $variablesComunes->isNotEmpty()) {
            $periodosEnRango = $formulariosSeleccionados
                ->flatMap(function ($formularioSeleccionado) use ($periodosPorFormulario, $periodoDesdePorFormulario, $periodoHastaPorFormulario) {
                    $formularioId = $formularioSeleccionado->id;
                    $periodosFormulario = $periodosPorFormulario->get($formularioId, collect());
                    $periodoDesde = (int) ($periodoDesdePorFormulario[$formularioId] ?? 0);
                    $periodoHasta = (int) ($periodoHastaPorFormulario[$formularioId] ?? 0);

                    if (! $periodoDesde || ! $periodoHasta) {
                        return collect();
                    }

                    if ($periodoDesde > $periodoHasta) {
                        [$periodoDesde, $periodoHasta] = [$periodoHasta, $periodoDesde];
                    }

                    return $periodosFormulario
                        ->filter(fn ($periodo) => $periodo->id >= $periodoDesde && $periodo->id <= $periodoHasta)
                        ->pluck('id');
                })
                ->unique()
                ->values();

            if ($periodosEnRango->isNotEmpty()) {
                $establecimientosParaConsulta = $sinEstablecimientosSeleccionados
                    ? $establecimientosDisponibles->pluck('id')
                    : $establecimientosSeleccionados;

                $datos = Dato::query()
                    ->whereIn('periodo_id', $periodosEnRango)
                    ->whereIn('establecimiento_id', $establecimientosParaConsulta)
                    ->whereIn('subvariable_id', $variablesComunes->flatMap->subvariables->pluck('id'))
                    ->whereIn('cabecera_id', $variablesComunes->flatMap->cabeceras->pluck('id'))
                    ->get();

                $rows = $this->buildRowsFromVariables(
                    $variablesComunes,
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
            'formulariosSeleccionados' => $formulariosSeleccionados,
            'formularioIdsSeleccionados' => $formulariosSeleccionados->pluck('id'),
            'periodosPorFormulario' => $periodosPorFormulario,
            'periodoDesdePorFormulario' => $periodoDesdePorFormulario,
            'periodoHastaPorFormulario' => $periodoHastaPorFormulario,
            'hayVariablesComunes' => $variablesComunes->isNotEmpty(),
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

    public function oportunidad(Request $request)
    {
                $formularios = Formulario::query()->orderBy('id')->get();

        $formulario = null;
        $periodos = collect();
        $periodoDesde = (int) $request->integer('periodo_desde_id');
        $periodoHasta = (int) $request->integer('periodo_hasta_id');
        $filas = collect();

        if ($request->filled('formulario_id')) {
            $request->validate([
                'formulario_id' => ['required', 'exists:formularios,id'],
                'periodo_desde_id' => ['nullable', 'exists:periodos,id'],
                'periodo_hasta_id' => ['nullable', 'exists:periodos,id'],
            ]);

            $formulario = Formulario::find((int) $request->integer('formulario_id'));

            if ($formulario) {
                $periodos = Periodo::query()
                    ->where('formulario_id', $formulario->id)
                    ->orderBy('fecha_inicio')
                    ->get();

                $periodoDesde = $periodoDesde ?: (int) optional($periodos->first())->id;
                $periodoHasta = $periodoHasta ?: (int) optional($periodos->last())->id;

                if ($periodoDesde && $periodoHasta) {
                    if ($periodoDesde > $periodoHasta) {
                        [$periodoDesde, $periodoHasta] = [$periodoHasta, $periodoDesde];
                    }

                    $periodosEnRango = $periodos
                        ->filter(fn ($periodo) => $periodo->id >= $periodoDesde && $periodo->id <= $periodoHasta)
                        ->pluck('id');

                    $filas = $this->buildRowsOportunidad($periodosEnRango);
                }
            }
        }

        return view('reportes.oportunidad', [
            'formularios' => $formularios,
            'formulario' => $formulario,
            'periodos' => $periodos,
            'periodoDesde' => $periodoDesde,
            'periodoHasta' => $periodoHasta,
            'filas' => $filas,
        ]);
    }
    public function exportVariable(Request $request, Variable $variable)
    {
        $user = auth()->user();
        $scope = $user->alcances()->first();

        $scopeData = $this->resolveScope($scope?->tipo, (int) ($scope?->alcance_id));
        $establecimientosScope = $scopeData['establecimientos'];

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

        if (! $formulario || ! $formulario->variables->contains('id', $variable->id)) {
            abort(404);
        }

        $variable->load(['subvariables' => fn ($query) => $query->where('estado', 'activo'), 'cabeceras']);

        $periodos = Periodo::query()
            ->where('formulario_id', $formulario->id)
            ->orderBy('fecha_inicio')
            ->get();

        $periodoDesde = $request->integer('periodo_desde_id') ?: optional($periodos->first())->id;
        $periodoHasta = $request->integer('periodo_hasta_id') ?: optional($periodos->last())->id;

        if (! $periodoDesde || ! $periodoHasta) {
            abort(404);
        }

        $mostrarConsolidado = $request->boolean('mostrar_consolidado');

        $sinEstablecimientosSeleccionados = collect($request->input('establecimiento_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->isEmpty();

        $establecimientosSeleccionados = collect($request->input('establecimiento_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $periodosEnRango = $periodos
            ->filter(fn ($periodo) => $periodo->id >= $periodoDesde && $periodo->id <= $periodoHasta)
            ->pluck('id');

        if ($periodosEnRango->isEmpty()) {
            abort(404);
        }

        $establecimientosParaConsulta = $sinEstablecimientosSeleccionados
            ? $establecimientosDisponibles->pluck('id')
            : $establecimientosSeleccionados;

        $datos = Dato::query()
            ->whereIn('periodo_id', $periodosEnRango)
            ->whereIn('establecimiento_id', $establecimientosParaConsulta)
            ->whereIn('subvariable_id', $variable->subvariables->pluck('id'))
            ->whereIn('cabecera_id', $variable->cabeceras->pluck('id'))
            ->get();

        $row = $this->buildVariableRow(
            $variable,
            $datos,
            $establecimientosDisponibles,
            $establecimientosSeleccionados,
            $mostrarConsolidado,
            $sinEstablecimientosSeleccionados
        );

        $periodDesdeDescripcion = $periodos->firstWhere('id', $periodoDesde)?->descripcion ?? 'desde';
        $periodHastaDescripcion = $periodos->firstWhere('id', $periodoHasta)?->descripcion ?? 'hasta';

        $filename = strtoupper(str($formulario->sistema()->nombre) . '_' . str($variable->descripcion) . '_' . str($periodDesdeDescripcion) . '-' . str($periodHastaDescripcion)) . '.xlsx';

        return Excel::download(new ReporteVariableExport($row, $scope?->tipo), $filename);
    }

    private function buildRows(
        Formulario $formulario,
        Collection $datos,
        Collection $establecimientosDisponibles,
        Collection $establecimientosSeleccionados,
        bool $mostrarConsolidado,
        bool $sinEstablecimientosSeleccionados
    ): array
    {
        return $this->buildRowsFromVariables(
            $formulario->variables,
            $datos,
            $establecimientosDisponibles,
            $establecimientosSeleccionados,
            $mostrarConsolidado,
            $sinEstablecimientosSeleccionados
        );
    }
    private function buildRowsFromVariables(
        Collection $variables,
        Collection $datos,
        Collection $establecimientosDisponibles,
        Collection $establecimientosSeleccionados,
        bool $mostrarConsolidado,
        bool $sinEstablecimientosSeleccionados
    ): array
    {
        return $variables->map(function ($variable) use ($datos, $establecimientosDisponibles, $establecimientosSeleccionados, $mostrarConsolidado, $sinEstablecimientosSeleccionados) {
            return $this->buildVariableRow($variable, $datos, $establecimientosDisponibles, $establecimientosSeleccionados, $mostrarConsolidado, $sinEstablecimientosSeleccionados);
        })->toArray();
    }

    private function buildRowsOportunidad($periodosEnRango)
    {
        $establecimientos = Establecimiento::query()
            ->select([
                'establecimientos.id',
                'establecimientos.descripcion as establecimiento_nombre',
                'municipios.nombre as municipio_nombre',
                'reds.nombre as red_nombre',
            ])
            ->join('municipios', 'municipios.id', '=', 'establecimientos.id_mun')
            ->join('reds', 'reds.id', '=', 'municipios.red_id')
            ->orderBy('reds.nombre')
            ->orderBy('municipios.nombre')
            ->orderBy('establecimientos.descripcion')
            ->get();

        if ($periodosEnRango->isEmpty() || $establecimientos->isEmpty()) {
            return $establecimientos->map(function ($establecimiento) {
                $establecimiento->produccion = '';

                return $establecimiento;
            });
        }

        $establecimientosConDato = Dato::query()
            ->whereIn('periodo_id', $periodosEnRango)
            ->whereIn('establecimiento_id', $establecimientos->pluck('id'))
            ->distinct()
            ->pluck('establecimiento_id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        return $establecimientos->map(function ($establecimiento) use ($establecimientosConDato) {
            $establecimiento->produccion = $establecimientosConDato->has((int) $establecimiento->id) ? '0' : '';

            return $establecimiento;
        });
    }

    private function buildVariableRow(
        Variable $variable,
        Collection $datos,
        Collection $establecimientosDisponibles,
        Collection $establecimientosSeleccionados,
        bool $mostrarConsolidado,
        bool $sinEstablecimientosSeleccionados
    ): array
    {
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