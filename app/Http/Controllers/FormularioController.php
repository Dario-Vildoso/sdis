<?php

namespace App\Http\Controllers;

use App\Models\Formulario;
use App\Models\Gestion;
use App\Models\Asignacion;
use App\Models\Variable;
use App\Models\Periodo;
use App\Models\Dato;
use App\Models\Establecimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Expr\FuncCall;

class FormularioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $formularios = Formulario::all()->where('sistema_id', Auth::user()->sistema_id);
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
            'gestion_id' => 'required|exists:gestions,id',
        ]);
        $formulario = new Formulario();
        $formulario->sistema_id = Auth::user()->sistema_id;
        $formulario->gestion_id = $request->gestion_id;
        $formulario->created_at = Carbon::now();
        $formulario->updated_at = Carbon::now();
        $formulario->save();
        return redirect()->route('formularios.index')->with('success', 'Formulario creado exitosamente.');
    }
    public function menu()
    {
        $user = Auth::user();
        if (!$user->alcances->tipo == 'establecimiento') {
            return redirect()->route('dashboard');
        }
        $formularios = Formulario::where('sistema_id', $user->sistema_id)
            ->whereHas('establecimientos', function ($q) use ($user) {
                $q->where('establecimientos.id', $user->alcances->alcance_id);
            })  
            ->get();
        $gestiones = Gestion::all();
        return view('formularios.menu', compact('formularios', 'gestiones'));
    }

    public function cargarPeriodo(Formulario $formulario, Periodo $periodo)
    {

        if  ((now() > $periodo->fecha_fin || now() < $periodo->fecha_inicio) && !$periodo->estado) {
            return redirect()->route('formularios.menu')->with('error', 'El periodo no está activo.');
        }
        $user = Auth::user();

        $formulario = Formulario::with(['variables.subvariables', 'variables.cabeceras' => function ($query) {
            $query->whereNull('cabecera_id')->with('hijos');
        }])->findOrFail($formulario->id);
        $formulario->variables->each(function ($variable) {
            $variable->total_leaf_columns = $variable->cabeceras->sum(
                fn($c) => $this->countLeaves($c)
            );
        });
        $datos = Dato::where('periodo_id', $periodo->id)->where('establecimiento_id', $user->alcances()->first()->alcance_id)->get();
        return view('formularios.periodo', compact('formulario', 'user', 'periodo', 'datos'));
    }

    public function storeData(Request $request, Formulario $formulario, Periodo $periodo)
    {
        $user = Auth::user();
        $establecimiento_id = $user->alcances()->first()->alcance_id;
        $datos = $request->datos;
        $datos = json_decode($datos, true);
        foreach ($datos as $dato) {

            Dato::updateOrCreate(
                [
                    'cabecera_id' => $dato['cabecera_id'],
                    'subvariable_id' => $dato['subvariable_id'],
                    'periodo_id' => $periodo->id,
                    'establecimiento_id' => $establecimiento_id,
                ],
                [
                    'valor' => $dato['valor'],
                    'fecha' => date('Y-m-d'),
                    'hora' => date('H:i:s'),
                ]
            );
        }

        return response()->json([
            'message' => 'Datos guardados correctamente'
        ]);
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
        $variables = Variable::all();
        return view('formularios.assignments', compact('formulario', 'variables'));
    }

    public function addVariable(Request $request)
    {
        $formulario = Formulario::findOrFail($request->formulario_id);
        $variable = Variable::findOrFail($request->variable_id);
        $asignacion = new Asignacion();
        $asignacion->formulario_id = $formulario->id;
        $asignacion->variable_id = $variable->id;
        $asignacion->save();
        return response()->json([
            'success' => true,
            'message' => 'Variable asignada exitosamente.',
        ]);
    }

    public function removeVariable(Request $request)
    {
        $formulario = Formulario::findOrFail($request->formulario_id);
        $variable = Variable::findOrFail($request->variable_id);
        $asignacion = Asignacion::where('formulario_id', $formulario->id)->where('variable_id', $variable->id)->first();
        $asignacion->delete();
        return response()->json([
            'success' => true,
            'message' => 'Variable desasignada exitosamente.',
        ]);
    }

    public function establecimientosAsignados(Formulario $formulario)
    {
        $asignadosIds = $formulario->establecimientos()->pluck('establecimientos.id')->toArray();

        $establecimientos = Establecimiento::with('municipio')
            ->where('estado', true)
            ->orderBy('descripcion')
            ->get();

        return response()->json([
            'formulario_id' => $formulario->id,
            'formulario_nombre' => $formulario->sistema->nombre . ' - ' . $formulario->gestion->nombre,
            'establecimientos' => $establecimientos->map(function ($establecimiento) use ($asignadosIds) {
                return [
                    'id' => $establecimiento->id,
                    'descripcion' => $establecimiento->descripcion,
                    'nivel' => $establecimiento->nivel,
                    'municipio' => $establecimiento->municipio->descripcion ?? null,
                    'asignado' => in_array($establecimiento->id, $asignadosIds),
                ];
            }),
        ]);
    }

    public function asignarEstablecimientos(Request $request, Formulario $formulario)
    {
        $asignados = $request->input('establecimientos.asignados', []);
        $desasignados = $request->input('establecimientos.desasignados', []);

        // Asignar nuevos establecimientos
        foreach ($asignados as $id) {
            if (!$formulario->establecimientos()->where('establecimiento_id', $id)->exists()) {
                $formulario->establecimientos()->attach($id);
            }
        }

        // Desasignar establecimientos
        foreach ($desasignados as $id) {
            if ($formulario->establecimientos()->where('establecimiento_id', $id)->exists()) {
                $formulario->establecimientos()->detach($id);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Establecimientos Asignados.',
        ]);
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
            'gestion_id' => 'required|exists:gestions,id',
        ]);
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
