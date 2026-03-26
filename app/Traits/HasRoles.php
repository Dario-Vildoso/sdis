<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;

trait HasRoles
{
    /**
     * Asignar un rol al usuario
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        if (!$this->roles->contains($role)) {
            $this->roles()->attach($role);
        }

        return $this;
    }

    /**
     * Revocar un rol del usuario
     */
    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->detach($role);

        return $this;
    }

    public function syncRoles($roles)
    {
        $this->roles()->sync($roles);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($role)
    {
        if (is_array($role)) {
            return $this->roles->whereIn('slug', $role)->count() > 0;
        }

        return $this->roles->where('slug', $role)->count() > 0;
    }

    /**
     * Verificar si el usuario tiene alguno de los roles
     */
    public function hasAnyRole($roles)
    {
        return $this->hasRole($roles);
    }

    /**
     * Verificar si el usuario tiene todos los roles
     */
    public function hasAllRoles($roles)
    {
        return collect($roles)->every(fn ($role) => $this->hasRole($role));
    }

    /**
     * Verificar si el usuario tiene un permiso através de sus roles
     */
    public function hasPermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
        }

        return $this->roles->flatMap->permissions->contains($permission);
    }

    /**
     * Obtener todos los permisos del usuario a través de sus roles
     */
    public function getAllPermissions()
    {
        return $this->roles->flatMap->permissions->unique();
    }

    /**
     * Verificar si es administrador
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }
}
