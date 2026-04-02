<?php

namespace App\Http\Controllers;

use App\Models\Variable;
use App\Models\SubVariable;
use App\Models\Cabecera;
use App\Models\Excepcion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class VariableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $variables = Variable::all();
        return view('variables.index', compact('variables'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate([
            'descripcion' => 'required|string|max:255',
        ]);
        $variable = new Variable();
        $variable->descripcion = $request->descripcion;
        $variable->created_at = Carbon::now();
        $variable->updated_at = Carbon::now();
        $variable->save();
        $subvariable = new SubVariable();
        $subvariable->descripcion = 'Nueva subvariable';
        $subvariable->variable_id = $variable->id;
        $subvariable->estado = 'activo';
        $subvariable->save();
        $cabecera = new Cabecera();
        $cabecera->descripcion = 'Nueva cabecera';
        $cabecera->variable_id = $variable->id;
        $cabecera->cabecera_id = null;
        $cabecera->estado = 'activo';
        $cabecera->save();
        return redirect()->route('variables.index')->with('success', 'Variable creada exitosamente.');
    }

    public function tabla(Variable $variable)
    {
        $variable = Variable::with(['cabeceras' => function ($query) {
            $query->whereNull('cabecera_id')->with('hijos');
        }, 'subvariables'])->findOrFail($variable->id);
        $variable->total_leaf_columns = $variable->cabeceras->sum(
            fn($c) => $this->countLeaves($c)
        );
        $excepciones = Excepcion::whereIn('subvariable_id', $variable->subvariables->pluck('id'))
            ->get(['subvariable_id', 'cabecera_id']);
        return view('variables.tabla', compact('variable', 'excepciones'));
    }

    /**
     * Count leaf nodes in a cabecera tree
     */
    private function countLeaves($cabecera)
    {
        if ($cabecera->hijos->isEmpty()) {
            return 1;
        }
        return $cabecera->hijos->sum(fn($h) => $this->countLeaves($h));
    }

    /**
     * Display the specified resource.
     */
    public function show(Variable $variable)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Variable $variable)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        request()->validate([
            'descripcion' => 'required|string|max:255',
        ]);
        $variable = Variable::find($id);
        $variable->descripcion = $request->descripcion;
        $variable->updated_at = Carbon::now();
        $variable->save();
        return redirect()->route('variables.index')->with('success', 'Variable actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Variable $variable)
    {
        $variable->delete();
        return redirect()->route('variables.index')->with('success', 'Variable eliminada exitosamente.');
    }

    /**
     * Get subvariables for a variable (AJAX)
     */
    public function getSubvariables(Variable $variable): JsonResponse
    {
        $subvariables = SubVariable::where('variable_id', $variable->id)->get();
        return response()->json(['subvariables' => $subvariables]);
    }

    /**
     * Get cabeceras for a variable (AJAX)
     */
    public function getCabeceras(Variable $variable): JsonResponse
    {
        $cabeceras = Cabecera::where('variable_id', $variable->id)->get();
        return response()->json(['cabeceras' => $cabeceras]);
    }

    /**
     * Guardar cambios realizados en la tabla de variable
     */
    public function guardarTabla(Request $request, Variable $variable)
    {
        try {
            $data = $request->json()->all();
            // Log payload received for debugging
            Log::debug('guardarTabla payload', $data ?: ['raw' => $request->getContent()]);

            $mapSubvariables = [];
            $mapCabeceras = [];

            // Actualizar nombre de la variable
            if (isset($data['variable']['descripcion'])) {
                $variable->update(['descripcion' => $data['variable']['descripcion']]);
            }
            // Borrar subvariables marcadas
            if (isset($data['deleted_subvariables']) && is_array($data['deleted_subvariables'])) {
                SubVariable::whereIn('id', $data['deleted_subvariables'])->delete();
            }

            if (isset($data['deleted_cabeceras']) && is_array($data['deleted_cabeceras'])) {
                Cabecera::whereIn('id', $data['deleted_cabeceras'])->delete();
            }
            // Guardar cambios de subvariables
            if (isset($data['subvariables'])) {
                foreach ($data['subvariables'] as $subvarData) {
                    $estado = $subvarData['estado'] ?? 'activo';
                    if (is_numeric($subvarData['id'])) {
                        // Actualizar subvariable existente
                        SubVariable::where('id', $subvarData['id'])
                            ->update([
                                'descripcion' => $subvarData['descripcion'],
                                'estado' => $estado,
                            ]);

                        $mapSubvariables[(string) $subvarData['id']] = (int) $subvarData['id'];
                        } else if (strpos($subvarData['id'], 'new-') === 0) {
                            // Crear nueva subvariable
                            $newSubvariable = SubVariable::create([
                                'variable_id' => $variable->id,
                                'descripcion' => $subvarData['descripcion'],
                                'estado' => $estado,
                            ]);
                            $mapSubvariables[(string) $subvarData['id']] = (int) $newSubvariable->id;
                    }
                }
            }

            // Guardar cambios de cabeceras
            if (isset($data['cabeceras'])) {
                foreach ($data['cabeceras'] as $cabData) {
                    if (is_numeric($cabData['id'])) {
                        Cabecera::where('id', $cabData['id'])
                            ->update([
                                'descripcion' => $cabData['descripcion'],
                                'cabecera_id' => $cabData['cabecera_id'] ?? null,
                            ]);
                        $mapCabeceras[(string) $cabData['id']] = (int) $cabData['id'];
                    } elseif (strpos($cabData['id'], 'new-') === 0) {
                        // crear cabecera nueva al nivel más profundo (sin padre)
                        $newCabecera = Cabecera::create([
                            'variable_id' => $variable->id,
                            'descripcion' => $cabData['descripcion'],
                            'cabecera_id' => null,
                        ]);
                        $mapCabeceras[(string) $cabData['id']] = (int) $newCabecera->id;
                    }
                }
            }
            
            if (isset($data['cabeceras_new'])) {
                foreach ($data['cabeceras_new'] as $cabData) {
                    $parentKey = isset($cabData['cabecera_id']) ? (string) $cabData['cabecera_id'] : null;
                    $parentId = null;
                    if ($parentKey !== null) {
                        if (is_numeric($parentKey)) {
                            $parentId = (int) $parentKey;
                        } elseif (isset($mapCabeceras[$parentKey])) {
                            $parentId = $mapCabeceras[$parentKey];
                        }
                    }

                    $cabnew = new Cabecera();
                    $cabnew->descripcion = $cabData['descripcion'];
                    $cabnew->variable_id = $variable->id;
                    $cabnew->cabecera_id = $parentId;
                    $cabnew->estado = 'activo';
                    $cabnew->save();

                    if (isset($cabData['id'])) {
                        $mapCabeceras[(string) $cabData['id']] = (int) $cabnew->id;
                    }
                }
            }

            if (isset($data['excepciones']) && is_array($data['excepciones'])) {
                $subvariableIds = SubVariable::where('variable_id', $variable->id)->pluck('id');
                Excepcion::whereIn('subvariable_id', $subvariableIds)->delete();

                foreach ($data['excepciones'] as $excepcion) {
                    $subvariableKey = isset($excepcion['subvariable_id']) ? (string) $excepcion['subvariable_id'] : null;
                    $cabeceraKey = isset($excepcion['cabecera_id']) ? (string) $excepcion['cabecera_id'] : null;

                    $resolvedSubvariableId = null;
                    if ($subvariableKey !== null) {
                        if (is_numeric($subvariableKey)) {
                            $resolvedSubvariableId = (int) $subvariableKey;
                        } elseif (isset($mapSubvariables[$subvariableKey])) {
                            $resolvedSubvariableId = $mapSubvariables[$subvariableKey];
                        }
                    }

                    $resolvedCabeceraId = null;
                    if ($cabeceraKey !== null) {
                        if (is_numeric($cabeceraKey)) {
                            $resolvedCabeceraId = (int) $cabeceraKey;
                        } elseif (isset($mapCabeceras[$cabeceraKey])) {
                            $resolvedCabeceraId = $mapCabeceras[$cabeceraKey];
                        }
                    }
                    
                    if ($resolvedSubvariableId && $resolvedCabeceraId) {
                        Excepcion::create([
                            'subvariable_id' => $resolvedSubvariableId,
                            'cabecera_id' => $resolvedCabeceraId,
                        ]);
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Cambios guardados exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}