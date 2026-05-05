<?php

namespace App\Http\Controllers;

use App\Models\Red;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $redes = Red::orderBy('nombre', 'asc')->get();
        return view('redes.index', compact('redes'));
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
            'codigo' => 'nullable|string|max:50|unique:reds',
        ]);

        $red = new Red();
        $red->nombre = $request->nombre;
        $red->codigo = $request->codigo;
        $red->created_at = Carbon::now();
        $red->updated_at = Carbon::now();
        $red->save();

        return redirect()->route('redes.index')->with('success', 'Red creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Red $red)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Red $red)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $red = Red::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:50|unique:reds,codigo,' . $red->id,
        ]);

        $red->nombre = $request->nombre;
        $red->codigo = $request->codigo;
        $red->updated_at = Carbon::now();
        $red->save();

        return redirect()->route('redes.index')->with('success', 'Red actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Red $red)
    {
        $red->delete();
        return redirect()->route('redes.index')->with('success', 'Red eliminada exitosamente.');
    }
}
