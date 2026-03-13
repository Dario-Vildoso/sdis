<?php

use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RedController;
use App\Http\Controllers\FormularioController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\VariableController;
use App\Http\Controllers\SubVariableController;
use App\Http\Controllers\CabeceraController;
use App\Http\Controllers\DatoController;
use App\Http\Controllers\GestionController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use League\CommonMark\Extension\CommonMark\Parser\Inline\EscapableParser;

Route::get('/', function () {
    return view('welcome');
})->middleware('auth');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::post('roles/{role}/assign-user', [RoleController::class, 'assignToUser'])->name('roles.assignToUser');
    Route::delete('roles/{role}/remove-user', [RoleController::class, 'removeFromUser'])->name('roles.removeFromUser');
    
    Route::resource('establecimientos', EstablecimientoController::class);
    Route::resource('gestiones', GestionController::class);
    Route::resource('formularios', FormularioController::class);
    Route::resource('periodos', PeriodoController::class);
    Route::patch('periodos/{periodo}/toggle', [PeriodoController::class, 'toggle'])->name('periodos.toggle');
    Route::resource('variables', VariableController::class);
    Route::get('variables/{variable}/subvariables', [VariableController::class, 'getSubvariables'])->name('variables.subvariables');
    Route::get('variables/{variable}/cabeceras', [VariableController::class, 'getCabeceras'])->name('variables.cabeceras');
    Route::resource('subvariables', SubVariableController::class);
    Route::resource('cabeceras', CabeceraController::class);
    Route::get('formulario/{formulario}/asignar-variables', [FormularioController::class, 'asignarVariables'])->name('formularios.asignarVariables');
    Route::get('variables/{variable}/tabla', [VariableController::class, 'tabla'])->name('variables.tabla');
    Route::post('variables/{variable}/tabla', [VariableController::class, 'guardarTabla'])->name('variables.guardarTabla');
});

require __DIR__.'/auth.php';
