<?php

namespace App\Http\Controllers;

use App\Models\Gestion;
use Illuminate\Http\Request;

class GestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gestiones = Gestion::all();
        return view('gestiones.index', compact('gestiones'));
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
            'nombre' => 'required|string|max:255|unique:gestions',
        ]);
        $gestion = new Gestion();
        $gestion->nombre = $request->nombre;
        $gestion->save();
        return redirect()->route('gestiones.index')->with('success', 'Gestión creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Gestion $gestion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gestion $gestion)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $gestion = Gestion::findOrFail($id);
        request()->validate([
            'nombre' => 'required|string|max:255|unique:gestiones,nombre,' . $id,
        ]);
        $gestion->nombre = $request->nombre;
        $gestion->save();
        return redirect()->route('gestiones.index')->with('success', 'Gestión actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gestion $gestion)
    {
        $gestion->delete();
        return redirect()->route('gestiones.index')->with('success', 'Gestión eliminada exitosamente.');
    }
}
