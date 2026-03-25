<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Variable;
use App\Models\SubVariable;
use App\Models\Cabecera;
use App\Models\Formulario;
use Illuminate\Http\Request;

class AsignacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //
    }

    public function createVariableForm(Request $request, formulario $form)
    {
        request()->validate([
            'descripcion' => 'required|string|max:255',
        ]);
        $variable = new Variable();
        $variable->descripcion = $request->descripcion;
        $variable->save();
        $subvariable = new SubVariable();
        $subvariable->descripcion = 'Nueva subvariable';
        $subvariable->variable_id = $variable->id;
        $subvariable->estado = 'activo';
        $subvariable->save();
        $cabecera = new Cabecera();
        $cabecera->descripcion = 'Nueva cabecera';
        $cabecera->variable_id = $variable->id;
        $cabecera->cabecera_id = null;
        $cabecera->estado = 'activo';
        $cabecera->save();
        $asignacion = new Asignacion();
        $asignacion->formulario_id = $form->id;
        $asignacion->variable_id = $variable->id;
        $asignacion->save();
        return response()->json([
            'success' => true,
            'message' => 'Variable asignada exitosamente.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Asignacion $asignacion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asignacion $asignacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asignacion $asignacion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asignacion $asignacion)
    {
        //
    }
}
