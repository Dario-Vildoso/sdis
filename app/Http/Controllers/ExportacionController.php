<?php

namespace App\Http\Controllers;

use App\Models\Dato;
use App\Models\Establecimiento;
use App\Models\Formulario;
use App\Models\Municipio;
use App\Models\Periodo;
use App\Models\Red;
use App\Models\Gestion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportacionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scope = $user->alcances()->first();

        $scopeData = $this->resolveScope($scope?->tipo, (int) ($scope?->alcance_id));
        $establecimientosScope = $scopeData['establecimientos'];
        $formularios = Formulario::query()
            ->where('sistema_id', $user->sistema_id)
            ->whereHas('establecimientos', function ($q) use ($establecimientosScope) {
                $q->whereIn(
                    'establecimientos.id',
                    $establecimientosScope->pluck('id')
                );
            })
            ->get();

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

        $establecimientosDisponibles = $establecimientosScope
            ->map(fn ($establecimiento) => [
                'id' => (int) $establecimiento->id,
                'nombre' => $establecimiento->descripcion,
                'nivel' => (int) $establecimiento->nivel,
                'municipio_id' => (int) $establecimiento->id_mun,
                'red_id' => (int) $establecimiento->red_id,
            ])
            ->values();

        $periodos = collect();
        $formulario = null;

        if ($request->filled('formulario_id')) {
            $formulario = Formulario::find($request->integer('formulario_id'));

            if ($formulario) {
                $periodos = Periodo::query()
                    ->where('formulario_id', $formulario->id)
                    ->orderBy('numero')
                    ->get();
            }
        }

        return view('exportaciones.index', [
            'formularios' => $formularios,
            'periodos' => $periodos,
            'formulario' => $formulario,
            'redesDisponibles' => $redesDisponibles,
            'municipiosDisponibles' => $municipiosDisponibles,
            'establecimientosDisponibles' => $establecimientosDisponibles,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'formulario_id' => ['required', 'exists:formularios,id'],
            'periodo_desde_id' => ['required', 'exists:periodos,id'],
            'periodo_hasta_id' => ['required', 'exists:periodos,id'],
            'red_ids' => ['nullable', 'array'],
            'red_ids.*' => ['integer', 'exists:reds,id'],
            'municipio_ids' => ['nullable', 'array'],
            'municipio_ids.*' => ['integer', 'exists:municipios,id'],
            'establecimiento_ids' => ['nullable', 'array'],
            'establecimiento_ids.*' => ['integer', 'exists:establecimientos,id'],
        ]);

        $selecciones = [
            'red' => collect($validated['red_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values(),
            'municipio' => collect($validated['municipio_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values(),
            'establecimiento' => collect($validated['establecimiento_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values(),
        ];

        $tiposConSeleccion = collect($selecciones)
            ->filter(fn (Collection $ids) => $ids->isNotEmpty())
            ->keys();

        if ($tiposConSeleccion->count() > 1) {
            return back()
                ->withInput()
                ->withErrors(['scope_selection' => 'Debes seleccionar elementos de un solo tipo: redes, municipios o establecimientos.']);
        }

        $formulario = Formulario::with([
            'variables' => function ($query) {
                $query->with([
                    'subvariables' => fn ($subQuery) => $subQuery->where('estado', 'activo'),
                    'cabeceras',
                ]);
            },
        ])->findOrFail((int) $validated['formulario_id']);

        $periodos = Periodo::query()
            ->where('formulario_id', $formulario->id)
            ->orderBy('fecha_inicio')
            ->get();
        $periodoDesdeId = (int) $validated['periodo_desde_id'];
        $periodoHastaId = (int) $validated['periodo_hasta_id'];
        $periodoDesde = (int) Periodo::findOrFail($periodoDesdeId)->numero;
        $periodoHasta = (int) Periodo::findOrFail($periodoHastaId)->numero;

        if ($periodoDesde > $periodoHasta) {
            [$periodoDesde, $periodoHasta] = [$periodoHasta, $periodoDesde];
        }

        $periodosEnRango = $periodos
            ->filter(fn ($periodo) => (int) $periodo->numero >= $periodoDesde && (int) $periodo->numero <= $periodoHasta)
            ->pluck('id');

        if ($periodosEnRango->isEmpty()) {
            return back()->withInput()->withErrors(['periodo_desde_id' => 'No hay periodos válidos en el rango seleccionado para el formulario.']);
        }

        // Limitar los establecimientos a los que el usuario tiene acceso según su alcance
        $user = auth()->user();
        $scope = $user->alcances()->first();
        $scopeData = $this->resolveScope($scope?->tipo, (int) ($scope?->alcance_id));
        $establecimientosBase = $scopeData['establecimientos'];

        // Construir la lista final de establecimientos seleccionados como la unión
        // de las selecciones explícitas (establecimientos, municipios, redes).
        $establecimientosSeleccionados = collect();

        if ($selecciones['establecimiento']->isNotEmpty()) {
            $establecimientosSeleccionados = $establecimientosSeleccionados->merge(
                $establecimientosBase->whereIn('id', $selecciones['establecimiento'])
            );
        }

        if ($selecciones['municipio']->isNotEmpty()) {
            $establecimientosSeleccionados = $establecimientosSeleccionados->merge(
                $establecimientosBase->whereIn('id_mun', $selecciones['municipio'])
            );
        }

        if ($selecciones['red']->isNotEmpty()) {
            $establecimientosSeleccionados = $establecimientosSeleccionados->merge(
                $establecimientosBase->whereIn('red_id', $selecciones['red'])
            );
        }

        $establecimientosSeleccionados = $establecimientosSeleccionados->unique('id')->values();

        // Si no se eligió nada, usar todos los establecimientos a los que tiene acceso el usuario
        if ($establecimientosSeleccionados->isEmpty()) {
            $establecimientosSeleccionados = $establecimientosBase;
        }

        $datos = Dato::query()
            ->whereIn('periodo_id', $periodosEnRango)
            ->whereIn('establecimiento_id', $establecimientosSeleccionados->pluck('id'))
            ->whereIn('subvariable_id', $formulario->variables->flatMap->subvariables->pluck('id'))
            ->whereIn('cabecera_id', $formulario->variables->flatMap->cabeceras->pluck('id'))
            ->get();

        $rows = $this->buildRows($formulario, $datos, $establecimientosSeleccionados);

        $resumenSeleccion = [
            'redes' => $establecimientosSeleccionados->pluck('red_nombre')->filter()->unique()->values(),
            'municipios' => $establecimientosSeleccionados->pluck('municipio_nombre')->filter()->unique()->values(),
            'establecimientos' => $establecimientosSeleccionados->pluck('descripcion')->filter()->unique()->values(),
        ];

        $gestionNombre = optional(Gestion::find($formulario->gestion_id))->nombre;


        $periodDesdeDescripcion = $periodos->firstWhere('id', $periodoDesdeId)?->descripcion ?? 'desde';
        $periodHastaDescripcion = $periodos->firstWhere('id', $periodoHastaId)?->descripcion ?? 'hasta';

        $filename = strtoupper(str($formulario->sistema->nombre) . '-' . $formulario->gestion->nombre . '_' . $periodDesdeDescripcion . '-' . $periodHastaDescripcion) . '.xls';

        return response()->streamDownload(function () use ($rows, $formulario, $periodos, $periodoDesdeId, $periodoHastaId, $resumenSeleccion, $gestionNombre) {
            echo view('exportaciones.excel', [
                'rows' => $rows,
                'formulario' => $formulario,
                'periodos' => $periodos,
                'periodoDesde' => $periodoDesdeId,
                'periodoHasta' => $periodoHastaId,
                'resumenSeleccion' => $resumenSeleccion,
                'gestionNombre' => $gestionNombre,
            ])->render();
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function buildRows(Formulario $formulario, Collection $datos, Collection $establecimientosSeleccionados): array
    {
        $datosAgrupados = $datos->groupBy(fn ($dato) => implode('-', [
            $dato->establecimiento_id,
            $dato->subvariable_id,
            $dato->cabecera_id,
        ]));

        return $formulario->variables->map(function ($variable) use ($establecimientosSeleccionados, $datosAgrupados) {
            $childrenByParent = $variable->cabeceras->groupBy('cabecera_id');
            $rootCabeceras = $childrenByParent->get(null, collect())->sortBy('id')->values();

            // Calcular cabeceras hoja en orden jerárquico
            $cabecerasHoja = collect();
            $walk = function (Collection $items) use (&$walk, $childrenByParent, &$cabecerasHoja) {
                foreach ($items->sortBy('id') as $cabecera) {
                    $children = $childrenByParent->get($cabecera->id, collect());
                    if ($children->isEmpty()) {
                        $cabecerasHoja->push($cabecera);
                        continue;
                    }
                    $walk($children);
                }
            };
            $walk($rootCabeceras);

            $subvariables = $variable->subvariables->map(function ($subvariable) use ($establecimientosSeleccionados, $datosAgrupados, $cabecerasHoja) {
                $totales = $cabecerasHoja->mapWithKeys(function ($cabecera) use ($establecimientosSeleccionados, $subvariable, $datosAgrupados) {
                    $valor = $establecimientosSeleccionados->sum(function ($establecimiento) use ($subvariable, $datosAgrupados, $cabecera) {
                        $key = implode('-', [$establecimiento->id, $subvariable->id, $cabecera->id]);

                        return (float) $datosAgrupados->get($key, collect())->sum('valor');
                    });

                    return [$cabecera->id => $valor];
                });

                return [
                    'subvariable' => $subvariable,
                    'totales' => $totales,
                ];
            });

            return [
                'variable' => $variable,
                'cabeceras_todas' => $variable->cabeceras->sortBy('id')->values(),
                'cabeceras_hoja' => $cabecerasHoja,
                'subvariables' => $subvariables,
            ];
        })->toArray();
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
