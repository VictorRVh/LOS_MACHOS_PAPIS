<?php

use App\Http\Controllers\CarpetasGrupoDriveController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleDriveController;
use App\Models\CarpetasPeriodoDrive;

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
Route::post('auth/reset_password', [
    \App\Http\Controllers\AuthController::class,
    'cambiarPasswordPrimeraVez',
]);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('logout', [\App\Http\Controllers\AuthController::class, 'logout']);
//     Route::get('user', function (Request $request) {
//         return $request->user();
//     });

//     // Permitir cambiar la contraseña inicial
//     Route::post('auth/reset_password', [
//         \App\Http\Controllers\AuthController::class,
//         'cambiarPasswordPrimeraVez',
//     ]);
// });

// Route::middleware('auth:sanctum', 'password.cambiada')->group(function () {


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

    //RUTA PARA CAMBIAR CONTRASEÑA



    /**
     * ------------------------------------------------------------------------
     * users routes
     * ------------------------------------------------------------------------
     */
    Route::get('users', [
        \App\Http\Controllers\UserController::class,
        'index',
    ])->middleware('permission:todo-acceso-usuarios|ver-usuarios');
    Route::get('users_active', [
        \App\Http\Controllers\UserController::class,
        'index_filter_status',
    ])->middleware('permission:todo-acceso-usuarios|ver-usuarios');

    Route::post('users', [
        \App\Http\Controllers\UserController::class,
        'store',
    ])->middleware('permission:todo-acceso-usuarios|crear-usuarios');

    Route::patch('users/{userId}', [
        \App\Http\Controllers\UserController::class,
        'update',
    ])->middleware('permission:todo-acceso-usuarios|editar-usuarios');

    Route::delete('users/{userId}', [
        \App\Http\Controllers\UserController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-usuarios|users-delete');

    Route::patch('users-update-password/{id}', [
        \App\Http\Controllers\UserController::class,
        'updatePassword',
    ])->middleware('permission:todo-acceso-usuarios|crear-usuarios');

    Route::patch('users-update/{id}', [
        \App\Http\Controllers\UserController::class,
        'updateProfile',
    ])->middleware('permission:todo-acceso-usuarios|crear-usuarios');

    /**
     * ------------------------------------------------------------------------
     * roles routes
     * ------------------------------------------------------------------------
     */
    Route::get('roles', [
        \App\Http\Controllers\RoleController::class,
        'index',
    ])->middleware('permission:todo-acceso-roles|ver-roles');

    Route::post('roles', [
        \App\Http\Controllers\RoleController::class,
        'store',
    ])->middleware('permission:todo-acceso-roles|crear-roles');

    Route::patch('roles/{roleId}', [
        \App\Http\Controllers\RoleController::class,
        'update',
    ])->middleware('permission:todo-acceso-roles|editar-roles');

    Route::delete('roles/{roleId}', [
        \App\Http\Controllers\RoleController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-roles|roles-delete');

    /**
     * ------------------------------------------------------------------------
     * permissions routes
     * ------------------------------------------------------------------------
     */
    Route::get('permissions', [
        \App\Http\Controllers\PermissionController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('permissions', [
        \App\Http\Controllers\PermissionController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('permissions/{permissionId}', [
        \App\Http\Controllers\PermissionController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('permissions/{permissionId}', [
        \App\Http\Controllers\PermissionController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');


    // RUTA PARA DE PAGOS
    Route::get('pago', [
        \App\Http\Controllers\PagoController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('pago', [
        \App\Http\Controllers\PagoController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('pago/{pagoId}', [
        \App\Http\Controllers\PagoController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('pago/{pagoId}', [
        \App\Http\Controllers\PagoController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');


    // RUTAS DE CONVENIOS
    Route::get('convenio', [
        \App\Http\Controllers\ConveniosController::class,
        'index',
    ])->middleware('permission:todo-acceso-convenios|ver-convenios');

    Route::post('convenio', [
        \App\Http\Controllers\ConveniosController::class,
        'store',
    ])->middleware('permission:todo-acceso-convenios|crear-convenios');

    Route::patch('convenio/{id}', [
        \App\Http\Controllers\ConveniosController::class,
        'update',
    ])->middleware('permission:todo-acceso-convenios|editar-convenios');

    Route::delete('convenio/{convenioId}', [
        \App\Http\Controllers\ConveniosController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-convenios|eliminar-convenios');


    //RUTA DE ESTUDIANTE
    Route::get('estudiante', [
        \App\Http\Controllers\EstudianteController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('estudiante', [
        \App\Http\Controllers\EstudianteController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('estudiante/{id}', [
        \App\Http\Controllers\EstudianteController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('estudiante/{id}', [
        \App\Http\Controllers\EstudianteController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');

    //BUSCAR DNI
    Route::post('buscar-documento', [
        \App\Http\Controllers\EstudianteController::class,
        'buscar',
    ])->middleware('permission:todo-acceso-matriculas|ver-matriculas');


    //RUTA PARA CILCLO ACADEMICO
    Route::get('ciclo_academico', [
        \App\Http\Controllers\CicloAcademicoController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('ciclo_academico', [
        \App\Http\Controllers\CicloAcademicoController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('ciclo_academico/{id}', [
        \App\Http\Controllers\CicloAcademicoController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('ciclo_academico/{id}', [
        \App\Http\Controllers\CicloAcademicoController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');


    // RUTA PARA CALENDARIO-ADMIN
    Route::get('calendario_admin', [
        \App\Http\Controllers\CalendarioAdminController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('calendario_admin', [
        \App\Http\Controllers\CalendarioAdminController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('calendario_admin/{id}', [
        \App\Http\Controllers\CalendarioAdminController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('calendario_admin/{id}', [
        \App\Http\Controllers\CalendarioAdminController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');


    // RUTA PARA ENTREGA_DOCENTE_ADMIN
    Route::get('entrega_docente_admin', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'index',
    ])->middleware('permission:todo-acceso-documento-programado|ver-documento-programado');

    Route::get('programacion_admin/{id_periodo}', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'indexByPeriodo',
    ])->middleware('permission:todo-acceso-documento-programado|ver-documento-programado');

    Route::post('entrega_docente_admin', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'store',
    ])->middleware('permission:todo-acceso-documento-programado|crear-documento-programado');

    Route::patch('entrega_docente_admin/{id}', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'update',
    ])->middleware('permission:todo-acceso-documento-programado|editar-documento-programado');
    Route::patch('crear_grupos/{id}', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'updateSubGrupo',
    ])->middleware('permission:todo-acceso-documento-programado|editar-documento-programado');

    Route::delete('entrega_docente_admin/{id}', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-documento-programado|eliminar-documento-programado');

    /// ENTREGA DOCENTE
    Route::get('lista_programacion/{id_admin}', [
        \App\Http\Controllers\EntregaDocenteController::class,
        'subidasPorProgramacion',
    ])->middleware('permission:todo-acceso-programacion-documentos-subidos|ver-programacion-documentos-subidos');
    
    Route::patch('entrega_docente/{id}', [
        \App\Http\Controllers\EntregaDocenteController::class,
        'update',
    ])->middleware('permission:todo-acceso-programacion-documentos-subidos|editar-programacion-documentos-subidos');

    Route::delete('entrega_docente/{id}', [
        \App\Http\Controllers\EntregaDocenteController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-programacion-documentos-subidos|eliminar-programacion-documentos-subidos');


    Route::get('programacion_grupo/{id_grupo}', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'programacionesPorGrupo',
    ])->middleware('permission:todo-acceso-especialidades|ver-mis-modulos');

    // RUTA PARA ESPECIALIDAD_MADRE
    Route::get('especialidad_madre', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'index',
    ])->middleware('permission:todo-acceso-especialidades|ver-especialidades');

    Route::get('especialidad_ciclo/{id_ciclo}', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'getEspecialidadesPorCiclo',
    ])->middleware('permission:todo-acceso-especialidades|ver-especialidades');

    Route::post('especialidad_madre', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'store',
    ])->middleware('permission:todo-acceso-especialidades|crear-especialidades');

    Route::patch('especialidad_madre/{id}', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'update',
    ])->middleware('permission:todo-acceso-especialidades|editar-especialidades');

    Route::delete('especialidad_madre/{id}', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-especialidades|eliminar-especialidades');


    // RUTA PARA PROGRAMA_ESTUDIO
    Route::get('programa_estudio', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'index',
    ])->middleware('permission:todo-acceso-programas|ver-programas');

    Route::get('programa_estudio_status', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'index_filter_status',
    ])->middleware('permission:todo-acceso-programas|ver-programas');


    Route::post('programa_estudio', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'store',
    ])->middleware('permission:todo-acceso-programas|crear-programas');

    Route::patch('programa_estudio/{id}', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'update',
    ])->middleware('permission:todo-acceso-programas|editar-programas');

    Route::delete('programa_estudio/{id}', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-programas|eliminar-programas');


    // RUTA PARA ESPECIALIDAD_PROGRAMA
    Route::get('especialidad_programa', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'index',
    ])->middleware('permission:todo-acceso-programa-especialidades|ver-programa-especialidades');

    Route::get('especialidad_programa/{id}', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'show',
    ])->middleware('permission:todo-acceso-programa-especialidades|ver-programa-especialidades');

    Route::get('especialidad_programa/{id}/modulos', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'getRelacionadosPorEspecialidadPrograma',
    ])->middleware('permission:todo-acceso-programa-especialidades|ver-programa-especialidades');

    Route::post('especialidad_programa', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'store',
    ])->middleware('permission:todo-acceso-programa-especialidades|crear-programa-especialidades');

    Route::patch('especialidad_programa/{id}', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'update',
    ])->middleware('permission:todo-acceso-programa-especialidades|editar-programa-especialidades');

    Route::delete('especialidad_programa/{id}', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-programa-especialidades|eliminar-programa-especialidades');


    // RUTA PARA DOCENTE
    Route::get('docente', [
        \App\Http\Controllers\DocenteController::class,
        'index',
    ])->middleware('permission:todo-acceso-docentes|ver-docentes');

    Route::post('docente', [
        \App\Http\Controllers\DocenteController::class,
        'store',
    ])->middleware('permission:todo-acceso-docentes|crear-docentes');

    Route::patch('docente/{id}', [
        \App\Http\Controllers\DocenteController::class,
        'update',
    ])->middleware('permission:todo-acceso-docentes|editar-docentes');

    Route::delete('docente/{id}', [
        \App\Http\Controllers\DocenteController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-docentes|eliminar-docente');


    // RUTA PARA PERIODO
    Route::get('periodo', [
        \App\Http\Controllers\PeriodoController::class,
        'index',
    ])->middleware('permission:todo-acceso-periodos|ver-periodos');

    Route::get('periodo_filter_status', [
        \App\Http\Controllers\PeriodoController::class,
        'index_filter_status',
    ])->middleware('permission:todo-acceso-periodos|ver-periodos');

    Route::post('periodo', [
        \App\Http\Controllers\PeriodoController::class,
        'store',
    ])->middleware('permission:todo-acceso-eriodos|crear-periodos');

    Route::patch('periodo/{id}', [
        \App\Http\Controllers\PeriodoController::class,
        'update',
    ])->middleware('permission:todo-acceso-periodos|editar-periodos');

    Route::delete('periodo/{id}', [
        \App\Http\Controllers\PeriodoController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-periodos|eliminar-periodos');


    // RUTA PARA MODULO
    Route::get('modulo', [
        \App\Http\Controllers\ModuloController::class,
        'index',
    ])->middleware('permission:todo-acceso-modulos|ver-modulos');

    Route::get('modulo/{id}', [
        \App\Http\Controllers\ModuloController::class,
        'show',
    ])->middleware('permission:todo-acceso-modulos|ver-modulos');

    Route::post('modulo', [
        \App\Http\Controllers\ModuloController::class,
        'store',
    ])->middleware('permission:todo-acceso-modulos|crear-modulos');

    Route::patch('modulo/{id}', [
        \App\Http\Controllers\ModuloController::class,
        'update',
    ])->middleware('permission:todo-acceso-modulos|editar-modulos');

    Route::delete('modulo/{id}', [
        \App\Http\Controllers\ModuloController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-modulos|eliminar-modulos');


    // RUTA PARA GRUPO
    Route::get('grupo', [
        \App\Http\Controllers\GrupoController::class,
        'index',
    ])->middleware('permission:todo-acceso-grupos|ver-grupos');

    Route::post('grupo', [
        \App\Http\Controllers\GrupoController::class,
        'store',
    ])->middleware('permission:todo-acceso-grupos|crear-grupos');

    Route::patch('grupo/{id}', [
        \App\Http\Controllers\GrupoController::class,
        'update',
    ])->middleware('permission:todo-acceso-grupos|editar-grupos');

    Route::delete('grupo/{id}', [
        \App\Http\Controllers\GrupoController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-grupos|eliminar-grupos');

    // Rutas para el formulario
    Route::get('especialidadByPrograma/{id}', [
        \App\Http\Controllers\GrupoController::class,
        'getEspecialidadesPorPrograma',
    ])->middleware('permission:ver-programas');

    Route::get('moduloByEspecialidad/{id}', [
        \App\Http\Controllers\GrupoController::class,
        'getModulosPorEspecialidad',
    ])->middleware('permission:ver-programa-especialidades');

    Route::get('periodoByModulo/{id}', [
        \App\Http\Controllers\GrupoController::class,
        'getPeriodoPorModulo',
    ])->middleware('permission:ver-periodos');

    // grupo-docente
    Route::get('docenteGrupo', [
        \App\Http\Controllers\GrupoController::class,
        'docentesPorGrupo',
    ])->middleware('permission:ver-docentes');

    // grupos filtrados
    Route::get('gruposFiltrados', [
        \App\Http\Controllers\GrupoController::class,
        'gruposPorCicloAnioPeriodo',
    ])->middleware('permission:ver-grupos');

    // Rutas nuevas para filtro de grupo
    Route::get('aniosByCiclo/{idCiclo}', [
        \App\Http\Controllers\GrupoController::class,
        'getAniosPorCiclo',
    ])->middleware('permission:ver-grupos');

    Route::get('periodoByAnio/{idAnio}', [
        \App\Http\Controllers\GrupoController::class,
        'getPeriodosPorAnio',
    ])->middleware('permission:ver-grupos');

    // para filtros de lista de grupos

    Route::get('periodoByCiclo/{cicloId}', [
        \App\Http\Controllers\GrupoController::class,
        'getPeriodosPorCiclo',
    ])->middleware('permission:ver-grupos');

    // lista de grupos
    Route::get('gruposMatricula', [
        \App\Http\Controllers\GrupoController::class,
        'getGruposPorCicloYPeriodo',
    ])->middleware('permission:ver-grupos');

    // Lista de grupos disponibles para cambio de grupo
    Route::get('gruposDisponibles', [
        \App\Http\Controllers\GrupoController::class,
        'gruposDisponibles',
    ])->middleware('permission:ver-grupos');

    //RUTA PARA CAPACIDAD TERMINAL
    Route::get('capacidad_terminal', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'index',
    ])->middleware('permission:todo-acceso-capacidad-terminal-docente|ver-capacidad-terminal-docente');
    Route::get('capacidad_terminal/{id}', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'indexGrupo',
    ])->middleware('permission:todo-acceso-capacidad-terminal-docente|ver-capacidad-terminal-docente');

    Route::post('capacidad_terminal', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'store',
    ])->middleware('permission:todo-acceso-capacidad-terminal-docente|crear-capacidad-terminal-docente');

    Route::patch('capacidad_terminal/{id}', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'update',
    ])->middleware('permission:todo-acceso-capacidad-terminal-docente|editar-capacidad-terminal-docente');

    Route::delete('capacidad_terminal/{id}', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-capacidad-terminal-docente|eliminar-capacidad-terminal-docente');


    // RUTA PARA NOTA DE CAPACIDAD TERMINAL
    Route::get('nota_capacidad_terminal', [
        \App\Http\Controllers\NotaCapacidadTerminalController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('nota_capacidad_terminal', [
        \App\Http\Controllers\NotaCapacidadTerminalController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('nota_capacidad_terminal/{id}', [
        \App\Http\Controllers\NotaCapacidadTerminalController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('nota_capacidad_terminal/{id}', [
        \App\Http\Controllers\NotaCapacidadTerminalController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');


    // RUTA PARA EXPERIENCIA FORMATIVA
    Route::get('experiencia_formativa', [
        \App\Http\Controllers\ExperienciaFormativaController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('experiencia_formativa', [
        \App\Http\Controllers\ExperienciaFormativaController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('experiencia_formativa/{id}', [
        \App\Http\Controllers\ExperienciaFormativaController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('experiencia_formativa/{id}', [
        \App\Http\Controllers\ExperienciaFormativaController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');


    // RUTA PARA NOTA DE EXPERIENCIA FORMATIVA
    Route::get('nota_experiencia_formativa', [
        \App\Http\Controllers\NotaExperienciaFormativaController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('nota_experiencia_formativa', [
        \App\Http\Controllers\NotaExperienciaFormativaController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('nota_experiencia_formativa/{id}', [
        \App\Http\Controllers\NotaExperienciaFormativaController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('nota_experiencia_formativa/{id}', [
        \App\Http\Controllers\NotaExperienciaFormativaController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');


  


    // RUTA PARA SESIONES
    Route::get('sesiones', [
        \App\Http\Controllers\SesionesController::class,
        'index',
    ])->middleware('permission:todo-acceso-sesiones-docente|ver-sesiones-docente');

        Route::get('sesion_docente/{id}', [
        \App\Http\Controllers\SesionesController::class,
        'indexOneSesion',
    ])->middleware('permission:todo-acceso-sesiones-docente|ver-sesiones-docente');

    Route::post('sesiones', [
        \App\Http\Controllers\SesionesController::class,
        'store',
    ])->middleware('permission:todo-acceso-sesiones-docente|crear-sesiones-docente');

    Route::patch('sesiones/{id}', [
        \App\Http\Controllers\SesionesController::class,
        'update',
    ])->middleware('permission:todo-acceso-sesiones-docente|editar-sesiones-docente');

    Route::delete('sesiones/{id}', [
        \App\Http\Controllers\SesionesController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-sesiones-docente|eliminar-sesiones-docente');


    // RUTA PARA ASISTENCIA
    Route::get('asistencia', [
        \App\Http\Controllers\AsistenciaController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('asistencia', [
        \App\Http\Controllers\AsistenciaController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('asistencia/{id}', [
        \App\Http\Controllers\AsistenciaController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('asistencia/{id}', [
        \App\Http\Controllers\AsistenciaController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');


    // RUTA PARA ENTREGAS REALIZADAS
    Route::get('entregas_realizadas', [
        \App\Http\Controllers\EntregasRealizadasController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('entregas_realizadas', [
        \App\Http\Controllers\EntregasRealizadasController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('entregas_realizadas/{id}', [
        \App\Http\Controllers\EntregasRealizadasController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('entregas_realizadas/{id}', [
        \App\Http\Controllers\EntregasRealizadasController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');


    //RUTA PARA EGRESADOS
    Route::get('egresados', [
        \App\Http\Controllers\EgresadosController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('egresados', [
        \App\Http\Controllers\EgresadosController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('egresados/{id}', [
        \App\Http\Controllers\EgresadosController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('egresados/{id}', [
        \App\Http\Controllers\EgresadosController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');


    // RUTA PARA PERSONAL ADMINISTRATIVO
    Route::get('personal_administrativo', [
        \App\Http\Controllers\PersonalAdministrativoController::class,
        'index',
    ])->middleware('permission:todo-acceso-administrativos|ver-administrativos');

    Route::post('personal_administrativo', [
        \App\Http\Controllers\PersonalAdministrativoController::class,
        'store',
    ])->middleware('permission:todo-acceso-administrativos|crear-administrativos');

    Route::patch('personal_administrativo/{id}', [
        \App\Http\Controllers\PersonalAdministrativoController::class,
        'update',
    ])->middleware('permission:todo-acceso-administrativos|editar-administrativos');

    Route::delete('personal_administrativo/{id}', [
        \App\Http\Controllers\PersonalAdministrativoController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-administrativos|eliminar-administrativos');


    //RUTA PARA COMISIONES
    Route::get('comisiones', [
        \App\Http\Controllers\ComisionesController::class,
        'index',
    ])->middleware('permission:todo-acceso-comisiones|ver-comisiones');
    Route::get('comisiones_filter', [
        \App\Http\Controllers\ComisionesController::class,
        'index_filter',
    ])->middleware('permission:todo-acceso-comisiones|ver-comisiones');

    Route::post('comisiones', [
        \App\Http\Controllers\ComisionesController::class,
        'store',
    ])->middleware('permission:todo-acceso-comisiones|crear-comisiones');

    Route::patch('comisiones/{id}', [
        \App\Http\Controllers\ComisionesController::class,
        'update',
    ])->middleware('permission:todo-acceso-comisiones|editar-comisiones');

    Route::delete('comisiones/{id}', [
        \App\Http\Controllers\ComisionesController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-comisiones|eliminar-comisiones');


    //RUTA PARA NOTIFICACIONES
    Route::get('notificaciones', [
        \App\Http\Controllers\NotificacionesController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('notificaciones', [
        \App\Http\Controllers\NotificacionesController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('notificaciones/{id}', [
        \App\Http\Controllers\NotificacionesController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('notificaciones/{id}', [
        \App\Http\Controllers\NotificacionesController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');

    //RUTA PARA ACTIVIDADES RECIENTES
    Route::get('actividades_recientes', [
        \App\Http\Controllers\ActividadesRecientesController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('actividades_recientes', [
        \App\Http\Controllers\ActividadesRecientesController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('actividades_recientes/{id}', [
        \App\Http\Controllers\ActividadesRecientesController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('actividades_recientes/{id}', [
        \App\Http\Controllers\ActividadesRecientesController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');

    // RUTA COMISION USUARIO

    Route::get('comision_usuario', [
        \App\Http\Controllers\ComisionUsuarioController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('comision_usuario', [
        \App\Http\Controllers\ComisionUsuarioController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos');

    Route::patch('comision_usuario/{id}', [
        \App\Http\Controllers\ComisionUsuarioController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos');

    Route::delete('comision_usuario/{id}', [
        \App\Http\Controllers\ComisionUsuarioController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');

    // API MATRICULA

    Route::get('matricula', [
        \App\Http\Controllers\MatriculaController::class,
        'index',
    ])->middleware('permission:todo-acceso-matriculas|ver-matriculas');

    Route::post('matricula', [
        \App\Http\Controllers\MatriculaController::class,
        'store',
    ])->middleware('permission:todo-acceso-matriculas|crear-matriculas');

    Route::patch('matricula/{id}', [
        \App\Http\Controllers\MatriculaController::class,
        'update',
    ])->middleware('permission:todo-acceso-matriculas|editar-matriculas');

    Route::delete('matricula/{id}', [
        \App\Http\Controllers\MatriculaController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-matriculas|eliminar-matriculas');

    //matricula Reserva

    Route::patch('reservaMatricula/{id}', [
        \App\Http\Controllers\MatriculaController::class,
        'reservar',
    ])->middleware('permission:ver-matriculas');

    Route::get('listaReserva', [
        \App\Http\Controllers\MatriculaController::class,
        'matriculadosConReserva',
    ])->middleware('permission:ver-matriculas');


    // Alumnos matriculados (en modulo matricula)
    Route::get('matricula/{grupoId}', [
        \App\Http\Controllers\MatriculaController::class,
        'getMatriculadosPorGrupo',
    ])->middleware('permission:ver-matriculas');

    Route::get('fichaMatricula/{estudianteId}', [
        \App\Http\Controllers\MatriculaController::class,
        'getFichaMatricula',
    ])->middleware('permission:ver-matriculas');


    Route::get('matriculados/{grupoId}', [
        \App\Http\Controllers\MatriculaController::class,
        'getMatriculadosPorGrupoExtendido',
    ])->middleware('permission:ver-matriculas');


    // Cambio de matricula
    Route::patch('cambiarMatricula', [
        \App\Http\Controllers\MatriculaController::class,
        'cambiarGrupo',
    ])->middleware('permission:todo-acceso-matriculas');


    Route::patch('cambiarMatricula/{idMatricula}', [
        \App\Http\Controllers\MatriculaController::class,
        'cambiarGrupo',
    ])->middleware('permission:todo-acceso-matriculas');


    // programa por ciclo

    Route::get('especialidadByPrograma/{idPrograma}', [
        \App\Http\Controllers\MatriculaController::class,
        'getEspecialidadesPorPrograma',
    ])->middleware('permission:ver-programa-especialidades');

    Route::get('grupoByEspecialidad/{idEspecialidad}', [
        \App\Http\Controllers\MatriculaController::class,
        'getGruposPorEspecialidad',
    ])->middleware('permission:ver-especialidades');


    // PARA DOCENTES MODULOS ASIGNADOS
    Route::get('modulosAsignados', [
        \App\Http\Controllers\DocenteController::class,
        'getModulosAsignados',
    ])->middleware('permission:ver-mis-modulos|ver-estudiantes-asignados');

    // PARA LA INFO DEL GRUPO
    Route::get('infoGrupo/{id}', [
        \App\Http\Controllers\GrupoController::class,
        'infoGrupo',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos|ver-mis-modulos');
});


Route::get('reportes/nomina/grupo/{idGrupo}', [
    \App\Http\Controllers\ReporteController::class,
    'nominaMatriculasExcel',
]);

Route::middleware('auth:sanctum')->prefix('drive')->group(function () {
    Route::get('/files/{fileId}', [GoogleDriveController::class, 'listFilesNew']);
    Route::post('/folder', [GoogleDriveController::class, 'createFolder']);
    Route::post('/upload', [GoogleDriveController::class, 'uploadFile']);
    Route::patch('/file/{fileId}/rename', [GoogleDriveController::class, 'renameFile']);
    Route::patch('/file/{fileId}/move', [GoogleDriveController::class, 'moveFile']);
    Route::delete('/file/{fileId}', [GoogleDriveController::class, 'deleteFile']);
    Route::get('/drive/file/{id}/download', [GoogleDriveController::class, 'downloadFile']);

});

Route::post('/carpetas-grupo/crear/{id_grupo}', [CarpetasGrupoDriveController::class, 'crearCarpetaGrupo']);

Route::post('/google/calendar-notifications', [GoogleCalendarWebhookController::class, 'handle']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/actividades-recientes', function () {
        return \App\Models\ActividadesRecientes::latest('fecha')->take(5)->get();
    })->middleware('permission:todo-acceso-permisos|ver-permisos');
    
    Route::get('/google/subscribe-calendar', [
        \App\Http\Controllers\EntregaDocenteAdminController::class, 
        'subscribeToCalendarNotifications'
    ])->middleware('permission:todo-acceso-permisos');
});

