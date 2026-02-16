<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Red;
use App\Models\Municipio;
use App\Models\Establecimiento;
use App\Models\Periodo;
use App\Models\Variable;
use App\Models\Cabecera;
use App\Models\SubVariable;
use App\Models\Dato;
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

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin1234'),
        ]);

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
            'codigo_est' => 'CSC001',
            'gestion' => 2026,
        ]);
        Establecimiento::create([
            'descripcion' => 'Puesto de Salud Tablas',
            'id_mun' => 2,
            'codigo_est' => 'PST001',
            'gestion' => 2026,
        ]);
        Establecimiento::create([
            'descripcion' => 'Centro de Salud San Lucas',
            'id_mun' => 28,
            'codigo_est' => 'CSL001',
            'gestion' => 2026,
        ]);
        Establecimiento::create([
            'descripcion' => 'Centro de Salud Padilla',
            'id_mun' => 8,
            'codigo_est' => 'CSP001',
            'gestion' => 2026,
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
        SubVariable::create(['descripcion' => 'Consultas Generales', 'variable_id' => $var1->id]);
        SubVariable::create(['descripcion' => 'Consultas Especializadas', 'variable_id' => $var1->id]);
        SubVariable::create(['descripcion' => 'Consultas Odontológicas', 'variable_id' => $var1->id]);

        // SUBVARIABLES para Internaciones
        SubVariable::create(['descripcion' => 'Internaciones Médicas', 'variable_id' => $var2->id]);
        SubVariable::create(['descripcion' => 'Internaciones Quirúrgicas', 'variable_id' => $var2->id]);

        // SUBVARIABLES para Vacunaciones
        SubVariable::create(['descripcion' => 'BCG', 'variable_id' => $var3->id]);
        SubVariable::create(['descripcion' => 'Pentavalente', 'variable_id' => $var3->id]);
        SubVariable::create(['descripcion' => 'OPV', 'variable_id' => $var3->id]);
        SubVariable::create(['descripcion' => 'SRP', 'variable_id' => $var3->id]);

        // SUBVARIABLES para Partos
        SubVariable::create(['descripcion' => 'Partos Vaginales', 'variable_id' => $var4->id]);
        SubVariable::create(['descripcion' => 'Partos por Cesárea', 'variable_id' => $var4->id]);

        // SUBVARIABLES para Atenciones Preventivas
        SubVariable::create(['descripcion' => 'Control Prenatal', 'variable_id' => $var5->id]);
        SubVariable::create(['descripcion' => 'Control Postnatal', 'variable_id' => $var5->id]);
        SubVariable::create(['descripcion' => 'Planificación Familiar', 'variable_id' => $var5->id]);

        // CABECERAS para Consultas Externas (raíces y anidadas)
        $cab1 = Cabecera::create([
            'descripcion' => 'Total Consultas',
            'variable_id' => $var1->id,
            'cabecera_id' => null, // raíz
        ]);
        $cab1_1 = Cabecera::create([
            'descripcion' => 'Consultas por Sexo',
            'variable_id' => $var1->id,
            'cabecera_id' => $cab1->id,
        ]);
        Cabecera::create([
            'descripcion' => 'Masculino',
            'variable_id' => $var1->id,
            'cabecera_id' => $cab1_1->id,
        ]);
        Cabecera::create([
            'descripcion' => 'Femenino',
            'variable_id' => $var1->id,
            'cabecera_id' => $cab1_1->id,
        ]);

        // CABECERAS para Internaciones
        $cab2 = Cabecera::create([
            'descripcion' => 'Total Internaciones',
            'variable_id' => $var2->id,
            'cabecera_id' => null, // raíz
        ]);
        $cab2_1 = Cabecera::create([
            'descripcion' => 'Internaciones por Sexo',
            'variable_id' => $var2->id,
            'cabecera_id' => $cab2->id,
        ]);
        Cabecera::create([
            'descripcion' => 'Masculino',
            'variable_id' => $var2->id,
            'cabecera_id' => $cab2_1->id,
        ]);
        Cabecera::create([
            'descripcion' => 'Femenino',
            'variable_id' => $var2->id,
            'cabecera_id' => $cab2_1->id,
        ]);

        // CABECERAS para Vacunaciones (raíz)
        Cabecera::create([
            'descripcion' => 'Total Vacunaciones',
            'variable_id' => $var3->id,
            'cabecera_id' => null, // raíz
        ]);

        // FORMULARIOS para diferentes gestiones
        $form2026 = Formulario::create([
            'nombre' => 'Reporte Mensual 2026',
            'gestion_id' => 7, // 2026
        ]);
        $form2025 = Formulario::create([
            'nombre' => 'Reporte Anual 2025',
            'gestion_id' => 6, // 2025
        ]);
        $form2024 = Formulario::create([
            'nombre' => 'Reporte Semestral 2024',
            'gestion_id' => 5, // 2024
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
}
