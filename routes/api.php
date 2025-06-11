<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * ------------------------------------------------------------------------
 * auth routes
 * ------------------------------------------------------------------------
 */
Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::get('auth/verify', [
    \App\Http\Controllers\AuthController::class,
    'verify',
]);

Route::middleware('auth:sanctum')->group(function () {
    /**
     * ------------------------------------------------------------------------
     * common routes
     * ------------------------------------------------------------------------
     */
    Route::get('logout', [
        \App\Http\Controllers\AuthController::class,
        'logout',
    ]);
    Route::get('user', function (Request $request) {
        return $request->user();
    });

    /**
     * ------------------------------------------------------------------------
     * users routes
     * ------------------------------------------------------------------------
     */
    Route::get('users', [
        \App\Http\Controllers\UserController::class,
        'index',
    ])->middleware('permission:users-all|users-view');

    Route::post('users', [
        \App\Http\Controllers\UserController::class,
        'store',
    ])->middleware('permission:users-all|users-create');

    Route::patch('users/{userId}', [
        \App\Http\Controllers\UserController::class,
        'update',
    ])->middleware('permission:users-all|users-edit');

    Route::delete('users/{userId}', [
        \App\Http\Controllers\UserController::class,
        'destroy',
    ])->middleware('permission:users-all|users-delete');

    /**
     * ------------------------------------------------------------------------
     * roles routes
     * ------------------------------------------------------------------------
     */
    Route::get('roles', [
        \App\Http\Controllers\RoleController::class,
        'index',
    ])->middleware('permission:roles-all|roles-view');

    Route::post('roles', [
        \App\Http\Controllers\RoleController::class,
        'store',
    ])->middleware('permission:roles-all|roles-create');

    Route::patch('roles/{roleId}', [
        \App\Http\Controllers\RoleController::class,
        'update',
    ])->middleware('permission:roles-all|roles-edit');

    Route::delete('roles/{roleId}', [
        \App\Http\Controllers\RoleController::class,
        'destroy',
    ])->middleware('permission:roles-all|roles-delete');

    /**
     * ------------------------------------------------------------------------
     * permissions routes
     * ------------------------------------------------------------------------
     */
    Route::get('permissions', [
        \App\Http\Controllers\PermissionController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('permissions', [
        \App\Http\Controllers\PermissionController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('permissions/{permissionId}', [
        \App\Http\Controllers\PermissionController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('permissions/{permissionId}', [
        \App\Http\Controllers\PermissionController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');


    // RUTA PARA DE PAGOS
    Route::get('pago', [
        \App\Http\Controllers\PagoController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('pago', [
        \App\Http\Controllers\PagoController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('pago/{pagoId}', [
        \App\Http\Controllers\PagoController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('pago/{pagoId}', [
        \App\Http\Controllers\PagoController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');


    // RUTAS DE CONVENIOS
    Route::get('convenio', [
        \App\Http\Controllers\ConveniosController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('convenio', [
        \App\Http\Controllers\ConveniosController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('convenio/{id}', [
        \App\Http\Controllers\ConveniosController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('convenio/{convenioId}', [
        \App\Http\Controllers\ConveniosController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');


    //RUTA DE ESTUDIANTE
    Route::get('estudiante', [
        \App\Http\Controllers\EstudianteController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('estudiante', [
        \App\Http\Controllers\EstudianteController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('estudiante/{id}', [
        \App\Http\Controllers\EstudianteController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('estudiante/{id}', [
        \App\Http\Controllers\EstudianteController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');


    //RUTA PARA CILCLO ACADEMICO
    Route::get('ciclo_academico', [
        \App\Http\Controllers\CicloAcademicoController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('ciclo_academico', [
        \App\Http\Controllers\CicloAcademicoController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('ciclo_academico/{id}', [
        \App\Http\Controllers\CicloAcademicoController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('ciclo_academico/{id}', [
        \App\Http\Controllers\CicloAcademicoController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');


    // RUTA PARA CALENDARIO-ADMIN
    Route::get('calendario_admin', [
        \App\Http\Controllers\CalendarioAdminController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('calendario_admin', [
        \App\Http\Controllers\CalendarioAdminController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('calendario_admin/{id}', [
        \App\Http\Controllers\CalendarioAdminController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('calendario_admin/{id}', [
        \App\Http\Controllers\CalendarioAdminController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');


    // RUTA PARA ENTREGA_DOCENTE_ADMIN
    Route::get('entrega_docente_admin', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('entrega_docente_admin', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('entrega_docente_admin/{id}', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('entrega_docente_admin/{id}', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');


    // RUTA PARA ESPECIALIDAD_MADRE
    Route::get('especialidad_madre', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('especialidad_madre', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('especialidad_madre/{id}', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('especialidad_madre/{id}', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');


    // RUTA PARA PROGRAMA_ESTUDIO
    Route::get('programa_estudio', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('programa_estudio', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('programa_estudio/{id}', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('programa_estudio/{id}', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');


    // RUTA PARA ESPECIALIDAD_PROGRAMA
    Route::get('especialidad_programa', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('especialidad_programa', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('especialidad_programa/{id}', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('especialidad_programa/{id}', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');


    // RUTA PARA DOCENTE
    Route::get('docente', [
        \App\Http\Controllers\DocenteController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('docente', [
        \App\Http\Controllers\DocenteController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('docente/{id}', [
        \App\Http\Controllers\DocenteController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('docente/{id}', [
        \App\Http\Controllers\DocenteController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');


    // RUTA PARA PERIODO
    Route::get('periodo', [
        \App\Http\Controllers\PeriodoController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('periodo', [
        \App\Http\Controllers\PeriodoController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('periodo/{id}', [
        \App\Http\Controllers\PeriodoController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('periodo/{id}', [
        \App\Http\Controllers\PeriodoController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');
});
