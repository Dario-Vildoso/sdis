<?php

namespace App\Http\Controllers;

use App\Models\Formulario;
use App\Models\Gestion;
use App\Models\Asignacion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FormularioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formularios = Formulario::all();
        $gestiones = Gestion::all();
        return view('formularios.index', compact('formularios', 'gestiones'));
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
            'nombre' => 'required|string|max:255',
            'gestion_id' => 'required|exists:gestions,id',
        ]);
        $formulario = new Formulario();
        $formulario->nombre = $request->nombre;
        $formulario->gestion_id = $request->gestion_id;
        $formulario->created_at = Carbon::now();
        $formulario->updated_at = Carbon::now();
        $formulario->save();
        return redirect()->route('formularios.index')->with('success', 'Formulario creado exitosamente.');
    }

    public function asignarVariables(Formulario $formulario)
    {
        $formulario = Formulario::with(['variables.subvariables', 'variables.cabeceras' => function ($query) {
            $query->whereNull('cabecera_id')->with('hijos');
        }])->findOrFail($formulario->id);
        $formulario->variables->each(function ($variable) {
            $variable->total_leaf_columns = $variable->cabeceras->sum(
                fn($c) => $this->countLeaves($c)
            );
        });
        return view('formularios.assignments', compact('formulario'));
    }

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
    public function show(Formulario $formulario)
    {
        $formulario = Formulario::with(['variables.subvariables', 'variables.cabeceras' => function ($query) {
            $query->whereNull('cabecera_id')->with('hijos');
        }])->findOrFail($formulario->id);
        $formulario->variables->each(function ($variable) {
            $variable->total_leaf_columns = $variable->cabeceras->sum(
                fn($c) => $this->countLeaves($c)
            );
        });
        return view('formularios.show', compact('formulario'));
    }

    /** 
     * Show the form for editing the specified resource.
     */
    public function edit(Formulario $formulario)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $formulario = Formulario::findOrFail($id);
        request()->validate([
            'nombre' => 'required|string|max:255' . $formulario->id,
            'gestion_id' => 'required|exists:gestions,id',
        ]);
        $formulario->nombre = $request->nombre;
        $formulario->gestion_id = $request->gestion_id;
        $formulario->updated_at = Carbon::now();
        $formulario->save();
        return redirect()->route('formularios.index')->with('success', 'Formulario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Formulario $formulario)
    {
        $formulario->delete();
        return redirect()->route('formularios.index')->with('success', 'Formulario eliminado exitosamente.');
    }
}
