<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Relación muchos a muchos con usuarios
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * Relación muchos a muchos con permisos
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * Asignar un permiso al rol
     */
    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        if (!$this->permissions->contains($permission)) {
            $this->permissions()->attach($permission);
        }

        return $this;
    }

    /**
     * Revocar un permiso del rol
     */
    public function revokePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission);

        return $this;
    }

    /**
     * Verificar si el rol tiene un permiso
     */
    public function hasPermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
        }

        return $this->permissions->contains($permission);
    }
}
