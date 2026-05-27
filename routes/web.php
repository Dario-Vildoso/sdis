 <?php
 
 use App\Http\Controllers\EstablecimientoController;
 use App\Http\Controllers\ProfileController;
 use App\Http\Controllers\UserController;
 use App\Http\Controllers\RedController;
 use App\Http\Controllers\MunicipioController;
 use App\Http\Controllers\FormularioController;
 use App\Http\Controllers\ReporteController;
 use App\Http\Controllers\ExportacionController;
 use App\Http\Controllers\AsignacionController;
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
    // Usuarios
    Route::get('users', [UserController::class, 'index'])->middleware('permission:users.view')->name('users.index');
    Route::post('users', [UserController::class, 'store'])->middleware('permission:users.management')->name('users.store');
    Route::put('users/{user}', [UserController::class, 'update'])->middleware('permission:users.management')->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.management')->name('users.destroy');
    Route::put('users/{user}/assignsystem', [UserController::class, 'assignSystem'])->middleware('permission:users.management')->name('users.assignsystem');
    Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->middleware('permission:users.management')->name('users.toggle');
    Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->middleware('permission:users.management')->name('users.resetPassword');

    // Roles
    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.view')->name('roles.index');
    Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.view')->name('roles.show');
    Route::get('users/create', [RoleController::class, 'create'])->middleware('permission:roles.management')->name('roles.create');
    Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.management')->name('roles.store');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->middleware('permission:roles.management')->name('roles.edit');
    Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.management')->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.management')->name('roles.destroy');
    Route::post('roles/{role}/assign-user', [RoleController::class, 'assignToUser'])->middleware('permission:roles.management')->name('roles.assignToUser');
    Route::delete('roles/{role}/remove-user', [RoleController::class, 'removeFromUser'])->middleware('permission:roles.management')->name('roles.removeFromUser');
    
    // Establecimientos
    Route::get('establecimientos', [EstablecimientoController::class, 'index'])->middleware('permission:establecimientos.view')->name('establecimientos.index');
    Route::post('establecimientos', [EstablecimientoController::class, 'store'])->middleware('permission:establecimientos.management')->name('establecimientos.store');
    Route::put('establecimientos/{establecimiento}', [EstablecimientoController::class, 'update'])->middleware('permission:establecimientos.management')->name('establecimientos.update');
    Route::delete('establecimientos/{establecimiento}', [EstablecimientoController::class, 'destroy'])->middleware('permission:establecimientos.management')->name('establecimientos.destroy');
    Route::patch('establecimientos/{establecimiento}/toggle', [EstablecimientoController::class, 'toggle'])->middleware('permission:establecimientos.management')->name('establecimientos.toggle');
    
    // Municipios
    Route::get('municipios', [MunicipioController::class, 'index'])->middleware('permission:municipios.view')->name('municipios.index');
    Route::post('municipios', [MunicipioController::class, 'store'])->middleware('permission:municipios.management')->name('municipios.store');
    Route::put('municipios/{municipio}', [MunicipioController::class, 'update'])->middleware('permission:municipios.management')->name('municipios.update');
    Route::delete('municipios/{municipio}', [MunicipioController::class, 'destroy'])->middleware('permission:municipios.management')->name('municipios.destroy');
    
    // Redes
    Route::get('redes', [RedController::class, 'index'])->middleware('permission:reds.view')->name('redes.index');
    Route::post('redes', [RedController::class, 'store'])->middleware('permission:reds.management')->name('redes.store');
    Route::put('redes/{red}', [RedController::class, 'update'])->middleware('permission:reds.management')->name('redes.update');
    Route::delete('redes/{red}', [RedController::class, 'destroy'])->middleware('permission:reds.management')->name('redes.destroy');
    
    // Formularios
    Route::get('formularios', [FormularioController::class, 'index'])->middleware('permission:formularios.view')->name('formularios.index');
    Route::post('formularios', [FormularioController::class, 'store'])->middleware('permission:formularios.management')->name('formularios.store');
    Route::put('formularios/{formulario}', [FormularioController::class, 'update'])->middleware('permission:formularios.management')->name('formularios.update');
    Route::delete('formularios/{formulario}', [FormularioController::class, 'destroy'])->middleware('permission:formularios.management')->name('formularios.destroy');
    Route::post('/formularios/add-variable', [FormularioController::class, 'addVariable'])->middleware('permission:formularios.management')->name('formularios.addVariable');
    Route::get('formulario/{formulario}/asignar-variables', [FormularioController::class, 'asignarVariables'])->middleware('permission:formularios.management')->name('formularios.asignarVariables');
    Route::post('/formularios/remove-variable', [FormularioController::class, 'removeVariable'])->middleware('permission:formularios.management')->name('formularios.removeVariable');
    Route::get('/formularios/{formulario}/establecimientos', [FormularioController::class, 'establecimientosAsignados'])->middleware('permission:formularios.view')->name('formularios.establecimientosAsignados');
    Route::post('/formularios/{formulario}/establecimientos', [FormularioController::class, 'asignarEstablecimientos'])->middleware('permission:formularios.management')->name('formularios.asignarEstablecimientos');
    Route::post('/asignaciones/create-variable-form/{form}', [AsignacionController::class, 'createVariableForm'])->middleware('permission:formularios.management')->name('asignaciones.createVarForm');

    // Periodos
    Route::get('periodos', [PeriodoController::class, 'index'])->middleware('permission:periodos.view')->name('periodos.index');
    Route::post('periodos', [PeriodoController::class, 'store'])->middleware('permission:periodos.management')->name('periodos.store');
    Route::put('periodos/{periodo}', [PeriodoController::class, 'update'])->middleware('permission:periodos.management')->name('periodos.update');
    Route::delete('periodos/{periodo}', [PeriodoController::class, 'destroy'])->middleware('permission:periodos.management')->name('periodos.destroy');
    Route::patch('periodos/{periodo}/toggle', [PeriodoController::class, 'toggle'])->middleware('permission:periodos.management')->name('periodos.toggle');

    // Variables
    Route::get('variables', [VariableController::class, 'index'])->middleware('permission:variables.view')->name('variables.index');
    Route::post('variables', [VariableController::class, 'store'])->middleware('permission:variables.management')->name('variables.store');
    Route::put('variables/{periodo}', [VariableController::class, 'update'])->middleware('permission:variables.management')->name('variables.update');
    Route::delete('variables/{periodo}', [VariableController::class, 'destroy'])->middleware('permission:variables.management')->name('variables.destroy');
    Route::get('subvariables', [SubVariableController::class, 'index'])->middleware('permission:variables.view')->name('subvariables.index');
    Route::post('subvariables', [SubVariableController::class, 'store'])->middleware('permission:variables.management')->name('subvariables.store');
    Route::put('subvariables/{periodo}', [SubVariableController::class, 'update'])->middleware('permission:variables.management')->name('subvariables.update');
    Route::delete('subvariables/{periodo}', [SubVariableController::class, 'destroy'])->middleware('permission:variables.management')->name('subvariables.destroy');
    Route::get('cabeceras', [CabeceraController::class, 'index'])->middleware('permission:variables.view')->name('cabeceras.index');
    Route::post('cabeceras', [CabeceraController::class, 'store'])->middleware('permission:variables.management')->name('cabeceras.store');
    Route::put('cabeceras/{periodo}', [CabeceraController::class, 'update'])->middleware('permission:variables.management')->name('cabeceras.update');
    Route::delete('cabeceras/{periodo}', [CabeceraController::class, 'destroy'])->middleware('permission:variables.management')->name('cabeceras.destroy');
    Route::get('variables/{variable}/subvariables', [VariableController::class, 'getSubvariables'])->middleware('permission:variables.management')->name('variables.subvariables');
    Route::get('variables/{variable}/cabeceras', [VariableController::class, 'getCabeceras'])->middleware('permission:variables.management')->name('variables.cabeceras');
    Route::get('variables/{variable}/tabla', [VariableController::class, 'tabla'])->middleware('permission:variables.management')->name('variables.tabla');
    Route::post('variables/{variable}/tabla', [VariableController::class, 'guardarTabla'])->middleware('permission:variables.management')->name('variables.guardarTabla');

    // Exportaciones
    Route::get('exportaciones', [ExportacionController::class, 'index'])->middleware('permission:exportaciones')->name('exportaciones.index');
    Route::post('exportaciones', [ExportacionController::class, 'export'])->middleware('permission:exportaciones')->name('exportaciones.export');

    // Gestiones
    Route::get('gestiones', [GestionController::class, 'index'])->middleware('permission:gestiones.view')->name('gestiones.index');
    Route::post('gestiones', [GestionController::class, 'store'])->middleware('permission:gestiones.management')->name('gestiones.store');
    Route::put('gestiones/{gestion}', [GestionController::class, 'update'])->middleware('permission:gestiones.management')->name('gestiones.update');
    Route::delete('gestiones/{gestion}', [GestionController::class, 'destroy'])->middleware('permission:gestiones.management')->name('gestiones.destroy');

    // Oportunidad
    Route::get('oportunidad-informacion', [ReporteController::class, 'oportunidad'])->middleware('permission:oportunidad')->name('reportes.oportunidad');

    // Subida de datos
    Route::get('formulario/menu', [FormularioController::class, 'menu'])->middleware('permission:formularios.submit')->name('formularios.menu');
    Route::get('formulario/{formulario}/periodo/{periodo}', [FormularioController::class, 'cargarPeriodo'])->middleware('permission:formularios.submit')->name('formularios.periodo');
    Route::post('formulario/{formulario}/periodo/{periodo}', [FormularioController::class, 'storeData'])->middleware('permission:formularios.submit')->name('formularios.guardarPeriodo');

    // Reportes
    Route::get('reportes', [ReporteController::class, 'index'])->middleware('permission:reportes')->name('reportes.index');
    Route::get('reportes-tabulator', [ReporteController::class, 'tabulator'])->middleware('permission:reportes')->name('reportes.tabulator');
    Route::get('reportes/export/{variable}', [ReporteController::class, 'exportVariable'])->middleware('permission:exportaciones')->name('reportes.export');
    Route::get('reportes/pivote', [ReporteController::class, 'pivote'])->middleware('permission:reportes')->name('reportes.pivote');
    Route::get('reportes/pivote/metadata', [ReporteController::class, 'pivoteMetadata'])->middleware('permission:reportes')->name('reportes.pivote.metadata');
    Route::post('reportes/pivote/datos', [ReporteController::class, 'pivoteDatos'])->middleware('permission:reportes')->name('reportes.pivote.datos');
    });
    

 
 require __DIR__.'/auth.php';
