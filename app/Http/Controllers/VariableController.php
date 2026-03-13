<?php

namespace App\Http\Controllers;

use App\Models\Variable;
use App\Models\SubVariable;
use App\Models\Cabecera;
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
        $subvariable->descripcion = $request->descripcion;
        $subvariable->variable_id = $variable->id;
        $subvariable->estado = 'activo';
        $subvariable->save();
        $cabecera = new Cabecera();
        $cabecera->descripcion = $request->descripcion;
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
        }])->findOrFail($variable->id);
        $variable->total_leaf_columns = $variable->cabeceras->sum(
            fn($c) => $this->countLeaves($c)
        );
        return view('variables.tabla', compact('variable'));
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
                    } else if (strpos($subvarData['id'], 'new-') === 0) {
                        // Crear nueva subvariable
                        SubVariable::create([
                            'variable_id' => $variable->id,
                            'descripcion' => $subvarData['descripcion'],
                            'estado' => $estado,
                        ]);
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
                    } elseif (strpos($cabData['id'], 'new-') === 0) {
                        // crear cabecera nueva al nivel más profundo (sin padre)
                        Cabecera::create([
                            'variable_id' => $variable->id,
                            'descripcion' => $cabData['descripcion'],
                            'cabecera_id' => null,
                        ]);
                    }
                }
            }
            
            if (isset($data['cabeceras_new'])) {
                $mapIds = [];
                foreach ($data['cabeceras_new'] as $cabData) {
                    if (is_numeric($cabData['cabecera_id'])) {
                        $cabnew = new Cabecera();
                        $cabnew->descripcion = $cabData['descripcion'];
                        $cabnew->variable_id = $variable->id;
                        $cabnew->cabecera_id = $cabData['cabecera_id'];
                        $cabnew->estado = 'activo';
                        $cabnew->save();
                        if (isset($cabData['id'])) {
                            $mapIds[$cabData['id']] = $cabnew->id;
                        }
                    } elseif (strpos($cabData['cabecera_id'], 'new-') === 0) {
                        $cabnew = new Cabecera();
                        $cabnew->descripcion = $cabData['descripcion'];
                        $cabnew->variable_id = $variable->id;
                        $cabnew->cabecera_id = $mapIds[$cabData['cabecera_id']] ?? $cabData['cabecera_id'];
                        $cabnew->estado = 'activo';
                        $cabnew->save();
                        if (isset($cabData['id'])) {
                            $mapIds[$cabData['id']] = $cabnew->id;
                        }
                    } else {
                        $cabnew = new Cabecera();
                        $cabnew->descripcion = $cabData['descripcion'];
                        $cabnew->variable_id = $variable->id;
                        $cabnew->cabecera_id = null;
                        $cabnew->estado = 'activo';
                        $cabnew->save();
                        if (isset($cabData['id'])) {
                            $mapIds[$cabData['id']] = $cabnew->id;
                        }
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Cambios guardados exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}