<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Sistema;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            // Permisos de Usuarios
            ['name' => 'Ver usuarios', 'slug' => 'users.view', 'description' => 'Ver lista de usuarios'],
            ['name' => 'Crear usuarios', 'slug' => 'users.create', 'description' => 'Crear nuevos usuarios'],
            ['name' => 'Editar usuarios', 'slug' => 'users.edit', 'description' => 'Editar usuarios existentes'],
            ['name' => 'Eliminar usuarios', 'slug' => 'users.delete', 'description' => 'Eliminar usuarios'],

            // Permisos de Roles
            ['name' => 'Ver roles', 'slug' => 'roles.view', 'description' => 'Ver lista de roles'],
            ['name' => 'Crear roles', 'slug' => 'roles.create', 'description' => 'Crear nuevos roles'],
            ['name' => 'Editar roles', 'slug' => 'roles.edit', 'description' => 'Editar roles existentes'],
            ['name' => 'Eliminar roles', 'slug' => 'roles.delete', 'description' => 'Eliminar roles'],

            // Permisos de Formularios
            ['name' => 'Ver formularios', 'slug' => 'formularios.view', 'description' => 'Ver formularios'],
            ['name' => 'Crear formularios', 'slug' => 'formularios.create', 'description' => 'Crear nuevos formularios'],
            ['name' => 'Editar formularios', 'slug' => 'formularios.edit', 'description' => 'Editar formularios'],
            ['name' => 'Eliminar formularios', 'slug' => 'formularios.delete', 'description' => 'Eliminar formularios'],

            // Permisos de Gestiones
            ['name' => 'Ver gestiones', 'slug' => 'gestiones.view', 'description' => 'Ver gestiones'],
            ['name' => 'Crear gestiones', 'slug' => 'gestiones.create', 'description' => 'Crear nuevas gestiones'],
            ['name' => 'Editar gestiones', 'slug' => 'gestiones.edit', 'description' => 'Editar gestiones'],
            ['name' => 'Eliminar gestiones', 'slug' => 'gestiones.delete', 'description' => 'Eliminar gestiones'],

            // Permisos de Variables
            ['name' => 'Ver variables', 'slug' => 'variables.view', 'description' => 'Ver variables'],
            ['name' => 'Crear variables', 'slug' => 'variables.create', 'description' => 'Crear nuevas variables'],
            ['name' => 'Editar variables', 'slug' => 'variables.edit', 'description' => 'Editar variables'],
            ['name' => 'Eliminar variables', 'slug' => 'variables.delete', 'description' => 'Eliminar variables'],

            // Permisos de Datos
            ['name' => 'Ver datos', 'slug' => 'datos.view', 'description' => 'Ver datos'],
            ['name' => 'Crear datos', 'slug' => 'datos.create', 'description' => 'Crear nuevos datos'],
            ['name' => 'Editar datos', 'slug' => 'datos.edit', 'description' => 'Editar datos'],
            ['name' => 'Eliminar datos', 'slug' => 'datos.delete', 'description' => 'Eliminar datos'],

            // Permisos generales
            ['name' => 'Acceso total', 'slug' => 'admin', 'description' => 'Acceso total al sistema'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }

        // Crear Sistemas
        $SDIS = Sistema::firstOrCreate(
            ['nombre' => 'SDIs'],
        );
        $Vigilancia = Sistema::firstOrCreate(
            ['nombre' => 'Vigilancia'],
        );
        $Hospitales = Sistema::firstOrCreate(
            ['nombre' => 'Hospitales'],
        );

        // Crear roles
        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Administrador', 'description' => 'Acceso total sin restricciones']
        );

        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrador', 'description' => 'Gestion copleta de usuarios y formularios']
        );

        // Asignar permisos a roles
        $allPermissions = Permission::all()->pluck('id')->toArray();
        $superAdmin->permissions()->sync($allPermissions);
        $admin->permissions()->sync($allPermissions);

        // Crear usuario super-admin
        $superAdminUser = User::firstOrCreate(
            ['name' => 'Super Admin'],
            [
                'password' => bcrypt('superadmin1234'),
            ]
        );
        $superAdminUser->assignRole('super-admin');
        $superAdminUser->sistemas()->sync([$SDIS->id, $Vigilancia->id, $Hospitales->id]);

        // Crear usuario administrador
        $adminSDIS = User::firstOrCreate(
            ['name' => 'Administrador SDIS'],
            [
                'password' => bcrypt('admin1234'),
            ]
        );
        // Asignar rol
        $adminSDIS->assignRole('admin');
        $adminSDIS->sistemas()->sync([$SDIS->id]);
        
        $adminHospitales = User::firstOrCreate(
            ['name' => 'Administrador Hospitales'],
            [
                'password' => bcrypt('admin1234'),
            ]
        );
        // Asignar rol
        $adminHospitales->assignRole('admin');
        $adminHospitales->sistemas()->sync([$Hospitales->id]);

        $adminVigilancia = User::firstOrCreate(
            ['name' => 'Administrador Vigilancia'],
            [
                'password' => bcrypt('admin1234'),
            ]
        );
        // Asignar rol
        $adminVigilancia->assignRole('admin');
        $adminVigilancia->sistemas()->sync([$Vigilancia->id]);
    }
}
