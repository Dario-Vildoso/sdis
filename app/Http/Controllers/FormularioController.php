<?php

namespace App\Http\Controllers;

use App\Models\Formulario;
use App\Models\Gestion;
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

    /**
     * Display the specified resource.
     */
    public function show(Formulario $formulario)
    {
        //
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
