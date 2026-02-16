<?php

namespace App\Http\Controllers;

use App\Models\SubVariable;
use App\Models\Variable;
use Illuminate\Http\Request;

class SubVariableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subvariables = SubVariable::select('sub_variables.id as id', 'sub_variables.descripcion as descripcion', 'sub_variables.variable_id as variable_id', 'variables.descripcion as variable_descripcion')
            ->join('variables', 'sub_variables.variable_id', '=', 'variables.id')
            ->get();
        $variables = Variable::all();
        return view('subvariables.index', compact('subvariables', 'variables'));
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

        $subVariable = new SubVariable();
        $subVariable->descripcion = $request->descripcion;
        $subVariable->variable_id = $request->variable_id;
        $subVariable->save();

        return redirect()->route('subvariables.index')->with('success', 'SubVariable creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SubVariable $subVariable)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubVariable $subVariable)
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

        $subVariable = SubVariable::findOrFail($id);
        $subVariable->descripcion = $request->descripcion;
        $subVariable->variable_id = $request->variable_id;
        $subVariable->save();

        return redirect()->route('subvariables.index')->with('success', 'SubVariable actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subVariable = SubVariable::findOrFail($id);
        $subVariable->delete();
        return redirect()->route('subvariables.index')->with('success', 'SubVariable eliminada exitosamente.');
    }
}
