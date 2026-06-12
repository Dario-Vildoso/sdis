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
    public function index(Request $request, string $view = 'reportes.index')
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

        $variableIdsSeleccionadas = collect($request->input('variable_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($scope?->tipo === 'red' && $redesSeleccionadas->isEmpty()) {
            $redesSeleccionadas = collect([(int) $scope->alcance_id]);
        }

        if ($redesSeleccionadas->isNotEmpty() && $municipiosSeleccionados->isEmpty()) {
            $municipiosSeleccionados = $municipiosDisponibles
                ->whereIn('red_id', $redesSeleccionadas)
                ->pluck('id')
                ->values();
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

        if ($municipiosSeleccionados->isNotEmpty() && collect($request->input('establecimiento_ids', []))->filter()->isEmpty()) {
            $establecimientosSeleccionados = $establecimientosDisponibles->pluck('id')->values();
        } else {
            $establecimientosSeleccionados = collect($request->input('establecimiento_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values();
        }

        $formulariosSeleccionados = collect();
        $periodosPorFormulario = collect();
        $periodoDesdePorFormulario = [];
        $periodoHastaPorFormulario = [];
        $variablesSeleccionadas = collect();
        $variablesDisponibles = collect();
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

            $variablesDisponibles = $formulariosSeleccionados
                ->flatMap->variables
                ->unique('id')
                ->values();

            $variablesSeleccionadas = $variablesDisponibles
                ->whereIn('id', $variableIdsSeleccionadas)
                ->values();
        }

        $mostrarConsolidado = $request->boolean(
            'mostrar_consolidado'
        );

        $sinEstablecimientosSeleccionados = $establecimientosSeleccionados->isEmpty();

        $rows = [];
        if ($formulariosSeleccionados->isNotEmpty() && $variablesSeleccionadas->isNotEmpty()) {
            $periodosEnRango = $formulariosSeleccionados
                ->flatMap(function ($formularioSeleccionado) use ($periodosPorFormulario, $periodoDesdePorFormulario, $periodoHastaPorFormulario) {
                    $formularioId = $formularioSeleccionado->id;
                    $periodosFormulario = $periodosPorFormulario->get($formularioId, collect());
                    $periodoDesde = (int) Periodo::find((int) ($periodoDesdePorFormulario[$formularioId] ?? 0))->numero;
                    $periodoHasta = (int) Periodo::find((int) ($periodoHastaPorFormulario[$formularioId] ?? 0))->numero;

                    if (! $periodoDesde || ! $periodoHasta) {
                        return collect();
                    }

                    if ($periodoDesde > $periodoHasta) {
                        [$periodoDesde, $periodoHasta] = [$periodoHasta, $periodoDesde];
                    }
                    
                    // dd((int) $periodosFormulario->pluck('numero')[0]);
                    // dd($periodoDesde, $periodoHasta);
                    // dd($periodosFormulario, $periodoDesde->numero, $periodoHasta->numero, (int) $periodosFormulario[0]->numero >= (int) $periodoDesde->numero);

                    return $periodosFormulario
                        ->filter(fn ($periodo) => (int) $periodo->numero >= $periodoDesde && (int) $periodo->numero <= $periodoHasta)
                        ->pluck('id');
                })
                ->unique()
                ->values();
            // dd($periodosEnRango);
            if ($periodosEnRango->isNotEmpty()) {
                $establecimientosParaConsulta = $sinEstablecimientosSeleccionados
                    ? $establecimientosDisponibles->pluck('id')
                    : $establecimientosSeleccionados;

                $datos = Dato::query()
                    ->whereIn('periodo_id', $periodosEnRango)
                    ->whereIn('establecimiento_id', $establecimientosParaConsulta)
                    ->whereIn('subvariable_id', $variablesSeleccionadas->flatMap->subvariables->pluck('id'))
                    ->whereIn('cabecera_id', $variablesSeleccionadas->flatMap->cabeceras->pluck('id'))
                    ->get();

                $rows = $this->buildRowsFromVariables(
                    $variablesSeleccionadas,
                    $datos,
                    $establecimientosDisponibles,
                    $establecimientosSeleccionados,
                    $mostrarConsolidado,
                    $sinEstablecimientosSeleccionados
                );
            }
        }


        return view($view, [
            'formularios' => $formularios,
            'formulario' => $formulario,
            'formulariosSeleccionados' => $formulariosSeleccionados,
            'formularioIdsSeleccionados' => $formulariosSeleccionados->pluck('id'),
            'periodosPorFormulario' => $periodosPorFormulario,
            'periodoDesdePorFormulario' => $periodoDesdePorFormulario,
            'periodoHastaPorFormulario' => $periodoHastaPorFormulario,
            'variablesDisponibles' => $variablesDisponibles,
            'variableIdsSeleccionadas' => $variableIdsSeleccionadas,
            'variablesSeleccionadas' => $variablesSeleccionadas,
            'hayVariablesDisponibles' => $variablesDisponibles->isNotEmpty(),
            'hayVariablesSeleccionadas' => $variablesSeleccionadas->isNotEmpty(),
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
    public function pivote()
    {
        $user = auth()->user();
        $scope = $user->alcances()->first();
        $scopeData = $this->resolveReportScope($user, $scope);
        $establecimientosScope = $scopeData['establecimientos'];

        $formularios = $this->formulariosAccesibles($user, $establecimientosScope)
            ->with(['sistema', 'gestion'])
            ->orderBy('id')
            ->get();

        $isGlobalScope = $this->isGlobalReportScope($user, $scope?->tipo);
        $showRedSelect = $isGlobalScope;
        $showMunicipioSelect = $isGlobalScope || $scope?->tipo === 'red';
        $showEstablecimientoSelect = $isGlobalScope || in_array($scope?->tipo, ['red', 'municipio']);
        $autoFormularioIds = $formularios->count() === 1 ? $formularios->pluck('id') : collect();

        $redesDisponibles = collect();
        $municipiosDisponibles = collect();
        $establecimientosDisponibles = collect();

        return view('reportes.pivote', [
            'formularios' => $formularios,
            'redesDisponibles' => $redesDisponibles,
            'municipiosDisponibles' => $municipiosDisponibles,
            'establecimientosDisponibles' => $establecimientosDisponibles,
            'scopeTipo' => $scope?->tipo,
            'scopeNombre' => $scopeData['nombre'],
            'showRedSelect' => $showRedSelect,
            'showMunicipioSelect' => $showMunicipioSelect,
            'showEstablecimientoSelect' => $showEstablecimientoSelect,
            'autoFormularioIds' => $autoFormularioIds,
            
        ]);
    }

    public function pivoteMetadata(Request $request)
    {
        $formularioIds = collect($request->input('formulario_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($formularioIds->isEmpty()) {
            return response()->json([
                'periodos' => [],
                'variables' => [],
            ]);
        }

        $user = auth()->user();
        $scope = $user->alcances()->first();
        $scopeData = $this->resolveReportScope($user, $scope);
        $formularios = $this->formulariosAccesibles($user, $scopeData['establecimientos'])
            ->whereIn('id', $formularioIds)
            ->with(['sistema', 'gestion'])
            ->get()
            ->sortBy(fn ($formulario) => $formularioIds->search($formulario->id))
            ->values();

        $formularioEstablecimientoIds = Establecimiento_Formulario::query()
            ->whereIn('formulario_id', $formularios->pluck('id'))
            ->pluck('establecimiento_id')
            ->map(fn ($id) => (int) $id)
            ->unique();

        $establecimientosFormularios = $scopeData['establecimientos']
            ->whereIn('id', $formularioEstablecimientoIds)
            ->values();

        $redes = $establecimientosFormularios
            ->groupBy('red_id')
            ->map(fn ($items) => [
                'id' => (int) $items->first()->red_id,
                'nombre' => $items->first()->red_nombre,
            ])
            ->values();

        $municipios = $establecimientosFormularios
            ->groupBy('id_mun')
            ->map(fn ($items) => [
                'id' => (int) $items->first()->id_mun,
                'nombre' => $items->first()->municipio_nombre,
                'red_id' => (int) $items->first()->red_id,
            ])
            ->values();

        $establecimientos = $establecimientosFormularios
            ->map(fn ($establecimiento) => [
                'id' => (int) $establecimiento->id,
                'nombre' => $establecimiento->descripcion,
                'nivel' => (int) $establecimiento->nivel,
                'municipio_id' => (int) $establecimiento->id_mun,
                'municipio_nombre' => $establecimiento->municipio_nombre,
                'red_id' => (int) $establecimiento->red_id,
                'red_nombre' => $establecimiento->red_nombre,
            ])
            ->values();

        $periodos = Periodo::query()
            ->with(['formulario.gestion'])
            ->whereIn('formulario_id', $formularios->pluck('id'))
            ->get()
            ->sortBy(fn ($periodo) => sprintf('%06d-%010d', (int) $periodo->numero, (int) $periodo->id))
            ->values()
            ->map(fn ($periodo) => [
                'id' => (int) $periodo->id,
                'formulario_id' => (int) $periodo->formulario_id,
                'descripcion' => $periodo->descripcion,
                'numero' => (string) $periodo->numero,
                'fecha_inicio' => optional($periodo->fecha_inicio)->format('Y-m-d'),
                'fecha_fin' => optional($periodo->fecha_fin)->format('Y-m-d'),
            ]);

        $variables = $this->variablesParaFormularios($formularios->pluck('id'));

        return response()->json([
            'periodos' => $periodos,
            'variables' => $variables,
            'redes' => $redes,
            'municipios' => $municipios,
            'establecimientos' => $establecimientos,
        ]);
    }

    public function pivoteDatos(Request $request)
    {
        $user = auth()->user();
        $scope = $user->alcances()->first();
        $scopeData = $this->resolveReportScope($user, $scope);
        $establecimientosScope = $scopeData['establecimientos'];

        $formularioIds = collect($request->input('formulario_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($formularioIds->isEmpty()) {
            return response()->json(['message' => 'Selecciona al menos un formulario.'], 422);
        }

        $formularios = $this->formulariosAccesibles($user, $establecimientosScope)
            ->whereIn('id', $formularioIds)
            ->with(['sistema', 'gestion'])
            ->get();

        if ($formularios->isEmpty()) {
            return response()->json(['message' => 'No se encontraron formularios válidos.'], 422);
        }

        $periodoDesde = Periodo::query()
            ->whereIn('formulario_id', $formularios->pluck('id'))
            ->find((int) $request->input('periodo_desde_id'));
        $periodoHasta = Periodo::query()
            ->whereIn('formulario_id', $formularios->pluck('id'))
            ->find((int) $request->input('periodo_hasta_id'));

        if (! $periodoDesde || ! $periodoHasta) {
            return response()->json(['message' => 'Selecciona los periodos desde y hasta.'], 422);
        }

        $numeroDesde = (int) $periodoDesde->numero;
        $numeroHasta = (int) $periodoHasta->numero;

        if ($numeroDesde > $numeroHasta) {
            [$numeroDesde, $numeroHasta] = [$numeroHasta, $numeroDesde];
        }

        $periodosEnRango = Periodo::query()
            ->whereIn('formulario_id', $formularios->pluck('id'))
            ->get()
            ->filter(fn ($periodo) => (int) $periodo->numero >= $numeroDesde && (int) $periodo->numero <= $numeroHasta)
            ->sortBy(fn ($periodo) => sprintf('%06d-%010d', (int) $periodo->numero, (int) $periodo->id))
            ->values();
        $periodoIdsEnRango = $periodosEnRango->pluck('id');

        if ($periodosEnRango->isEmpty()) {
            return response()->json(['message' => 'No hay periodos dentro del rango seleccionado.'], 422);
        }

        $variableIds = collect($request->input('variable_ids', []))->map(fn ($id) => (int) $id)->filter()->unique()->values();

        if ($variableIds->isEmpty()) {
            return response()->json(['message' => 'Selecciona una o más variables para generar el reporte.'], 422);
        }

        $redIds = collect($request->input('red_ids', []))->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $municipioIds = collect($request->input('municipio_ids', []))->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $establecimientoIds = collect($request->input('establecimiento_ids', []))->map(fn ($id) => (int) $id)->filter()->unique()->values();

        $formularioEstablecimientoIds = Establecimiento_Formulario::query()
            ->whereIn('formulario_id', $formularioIds)
            ->pluck('establecimiento_id')
            ->map(fn ($id) => (int) $id)
            ->unique();

        $establecimientosFiltrados = $establecimientosScope
            ->whereIn('id', $formularioEstablecimientoIds)
            ->when($redIds->isNotEmpty(), fn ($col) => $col->whereIn('red_id', $redIds))
            ->when($municipioIds->isNotEmpty(), fn ($col) => $col->whereIn('id_mun', $municipioIds))
            ->values();

        if ($establecimientoIds->isNotEmpty()) {
            $establecimientosFiltrados = $establecimientosFiltrados->whereIn('id', $establecimientoIds)->values();
        }

        if ($establecimientosFiltrados->isEmpty()) {
            return response()->json(['message' => 'No hay establecimientos para los filtros seleccionados.'], 422);
        }

        $variables = $this->variablesParaFormularios($formularios->pluck('id'))
            ->whereIn('id', $variableIds)
            ->values();

        if ($variables->isEmpty()) {
            return response()->json(['message' => 'Las variables seleccionadas no pertenecen a los formularios elegidos.'], 422);
        }

        $subvariableIds = collect($variables)->flatMap(fn ($variable) => collect($variable['subvariables'])->pluck('id'))->unique()->values();
        $cabeceraIds = collect($variables)->flatMap(fn ($variable) => collect($variable['cabeceras_leaf'])->pluck('id'))->unique()->values();

        $datosAgrupados = Dato::query()
            ->whereIn('periodo_id', $periodoIdsEnRango)
            ->whereIn('establecimiento_id', $establecimientosFiltrados->pluck('id'))
            ->whereIn('subvariable_id', $subvariableIds)
            ->whereIn('cabecera_id', $cabeceraIds)
            ->get()
            ->groupBy(fn ($dato) => implode('-', [$dato->periodo_id, $dato->establecimiento_id, $dato->subvariable_id, $dato->cabecera_id]));

        $periodosPayload = $periodosEnRango->map(fn ($periodo) => [
            'id' => (int) $periodo->id,
            'descripcion' => $periodo->descripcion,
            'numero' => (string) $periodo->numero,
            'label' => $periodo->numero . '. ' . $periodo->descripcion,
        ])->values();

        $tablas = collect($variables)->map(function ($variable) use ($establecimientosFiltrados, $datosAgrupados, $periodosPayload) {
            $leafCabeceras = collect($variable['cabeceras_leaf']);
            $filas = collect($variable['subvariables'])->flatMap(function ($subvariable) use ($establecimientosFiltrados, $datosAgrupados, $leafCabeceras, $periodosPayload) {
                return $establecimientosFiltrados->map(function ($establecimiento) use ($subvariable, $datosAgrupados, $leafCabeceras, $periodosPayload) {
                    $valores = $periodosPayload->mapWithKeys(function ($periodo) use ($establecimiento, $subvariable, $datosAgrupados, $leafCabeceras) {
                        $valoresPeriodo = $leafCabeceras->mapWithKeys(function ($cabecera) use ($periodo, $establecimiento, $subvariable, $datosAgrupados) {
                            $key = implode('-', [$periodo['id'], $establecimiento->id, $subvariable['id'], $cabecera['id']]);

                            return [$cabecera['id'] => (float) $datosAgrupados->get($key, collect())->sum('valor')];
                        });

                        return [$periodo['id'] => $valoresPeriodo];
                    });

                    return [
                        'subvariable' => $subvariable,
                        'establecimiento' => [
                            'id' => (int) $establecimiento->id,
                            'descripcion' => $establecimiento->descripcion,
                            'municipio_nombre' => $establecimiento->municipio_nombre,
                            'red_nombre' => $establecimiento->red_nombre,
                        ],
                        'valores' => $valores,
                        'total' => $valores->flatMap(fn ($periodoValores) => $periodoValores)->sum(),
                    ];
                });
            })->values();

            return [
                'variable' => [
                    'id' => $variable['id'],
                    'descripcion' => $variable['descripcion'],
                ],
                'periodos' => $periodosPayload,
                'cabeceras' => $variable['cabeceras'],
                'cabeceras_leaf' => $variable['cabeceras_leaf'],
                'filas' => $filas,
            ];
        })->values();

        return response()->json([
            'tables' => $tablas,
            'variables' => $variables,
            'establecimientos_count' => $establecimientosFiltrados->count(),
            'periodos_count' => $periodosEnRango->count(),
        ]);
    }

    public function tabulator(Request $request)
    {
        return $this->index($request, 'reportes.tabulator');
    }

    public function oportunidad(Request $request)
    {
        $user = auth()->user();
        $formularios = Formulario::query()->where('sistema_id', $user->sistema_id)->orderBy('id')->get();

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

        $filename = strtoupper(str($formulario->sistema->nombre) . '_' . str($variable->descripcion) . '_' . str($periodDesdeDescripcion) . '-' . str($periodHastaDescripcion)) . '.xlsx';

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

        $datosPorEstablecimiento = Dato::query()
            ->whereIn('periodo_id', $periodosEnRango)
            ->whereIn('establecimiento_id', $establecimientos->pluck('id'))
            ->get()
            ->groupBy('establecimiento_id');

        return $establecimientos->map(function ($establecimiento) use ($datosPorEstablecimiento) {
            $datosEstablecimiento = $datosPorEstablecimiento->get((int) $establecimiento->id, collect());

            if ($datosEstablecimiento->isEmpty()) {
                $establecimiento->produccion = '';
                $establecimiento->fecha = '';
                return $establecimiento;
            }

            $tieneDatosConProduccion = $datosEstablecimiento->contains(fn ($dato) => (float) $dato->valor !== 0.0);
            $establecimiento->produccion = $tieneDatosConProduccion ? 'con produccion' : 'sin produccion';
            $establecimiento->fecha = $datosEstablecimiento
                ->pluck('fecha')
                ->filter()
                ->max() ?? '';

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

private function resolveReportScope($user, $scope): array
    {
        if ($this->isGlobalReportScope($user, $scope?->tipo)) {
            return $this->resolveScope('sedes', 0);
        }

        return $this->resolveScope($scope?->tipo, (int) ($scope?->alcance_id));
    }

    private function formulariosAccesibles($user, Collection $establecimientosScope)
    {
        return Formulario::query()
            ->where('sistema_id', $user->sistema_id)
            ->whereHas('establecimientos', fn ($query) => $query->whereIn('establecimientos.id', $establecimientosScope->pluck('id')));
    }

    private function isGlobalReportScope($user, ?string $scopeTipo): bool
    {
        return $scopeTipo === 'sedes' || $user->hasAnyRole(['admin', 'super-admin']);
    }


    private function variablesParaFormularios(Collection $formularioIds): Collection
    {
        return Variable::query()
            ->whereHas('formularios', fn ($query) => $query->whereIn('formularios.id', $formularioIds))
            ->with([
                'subvariables' => fn ($query) => $query->where('estado', 'activo')->orderBy('id'),
                'cabeceras' => fn ($query) => $query->orderBy('id'),
            ])
            ->get()
            ->map(function ($variable) {
                return [
                    'id' => (int) $variable->id,
                    'descripcion' => $variable->descripcion,
                    'subvariables' => $variable->subvariables->map(fn ($subvariable) => [
                        'id' => (int) $subvariable->id,
                        'descripcion' => $subvariable->descripcion,
                    ])->values(),
                    'cabeceras' => $variable->cabeceras->sortBy('id')->map(fn ($cabecera) => [
                        'id' => (int) $cabecera->id,
                        'descripcion' => $cabecera->descripcion,
                        'cabecera_id' => $cabecera->cabecera_id ? (int) $cabecera->cabecera_id : null,
                    ])->values(),
                    'cabeceras_leaf' => $this->orderedLeafCabeceras($variable->cabeceras)->map(function ($cabecera) use ($variable) {
                        $niveles = $this->cabeceraPath($cabecera, $variable->cabeceras);

                        return [
                            'id' => (int) $cabecera->id,
                            'descripcion' => implode(' / ', $niveles),
                            'niveles' => $niveles,
                        ];
                    })->values(),
                ];
            })
            ->values();
    }

    private function orderedLeafCabeceras(Collection $cabeceras): Collection
    {
        $childrenByParent = $cabeceras->sortBy('id')->groupBy(fn ($cabecera) => $cabecera->cabecera_id ? (string) $cabecera->cabecera_id : 'root');
        $leafCabeceras = collect();
        $walk = function (Collection $items) use (&$walk, $childrenByParent, &$leafCabeceras) {
            foreach ($items->sortBy('id') as $cabecera) {
                $children = $childrenByParent->get((string) $cabecera->id, collect());

                if ($children->isEmpty()) {
                    $leafCabeceras->push($cabecera);
                    continue;
                }

                $walk($children);
            }
        };

        $walk($childrenByParent->get('root', collect()));

        return $leafCabeceras->values();
    }

    private function cabeceraPath($cabecera, Collection $cabeceras): array
    {
        $cabecerasPorId = $cabeceras->keyBy('id');
        $path = [];
        $actual = $cabecera;

        while ($actual) {
            array_unshift($path, $actual->descripcion);
            $actual = $actual->cabecera_id ? $cabecerasPorId->get($actual->cabecera_id) : null;
        }

        return $path;
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