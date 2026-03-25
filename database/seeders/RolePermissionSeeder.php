<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

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

        // Crear roles
        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrador', 'description' => 'Gestion copleta de usuarios y formularios']
        );

        $reports = Role::firstOrCreate(
            ['slug' => 'reportes'],
            ['name' => 'Reportes', 'description' => 'Envio de datos y/o gestion de reportes']
        );

        // Asignar permisos a roles
        $allPermissions = Permission::all()->pluck('id')->toArray();
        $admin->permissions()->sync($allPermissions);

        $reportsPermissions = Permission::whereIn('slug', [
            'users.view',
            'formularios.view', 'formularios.create', 'formularios.edit',
            'gestiones.view', 'gestiones.create', 'gestiones.edit',
            'variables.view', 'variables.create', 'variables.edit',
            'datos.view', 'datos.create', 'datos.edit',
        ])->pluck('id')->toArray();
        $reports->permissions()->sync($reportsPermissions);

        // Crear usuario administrador
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('admin1234'),
            ]
        );

        // Asignar rol
        $adminUser->assignRole('admin');


        // Crear usuario de reportes
        $establecimietoUser = User::firstOrCreate(
            ['email' => 'Establecimiento@example.com'],
            [
                'name' => 'Establecimiento',
                'password' => bcrypt('establecimiento1234'),
            ]
        );

        $establecimietoUser->assignRole('reportes');

        // Crear usuario de reportes
        $municipioUser = User::firstOrCreate(
            ['email' => 'Municipio@example.com'],
            [
                'name' => 'Municipio',
                'password' => bcrypt('municipio1234'),
            ]
        );

        $municipioUser->assignRole('reportes');

        // Crear usuario de reportes
        $redUser = User::firstOrCreate(
            ['email' => 'red@example.com'],
            [
                'name' => 'Red',
                'password' => bcrypt('redes1234'),
            ]
        );

        $redUser->assignRole('reportes');

        $establecimietoUser->alcances()->create([
            'tipo' => 'establecimiento',
            'alcance_id' => 1
        ]);

        $municipioUser->alcances()->create([
            'tipo' => 'municipio',
            'alcance_id' => 1
        ]);

        $redUser->alcances()->create([
            'tipo' => 'red',
            'alcance_id' => 1
        ]);
    }
}
