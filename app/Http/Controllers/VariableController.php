<?php

namespace App\Http\Controllers;

use App\Models\Variable;
use App\Models\SubVariable;
use App\Models\Cabecera;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;

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
        return redirect()->route('variables.index')->with('success', 'Variable creada exitosamente.');
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
}
