<?php

namespace App\Http\Controllers;

use App\Models\Municipio;
use App\Models\Red;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MunicipioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $municipios = Municipio::with('red')->orderBy('nombre', 'asc')->get();
        $redes = Red::all();
        return view('municipios.index', compact('municipios', 'redes'));
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
        $request->validate([
            'nombre' => 'required|string|max:255',
            'red_id' => 'required|integer|exists:reds,id',
            'codigo' => 'nullable|string|max:50|unique:municipios',
        ]);

        $municipio = new Municipio();
        $municipio->nombre = $request->nombre;
        $municipio->red_id = $request->red_id;
        $municipio->codigo = $request->codigo;
        $municipio->created_at = Carbon::now();
        $municipio->updated_at = Carbon::now();
        $municipio->save();

        return redirect()->route('municipios.index')->with('success', 'Municipio creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Municipio $municipio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Municipio $municipio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $municipio = Municipio::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'red_id' => 'required|integer|exists:reds,id',
            'codigo' => 'nullable|string|max:50|unique:municipios,codigo,' . $municipio->id,
        ]);

        $municipio->nombre = $request->nombre;
        $municipio->red_id = $request->red_id;
        $municipio->codigo = $request->codigo;
        $municipio->updated_at = Carbon::now();
        $municipio->save();

        return redirect()->route('municipios.index')->with('success', 'Municipio actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Municipio $municipio)
    {
        $municipio->delete();
        return redirect()->route('municipios.index')->with('success', 'Municipio eliminado exitosamente.');
    }
}