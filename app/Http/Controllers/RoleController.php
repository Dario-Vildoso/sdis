<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = Role::paginate(10);
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'slug' => 'required|string|max:255|unique:roles|regex:/^[a-z0-9_-]+$/',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create($validated);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('roles.show', $role)
            ->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $permissions = Permission::all();
        return view('roles.show', compact('role', 'permissions'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id . '|regex:/^[a-z0-9_-]+$/',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update($validated);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('roles.show', $role)
            ->with('success', 'Rol actualizado exitosamente.');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        if ($role->slug === 'admin' || $role->users()->exists()) {
            return redirect()->route('roles.index')
                ->with('error', 'No se puede eliminar este rol.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado exitosamente.');
    }

    /**
     * Assign a role to a user
     */
    public function assignToUser(Request $request, Role $role)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $role->users()->attach($validated['user_id']);

        return redirect()->back()
            ->with('success', 'Rol asignado al usuario exitosamente.');
    }

    /**
     * Remove a role from a user
     */
    public function removeFromUser(Request $request, Role $role)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $role->users()->detach($validated['user_id']);

        return redirect()->back()
            ->with('success', 'Rol removido del usuario exitosamente.');
    }
}
