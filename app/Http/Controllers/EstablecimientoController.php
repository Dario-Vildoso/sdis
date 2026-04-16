<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EstablecimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $establecimientos = Establecimiento::all();
        $municipios = Municipio::all();
        return view('establecimientos.index', compact('establecimientos' , 'municipios'));
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
            'codigo_est' => 'required|string|max:50|unique:establecimientos',
            'gestion' => 'required|string|max:50',
        ]);
        $municipio = Municipio::find($request->id_mun);
        $establecimiento = new Establecimiento();
        $establecimiento->descripcion = $request->descripcion;
        $establecimiento->id_mun = $municipio->id;
        $establecimiento->codigo_est = $request->codigo_est;
        $establecimiento->gestion = $request->gestion;
        $establecimiento->nivel = $request->nivel;
        $establecimiento->estado = true; // Activo por defecto
        $establecimiento->created_at = Carbon::now();
        $establecimiento->updated_at = Carbon::now();
        $establecimiento->save();
        return redirect()->route('establecimientos.index')->with('success', 'Establecimiento creado exitosamente.');
    }

    public function toggle(Request $request, $id)
    {
        $establecimiento = Establecimiento::findOrFail($id);
        $establecimiento->estado = !$establecimiento->estado;
        $establecimiento->save();
        return response()->json(['success' => true, 'estado' => $establecimiento->estado]);
    }
    /**
     * Display the specified resource.
     */
    public function show(Establecimiento $establecimiento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Establecimiento $establecimiento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $establecimiento = Establecimiento::findOrFail($id);
        request()->validate([
            'descripcion' => 'required|string|max:255',
            'id_mun' => 'required|integer|exists:municipios,id',
            'codigo_est' => 'required|string|max:50|unique:establecimientos,codigo_est,'.$establecimiento->id,
            'gestion' => 'required|string|max:50',
            'nivel' => 'required|integer|min:1|max:3',
        ]);
        $establecimiento->descripcion = $request->descripcion;
        $establecimiento->id_mun = $request->id_mun;
        $establecimiento->codigo_est = $request->codigo_est;
        $establecimiento->gestion = $request->gestion;
        $establecimiento->nivel = $request->nivel;
        $establecimiento->updated_at = Carbon::now();
        $establecimiento->save();
        return redirect()->route('establecimientos.index')->with('success', 'Establecimiento actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Establecimiento $establecimiento)
    {
        $establecimiento->delete();
        return redirect()->route('establecimientos.index')->with('success', 'Establecimiento eliminado exitosamente.');
    }
}
