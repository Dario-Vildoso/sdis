<?php

namespace App\Http\Controllers;

use App\Models\Periodo;
use App\Models\Formulario;
use App\Models\Gestion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;

class PeriodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formularios = Formulario::all();
        $gestiones = Gestion::all();
        $periodos = Periodo::all();
        return view('periodos.index', compact('periodos', 'formularios', 'gestiones'));
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
            'form_id' => 'required|exists:formularios,id',
            'descripcion' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $periodo = new Periodo();
        $periodo->descripcion = $meses[intval($request->descripcion)-1];
        $periodo->fecha_inicio = $request->fecha_inicio;
        $periodo->fecha_fin = $request->fecha_fin;
        $periodo->formulario_id = $request->form_id;
        $periodo->numero = $request->descripcion;
        $periodo->estado = false;
        $periodo->created_at = Carbon::now();
        $periodo->updated_at = Carbon::now();
        $periodo->save();

        return redirect()->route('periodos.index')->with('success', 'Periodo creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Periodo $periodo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Periodo $periodo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $periodo = Periodo::findOrFail($id);
        request()->validate([
            'form_id' => 'required|exists:formularios,id',
            'descripcion' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);
        $periodo->formulario_id = $request->form_id;
        $periodo->descripcion = $request->descripcion;
        $periodo->fecha_inicio = $request->fecha_inicio;
        $periodo->fecha_fin = $request->fecha_fin;
        $periodo->updated_at = Carbon::now();
        $periodo->save();

        return redirect()->route('periodos.index')->with('success', 'Periodo actualizado exitosamente.');
    }

    /**
     * Toggle the 'estado' boolean for the specified resource.
     */
    public function toggle(Request $request, Periodo $periodo): JsonResponse
    {
        $periodo->estado = !$periodo->estado;
        $periodo->updated_at = Carbon::now();
        $periodo->save();

        return response()->json(['estado' => (bool) $periodo->estado]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Periodo $periodo)
    {
        $periodo->delete();
        return redirect()->route('periodos.index')->with('success', 'Periodo eliminado exitosamente.');
    }
}
