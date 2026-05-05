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
            ['name' => 'Gestionar usuarios', 'slug' => 'users.management', 'description' => 'Crear, Editar, Eliminar, Activar y Desactivar usuarios'],
            ['name' => 'Gestionar sistemas de usuario', 'slug' => 'users.sistemas', 'description' => 'Asignar y Desasignar sistemas de usuario'],
            ['name' => 'Asignar administradores', 'slug' => 'users.admins', 'description' => 'Asignar y Desasignar administradores'],
            ['name' => 'Alcances de Operadores', 'slug' => 'users.operators', 'description' => 'Permite que el usuario tenga alcances de operador (Establecimiento, Municipio o Red)'],

            // Permisos de Roles
            ['name' => 'Ver roles', 'slug' => 'roles.view', 'description' => 'Ver lista de roles'],
            ['name' => 'Gestionar roles', 'slug' => 'roles.management', 'description' => 'Crear, Editar, Eliminar, Asignar y Desasignar roles'],

            // Permisos de Formularios
            ['name' => 'Ver formularios', 'slug' => 'formularios.view', 'description' => 'Ver formularios'],
            ['name' => 'Gestionar formularios', 'slug' => 'formularios.management', 'description' => 'Crear, Editar, Eliminar Formularios'],
            ['name' => 'Enviar fomrularios', 'slug' => 'formularios.submit', 'description' => 'Subir formularios'],

            // Permisos de Periodos
            ['name' => 'Ver periodos', 'slug' => 'periodos.view', 'description' => 'Ver periodos'],
            ['name' => 'Gestionar periodos', 'slug' => 'periodos.management', 'description' => 'Crear, Editar, Eliminar Periodos'],

            // Permisos de Gestiones
            ['name' => 'Ver gestiones', 'slug' => 'gestiones.view', 'description' => 'Ver gestiones'],
            ['name' => 'Gestionar gestiones', 'slug' => 'gestiones.management', 'description' => 'Crear, Editar, Eliminar Gestiones'],

            // Permisos de Variables
            ['name' => 'Ver variables', 'slug' => 'variables.view', 'description' => 'Ver variables'],
            ['name' => 'Gestionar variables', 'slug' => 'variables.management', 'description' => 'Crear, Editar, Eliminar Variables'],

            // Permisos de Establecimientos
            ['name' => 'Ver establecimientos', 'slug' => 'establecimientos.view', 'description' => 'Ver establecimientos'],
            ['name' => 'Gestionar establecimientos', 'slug' => 'establecimientos.management', 'description' => 'Crear, Editar, Eliminar Establecimientos'],

            // Permisos de Municipios
            ['name' => 'Ver municipios', 'slug' => 'municipios.view', 'description' => 'Ver municipios'],
            ['name' => 'Gestionar municipios', 'slug' => 'municipios.management', 'description' => 'Crear, Editar, Eliminar Municipios'],

            // Permisos de Redes
            ['name' => 'Ver redes', 'slug' => 'reds.view', 'description' => 'Ver redes'],
            ['name' => 'Gestionar redes', 'slug' => 'reds.management', 'description' => 'Crear, Editar, Eliminar Redes'],

            // Permisos de Reportes
            ['name' => 'Gestionar reportes', 'slug' => 'reportes', 'description' => 'Generar reportes'],
            ['name' => 'Exportar reportes', 'slug' => 'exportaciones', 'description' => 'Exportar reportes'],
            ['name' => 'Generar Oportunidad de Información', 'slug' => 'oportunidad', 'description' => 'Generar Reporte de Oportunidad de Información'],
            
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
