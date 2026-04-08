<?php

namespace App\Http\Controllers;

use App\Models\Alcance;
use App\Models\User;
use App\Models\Role;
use App\Models\Establecimiento;
use App\Models\Municipio;
use App\Models\Red;
use App\Traits\HasRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use function PHPUnit\Framework\returnArgument;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('alcances')->get();
        $conteos = [
            'red'            => $users->where('alcances.tipo', 'red')->count(),
            'municipio'      => $users->where('alcances.tipo', 'municipio')->count(),
            'establecimiento'=> $users->where('alcances.tipo', 'establecimiento')->count(),
        ];
        $roles = Role::all();
        $establecimientos = Establecimiento::all();
        $municipios = Municipio::all();
        $redes = Red::all();
        $alcances = Alcance::all();
         $users->each(function ($user) use ($alcances) {
            $user->alcance = $alcances->where('usuario_id', $user->id)->first();
        });
        return view('users.index', compact('users', 'roles', 'establecimientos', 'municipios', 'redes', 'conteos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
            'role' => 'required|exists:roles,id',
        ]); 

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        $user->assignRole(ROle::findOrFail($request->role));
        $user->load('roles');
        if ($user->hasRole('reportes')) {
            switch ($request->tipo) {
                case 'establecimiento':
                    Alcance::create([
                        'usuario_id' => $user->id,
                        'tipo' => $request->tipo,
                        'alcance_id' => $request->establecimiento
                        ]);
                        break;
                case 'municipio':
                    Alcance::create([
                        'usuario_id' => $user->id,
                        'tipo' => $request->tipo,
                        'alcance_id' => $request->municipio
                    ]);
                    break;
                case 'red':
                    Alcance::create([
                        'usuario_id' => $user->id,
                        'tipo' => $request->tipo,
                        'alcance_id' => $request->red
                    ]);
                    break;
            }
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|exists:roles,id',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $user->syncRoles($request->role);

        $user->load('roles');

        if ($user->hasRole('reportes')) {

            $alcanceId = match ($request->tipo) {
                'establecimiento' => $request->establecimiento,
                'municipio' => $request->municipio,
                'red' => $request->red,
            };

            Alcance::updateOrCreate(
                [
                    'usuario_id' => $user->id
                ],
                [
                    'tipo' => $request->tipo,
                    'alcance_id' => $alcanceId
                ]
            );
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
