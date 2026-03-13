<?php

namespace App\Http\Controllers;

use App\Models\Cabecera;
use App\Models\Variable;
use Illuminate\Http\Request;

class CabeceraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cabeceras = Cabecera::select('cabeceras.id as id', 'cabeceras.descripcion as descripcion', 'cabeceras.cabecera_id as cabecera_id', 'cabeceras.variable_id as variable_id', 'variables.descripcion as variable_descripcion')
            ->join('variables', 'cabeceras.variable_id', '=', 'variables.id')->get();
        $variables = Variable::all();
        return view('cabeceras.index', compact('cabeceras', 'variables'));
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
            'variable_id' => 'required|exists:variables,id',
        ]);

        $cabecera = new Cabecera();
        $cabecera->descripcion = $request->descripcion;
        $cabecera->variable_id = $request->variable_id;
        $cabecera->cabecera_id = $request->cabecera_id;
        $cabecera->estado = 'activo';
        $cabecera->save();

        return redirect()->route('cabeceras.index')->with('success', 'Cabecera creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cabecera $cabecera)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cabecera $cabecera)
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
            'variable_id' => 'required|exists:variables,id',
        ]);
        $cabecera = Cabecera::findOrFail($id);
        $cabecera->descripcion = $request->descripcion;
        $cabecera->variable_id = $request->variable_id;
        $cabecera->cabecera_id = $request->cabecera_id;
        $cabecera->save();

        return redirect()->route('cabeceras.index')->with('success', 'Cabecera actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cabecera $cabecera)
    {
        $cabecera->delete();
        return redirect()->route('cabeceras.index')->with('success', 'Cabecera eliminada exitosamente.');
    }
}
