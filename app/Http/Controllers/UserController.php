<?php

namespace App\Http\Controllers;

use App\Models\Alcance;
use App\Models\User;
use App\Models\Role;
use App\Models\Establecimiento;
use App\Models\Municipio;
use App\Models\Red;
use App\Models\Sistema;
use App\Models\user_sistema;
use App\Traits\HasRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\returnArgument;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = Auth::user()->hasRole('super-admin')? User::all() : User::with('alcances')
            ->whereHas('sistemas', function ($query) {
                $query->where('sistema_id', Auth::user()->sistema_id);
            })
            ->get();
        $unsignedUsers = Auth::user()->hasRole('super-admin')? [] : User::with('alcances')
            ->whereDoesntHave('sistemas', function ($query) {
                $query->where('sistema_id', Auth::user()->sistema_id);
            })
            ->get();
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
        $sistemas = Sistema::all();
        return view('users.index', compact('users', 'unsignedUsers', 'roles', 'establecimientos', 'municipios', 'redes', 'conteos', 'sistemas'));
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
            'name' => 'required|string|max:255|unique:users,name',
            'password' => 'required|string',
            'role' => 'required|exists:roles,id',
            'sistemas' => 'nullable|array',
            'sistemas.*' => 'exists:sistemas,id',
        ]); 
        $user = new User();
        $user->name = $request->name;
        $user->password = Hash::make($request->password);
        $user->save();
        if(Auth::user()->hasRole('super-admin')){
            $user->sistemas()->attach($request->sistemas);
            $user->sistema_id = $request->sistemas[0];
            $user->save();
        } else {
            $user->sistemas()->attach(Auth::user()->sistema_id);
            $user->sistema_id = Auth::user()->sistema_id;
            $user->save();
        }
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

    public function assignSystem(User $user)
    {
        $user->sistemas()->attach(Auth::user()->sistema_id);
        $user->save();

        return redirect()->route('users.index')->with('success', 'Sistema asignado exitosamente.');
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
            'password' => 'nullable|string|min:8',
            'role' => 'required|exists:roles,id',
            'sistemas' => 'nullable|array',
            'sistemas.*' => 'exists:sistemas,id',
        ]);

        $user->name = $request->name;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        if (Auth::user()->hasRole('super-admin')) {
            $user->sistemas()->sync($request->sistemas);
        }

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

    public function toggle(User $user)
    {
        $user->estado = !$user->estado;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Estado del usuario actualizado exitosamente.');
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
