<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Red;
use App\Models\Municipio;
use App\Models\Establecimiento;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Variable;
use App\Models\Cabecera;
use App\Models\SubVariable;
use App\Models\Sistema;
use App\Models\Formulario;
use App\Models\Gestion;
use App\Models\Asignacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        
        // Crear Sistemas
        $SDIS = Sistema::firstOrCreate(
            ['nombre' => 'SDIS'],
        );
        $Vigilancia = Sistema::firstOrCreate(
            ['nombre' => 'Vigilancia'],
        );
        $Hospitales = Sistema::firstOrCreate(
            ['nombre' => 'Hospitales'],
        );

        Red::create([
            'nombre' => 'RED DE SALUD I SUCRE',
            'CODIGO' => '1',
        ]);
        Red::create([
            'nombre' => 'RED DE SALUD II TARABUCO',
            'CODIGO' => '2',
        ]);
        Red::create([
            'nombre' => 'RED DE SALUD III PADILLA',
            'CODIGO' => '3',
        ]);
        Red::create([
            'nombre' => 'RED DE SALUD IV AZURDUY',
            'CODIGO' => '4',
        ]);
        Red::create([
            'nombre' => 'RED DE SALUD V MONTEAGUDO',
            'CODIGO' => '5',
        ]);
        Red::create([
            'nombre' => 'RED DE SALUD VI CAMARGO',
            'CODIGO' => '6',
        ]);
        Red::create([
            'nombre' => 'RED DE SALUD VII RURAL',
            'CODIGO' => '7',
        ]);
//6 DISTRITOS DE SUCRE
        Municipio::create([
            'nombre' => 'SUCRE',
            'CODIGO' => '10101',
            'red_id' => '1',
        ]);

    //6MUNCIPIOS RED 2
        Municipio::create([
            'nombre' => 'ZUDANEZ',
            'CODIGO' => '10301',
            'red_id' => '2',
        ]);
        Municipio::create([
            'nombre' => 'PRESTO',
            'CODIGO' => '10302',
            'red_id' => '2',
        ]);
        Municipio::create([
            'nombre' => 'MOJOCOYA',
            'CODIGO' => '10303',
            'red_id' => '2',
        ]);
        Municipio::create([
            'nombre' => 'ICLA',
            'CODIGO' => '10304',
            'red_id' => '2',
        ]);
        Municipio::create([
            'nombre' => 'TARABUCO',
            'CODIGO' => '10601',
            'red_id' => '2',
        ]);
        Municipio::create([
            'nombre' => 'YAMPARAEZ',
            'CODIGO' => '10602',
            'red_id' => '2',
        ]);

    //5 MUNICIPIOS RED 3
        Municipio::create([
            'nombre' => 'PADILLA',
            'CODIGO' => '10401',
            'red_id' => '3',
        ]);
        Municipio::create([
            'nombre' => 'TOMINA',
            'CODIGO' => '10402',
            'red_id' => '3',
        ]);
        Municipio::create([
            'nombre' => 'ALCALA',
            'CODIGO' => '10404',
            'red_id' => '3',
        ]);
        Municipio::create([
            'nombre' => 'EL VILLAR',
            'CODIGO' => '10405',
            'red_id' => '3',
        ]);
        Municipio::create([
            'nombre' => 'VILLA SERRANO',
            'CODIGO' => '10801',
            'red_id' => '3',
        ]);

    //3 MUNICIPIOS RED 4
        Municipio::create([
            'nombre' => 'AZURDUY',
            'CODIGO' => '10201',
            'red_id' => '4',
        ]);
        Municipio::create([
            'nombre' => 'TARVITA',
            'CODIGO' => '10202',
            'red_id' => '4',
        ]);
        Municipio::create([
            'nombre' => 'SOPACHUY',
            'CODIGO' => '10403',
            'red_id' => '4',
        ]);
    //5 MUNICIPIOS RED 5
        Municipio::create([
            'nombre' => 'MONTEAGUDO',
            'CODIGO' => '10501',
            'red_id' => '5',
        ]);
        Municipio::create([
            'nombre' => 'HUACARETA',
            'CODIGO' => '10502',
            'red_id' => '5',
        ]);
        Municipio::create([
            'nombre' => 'MUYPAMPA',
            'CODIGO' => '11001',
            'red_id' => '5',
        ]);
        Municipio::create([
            'nombre' => 'HUACAYA',
            'CODIGO' => '11002',
            'red_id' => '5',
        ]);
        Municipio::create([
            'nombre' => 'MACHARETI',
            'CODIGO' => '11003',
            'red_id' => '5',
        ]);
    //7 MUNICIPIOS RED 6   role
        Municipio::create([
            'nombre' => 'CAMARGO',
            'CODIGO' => '10701',
            'red_id' => '6',
        ]);
        Municipio::create([
            'nombre' => 'SAN NLUCAS',
            'CODIGO' => '10702',
            'red_id' => '6',
        ]);
        Municipio::create([
            'nombre' => 'INCAHUASI',
            'CODIGO' => '10703',
            'red_id' => '6',
        ]);
        Municipio::create([
            'nombre' => 'VILLA CHARCAS',
            'CODIGO' => '10704',
            'red_id' => '6',
        ]);
        Municipio::create([
            'nombre' => 'VILLA ABECIA',
            'CODIGO' => '10901',
            'red_id' => '6',
        ]);
        Municipio::create([
            'nombre' => 'CULPINA',
            'CODIGO' => '10903',
            'red_id' => '6',
        ]);
    //HOSPITALES
        Municipio::create([
        'nombre' => 'HOSP 2DO NIVEL',
        'CODIGO' => '101022',
        'red_id' => '1',
        ]);
        Municipio::create([
        'nombre' => 'HOSP 3ER NIVEL',
        'CODIGO' => '101033',
        'red_id' => '1',
        ]);

        // ESTABLECIMIENTOS
        Establecimiento::create([
            'descripcion' => 'Centro de Salud Sucre Central',
            'id_mun' => 1,
            'estado' => true,
            'codigo_est' => 'CSC001',
        ]);
        Establecimiento::create([
            'descripcion' => 'Puesto de Salud Tablas',
            'id_mun' => 2,
            'estado' => true,
            'codigo_est' => 'PST001',
        ]);
        Establecimiento::create([
            'descripcion' => 'Centro de Salud San Lucas',
            'id_mun' => 28,
            'estado' => true,
            'codigo_est' => 'CSL001',
        ]);
        Establecimiento::create([
            'descripcion' => 'Centro de Salud Padilla',
            'id_mun' => 8,
            'estado' => true,
            'codigo_est' => 'CSP001',
        ]);

        // GESTIONES (2020-2027)
        for ($year = 2020; $year <= 2027; $year++) {
            Gestion::create(['nombre' => (string)$year]);
        }

        // VARIABLES
        $var1 = Variable::create(['descripcion' => 'Consultas Externas']);
        $var2 = Variable::create(['descripcion' => 'Internaciones']);
        $var3 = Variable::create(['descripcion' => 'Vacunaciones']);
        $var4 = Variable::create(['descripcion' => 'Partos']);
        $var5 = Variable::create(['descripcion' => 'Atenciones Preventivas']);

        // SUBVARIABLES para Consultas Externas
        SubVariable::create(['descripcion' => 'Consultas Generales', 'estado' => 'activo', 'variable_id' => $var1->id]);
        SubVariable::create(['descripcion' => 'Consultas Especializadas', 'estado' => 'activo', 'variable_id' => $var1->id]);
        SubVariable::create(['descripcion' => 'Consultas Odontológicas', 'estado' => 'activo', 'variable_id' => $var1->id]);

        // SUBVARIABLES para Internaciones
        SubVariable::create(['descripcion' => 'Internaciones Médicas', 'estado' => 'activo', 'variable_id' => $var2->id]);
        SubVariable::create(['descripcion' => 'Internaciones Quirúrgicas', 'estado' => 'activo', 'variable_id' => $var2->id]);

        // SUBVARIABLES para Vacunaciones
        SubVariable::create(['descripcion' => 'BCG', 'estado' => 'activo', 'variable_id' => $var3->id]);
        SubVariable::create(['descripcion' => 'Pentavalente', 'estado' => 'activo', 'variable_id' => $var3->id]);
        SubVariable::create(['descripcion' => 'OPV', 'estado' => 'activo', 'variable_id' => $var3->id]);
        SubVariable::create(['descripcion' => 'SRP', 'estado' => 'activo', 'variable_id' => $var3->id]);

        // SUBVARIABLES para Partos
        SubVariable::create(['descripcion' => 'Partos Vaginales', 'estado' => 'activo', 'variable_id' => $var4->id]);
        SubVariable::create(['descripcion' => 'Partos por Cesárea', 'estado' => 'activo', 'variable_id' => $var4->id]);

        // SUBVARIABLES para Atenciones Preventivas
        SubVariable::create(['descripcion' => 'Control Prenatal', 'estado' => 'activo', 'variable_id' => $var5->id]);
        SubVariable::create(['descripcion' => 'Control Postnatal', 'estado' => 'activo', 'variable_id' => $var5->id]);
        SubVariable::create(['descripcion' => 'Planificación Familiar', 'estado' => 'activo', 'variable_id' => $var5->id]);

        // CABECERAS para Consultas Externas (raíces y anidadas)
        $cab1 = Cabecera::create([
            'descripcion' => 'Total Consultas',
            'variable_id' => $var1->id,
            'cabecera_id' => null, // raíz
            'estado' => 'activo',
        ]);
        $cab1_1 = Cabecera::create([
            'descripcion' => 'Consultas por Sexo',
            'variable_id' => $var1->id,
            'cabecera_id' => $cab1->id,
            'estado' => 'activo',
        ]);
        Cabecera::create([
            'descripcion' => 'Masculino',
            'variable_id' => $var1->id,
            'cabecera_id' => $cab1_1->id,
            'estado' => 'activo',
        ]);
        Cabecera::create([
            'descripcion' => 'Femenino',
            'variable_id' => $var1->id,
            'cabecera_id' => $cab1_1->id,
            'estado' => 'activo',
        ]);

        // CABECERAS para Internaciones
        $cab2 = Cabecera::create([
            'descripcion' => 'Total Internaciones',
            'variable_id' => $var2->id,
            'cabecera_id' => null, // raíz
            'estado' => 'activo',
        ]);
        $cab2_1 = Cabecera::create([
            'descripcion' => 'Internaciones por Sexo',
            'variable_id' => $var2->id,
            'cabecera_id' => $cab2->id,
            'estado' => 'activo',
        ]);
        Cabecera::create([
            'descripcion' => 'Masculino',
            'variable_id' => $var2->id,
            'cabecera_id' => $cab2_1->id,
            'estado' => 'activo',
        ]);
        Cabecera::create([
            'descripcion' => 'Femenino',
            'variable_id' => $var2->id,
            'cabecera_id' => $cab2_1->id,
            'estado' => 'activo',
        ]);

        // CABECERAS para Vacunaciones (raíz)
        Cabecera::create([
            'descripcion' => 'Total Vacunaciones',
            'variable_id' => $var3->id,
            'cabecera_id' => null, // raíz
            'estado' => 'activo',
        ]);

        // FORMULARIOS para diferentes gestiones
        foreach ([$SDIS, $Vigilancia, $Hospitales] as $sistema) {
            $form2026 = Formulario::create([
                'descripcion' => 'Reporte Mensual 2026',
                'gestion_id' => 7, // 2026
                'sistema_id' => $sistema->id,
            ]);
            $form2025 = Formulario::create([
                'descripcion' => 'Reporte Anual 2025',
                'gestion_id' => 6, // 2025
                'sistema_id' => $sistema->id,
            ]);
            $form2024 = Formulario::create([
                'descripcion' => 'Reporte Semestral 2024',
                'gestion_id' => 5, // 2024
                'sistema_id' => $sistema->id,
            ]);
    
            // ASIGNACIONES - Variables asignadas a Formularios
            Asignacion::create([
                'variable_id' => $var1->id,
                'formulario_id' => $form2026->id,
            ]);
            Asignacion::create([
                'variable_id' => $var2->id,
                'formulario_id' => $form2026->id,
            ]);
            Asignacion::create([
                'variable_id' => $var3->id,
                'formulario_id' => $form2026->id,
            ]);
    
            Asignacion::create([
                'variable_id' => $var1->id,
                'formulario_id' => $form2025->id,
            ]);
            Asignacion::create([
                'variable_id' => $var4->id,
                'formulario_id' => $form2025->id,
            ]);
            Asignacion::create([
                'variable_id' => $var5->id,
                'formulario_id' => $form2025->id,
            ]);
    
            Asignacion::create([
                'variable_id' => $var2->id,
                'formulario_id' => $form2024->id,
            ]);
            Asignacion::create([
                'variable_id' => $var5->id,
                'formulario_id' => $form2024->id,
            ]);
        }

        $formularios = Formulario::all();
        $establecimientos = Establecimiento::pluck('id');

        $formularios->each(function ($formulario) use ($establecimientos) {
            $formulario->establecimientos()->syncWithoutDetaching($establecimientos);
        });

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
            ['name' => 'Enviar formularios', 'slug' => 'formularios.submit', 'description' => 'Subir formularios'],

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
        $superAdmin->permissions()->detach([5, 10]);
        $admin->permissions()->sync($allPermissions);
        $admin->permissions()->detach([3, 4, 7, 5, 10]);

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
                'sistema_id' => $SDIS->id
            ]
        );
        // Asignar rol
        $adminSDIS->assignRole('admin');
        $adminSDIS->sistemas()->sync([$SDIS->id]);
        
        $adminHospitales = User::firstOrCreate(
            ['name' => 'Administrador Hospitales'],
            [
                'password' => bcrypt('admin1234'),
                'sistema_id' => $Hospitales->id
            ]
        );
        // Asignar rol
        $adminHospitales->assignRole('admin');
        $adminHospitales->sistemas()->sync([$Hospitales->id]);

        $adminVigilancia = User::firstOrCreate(
            ['name' => 'Administrador Vigilancia'],
            [
                'password' => bcrypt('admin1234'),
                'sistema_id' => $Vigilancia->id
            ]
        );
        // Asignar rol
        $adminVigilancia->assignRole('admin');
        $adminVigilancia->sistemas()->sync([$Vigilancia->id]);
    }
}
