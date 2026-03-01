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
    Route::get('users/{id}', [
        \App\Http\Controllers\UserController::class,
        'indexUserData',
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
    Route::patch('usersRestaurarPassword/{userId}', [
        \App\Http\Controllers\UserController::class,
        'restaurarPassword',
    ])->middleware('permission:todo-acceso-usuarios|editar-usuarios');
    Route::delete('users/{userId}', [
        \App\Http\Controllers\UserController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-usuarios|eliminar-usuarios');

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
    ])->middleware('permission:todo-acceso-roles|eliminar-roles');

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
    ])->middleware('permission:todo-acceso-modalidades|ver-modalidades');

    Route::post('convenio', [
        \App\Http\Controllers\ConveniosController::class,
        'store',
    ])->middleware('permission:todo-acceso-modalidades|crear-modalidades');

    Route::patch('convenio/{id}', [
        \App\Http\Controllers\ConveniosController::class,
        'update',
    ])->middleware('permission:todo-acceso-modalidades|editar-modalidades');

    Route::delete('convenio/{convenioId}', [
        \App\Http\Controllers\ConveniosController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-modalidades|eliminar-modalidades');


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

    Route::get('estudiantesEgresados', [
        \App\Http\Controllers\EstudianteController::class,
        'getEgresados',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos|ver-grupos');

    //BUSCAR DNI
    Route::post('buscar-documento', [
        \App\Http\Controllers\EstudianteController::class,
        'buscar',
    ])->middleware('permission:todo-acceso-matrículas|ver-matrículas');

    Route::post('buscarEstudiante', [
        \App\Http\Controllers\EstudianteController::class,
        'buscarHistorialEstudiante',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos|ver-grupos');


    //CERTICADO DEL ESTUDIANTE
    Route::post('estudiante-documento', [
        \App\Http\Controllers\EstudianteDocumentoController::class,
        'emitirCertificado',
    ])->middleware('permission:todo-acceso-permisos|ver-grupos');

    //CONSTANCIA DEL ESTUDIANTE
    Route::post('estudiante-constancia', [
        \App\Http\Controllers\EstudianteDocumentoController::class,
        'emitirConstancia',
    ])->middleware('permission:todo-acceso-permisos|ver-grupos');

    Route::get('estudianteDocumentoValidar', [
        \App\Http\Controllers\EstudianteDocumentoController::class,
        'existeCertificado',
    ])->middleware('permission:todo-acceso-permisos|ver-grupos');

    //RUTA PARA CILCLO ACADEMICO
    Route::get('ciclo_academico', [
        \App\Http\Controllers\CicloAcademicoController::class,
        'index',
    ])->middleware('permission:todo-acceso-ciclo-académico|ver-ciclo-académico');

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

    Route::patch('crear_grupos_personalizado/{idAdminEntrega}', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'publicarSoloGrupo',
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
    ])->middleware('permission:todo-acceso-programación-documentos-subidos|ver-programación-documentos-subidos');

    Route::get('entrega_docente_estado/{id}', [
        \App\Http\Controllers\EntregaDocenteController::class,
        'verificarEstado',
    ])->middleware('permission:todo-acceso-programación-documentos-subidos|ver-programación-documentos-subidos|ver-mis-módulos');

    Route::post('entrega_docente_subir', [
        \App\Http\Controllers\EntregaDocenteController::class,
        'subirArchivo',
    ])->middleware('permission:todo-acceso-programación-documentos-subidos|ver-programación-documentos-subidos|ver-mis-módulos');

    Route::post('entrega_docente_sincronizar', [
        \App\Http\Controllers\EntregaDocenteController::class,
        'sincronizarEstado',
    ])->middleware('permission:todo-acceso-programación-documentos-subidos|ver-programación-documentos-subidos');

    Route::patch('entrega_docente/{id}', [
        \App\Http\Controllers\EntregaDocenteController::class,
        'update',
    ])->middleware('permission:todo-acceso-programación-documentos-subidos|editar-programación-documentos-subidos');

    Route::delete('entrega_docente/{id}', [
        \App\Http\Controllers\EntregaDocenteController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-programación-documentos-subidos|eliminar-programación-documentos-subidos');


    Route::get('programacion_grupo/{id_grupo}', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'programacionesPorGrupo',
    ])->middleware('permission:todo-acceso-programas-de-estudio|ver-mis-módulos');

    // RUTA PARA ESPECIALIDAD_MADRE
    Route::get('especialidad_madre', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'index',
    ])->middleware('permission:todo-acceso-programas-de-estudio|ver-programas-de-estudio');

    Route::get('especialidad_ciclo/{id_ciclo}', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'getEspecialidadesPorCiclo',
    ])->middleware('permission:todo-acceso-programas-de-estudio|ver-programas-de-estudio');

    Route::post('especialidad_madre', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'store',
    ])->middleware('permission:todo-acceso-programas-de-estudio|crear-programas-de-estudio');

    Route::patch('especialidad_madre/{id}', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'update',
    ])->middleware('permission:todo-acceso-programas-de-estudio|editar-programas-de-estudio');

    Route::delete('especialidad_madre/{id}', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-programas-de-estudio|eliminar-programas-de-estudio');

    Route::get('especialidad_periodo/{id_periodo}', [
        \App\Http\Controllers\EspecialidadMadreController::class,
        'getEspecialidades',
    ])->middleware('permission:todo-acceso-programas-de-estudio|ver-programas-de-estudio');

    // RUTA PARA PROGRAMA_ESTUDIO
    Route::get('programa_estudio', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'index',
    ])->middleware('permission:todo-acceso-ciclo-académico|ver-ciclo-académico');

    Route::get('programa_estudio_status', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'index_filter_status',
    ])->middleware('permission:todo-acceso-ciclo-académico|ver-ciclo-académico');


    Route::post('programa_estudio', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'store',
    ])->middleware('permission:todo-acceso-ciclo-académico|crear-ciclo-académico');

    Route::patch('programa_estudio/{id}', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'update',
    ])->middleware('permission:todo-acceso-ciclo-académico|editar-ciclo-académico');

    Route::delete('programa_estudio/{id}', [
        \App\Http\Controllers\ProgramaEstudioController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-ciclo-académico|eliminar-ciclo-académico');


    // RUTA PARA ESPECIALIDAD_PROGRAMA
    Route::get('especialidad_programa', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'index',
    ])->middleware('permission:todo-acceso-ciclo-programa|ver-ciclo-programa');

    Route::get('especialidad_programa/{id}', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'show',
    ])->middleware('permission:todo-acceso-ciclo-programa|ver-ciclo-programa');

    Route::get('especialidad_programa/{id}/modulos', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'getRelacionadosPorEspecialidadPrograma',
    ])->middleware('permission:todo-acceso-ciclo-programa|ver-ciclo-programa');

    Route::post('especialidad_programa', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'store',
    ])->middleware('permission:todo-acceso-ciclo-programa|crear-ciclo-programa');

    Route::patch('especialidad_programa/{id}', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'update',
    ])->middleware('permission:todo-acceso-ciclo-programa|editar-ciclo-programa');

    Route::delete('especialidad_programa/{id}', [
        \App\Http\Controllers\EspecialidadProgramaController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-ciclo-programa|eliminar-ciclo-programa');


    // RUTA PARA DOCENTE
    Route::get('docente', [
        \App\Http\Controllers\DocenteController::class,
        'index',
    ])->middleware('permission:todo-acceso-docentes|ver-docentes');
    Route::get('docente/{id}', [
        \App\Http\Controllers\DocenteController::class,
        'indexDataDocente',
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

    Route::get('reportes/matriculaInstitucional/{id}', [
        \App\Http\Controllers\ReporteController::class,
        'exportMatriculaInstitucional',
    ])->middleware('permission:todo-acceso-periodos|ver-periodos');

    Route::get('periodo_filter_status', [
        \App\Http\Controllers\PeriodoController::class,
        'index_filter_status',
    ])->middleware('permission:todo-acceso-periodos|ver-periodos');

    Route::post('periodo', [
        \App\Http\Controllers\PeriodoController::class,
        'store',
    ])->middleware('permission:todo-acceso-periodos|crear-periodos');

    Route::patch('periodo/{id}', [
        \App\Http\Controllers\PeriodoController::class,
        'update',
    ])->middleware('permission:todo-acceso-periodos|editar-periodos');

    Route::delete('periodo/{id}', [
        \App\Http\Controllers\PeriodoController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-periodos|eliminar-periodos');

    Route::get('periodosAnios', [
        \App\Http\Controllers\PeriodoController::class,
        'getAnios',
    ])->middleware('permission:todo-acceso-periodos|ver-periodos');

    Route::get('periodosAniosFiltrado/{anio}', [
        \App\Http\Controllers\PeriodoController::class,
        'getPeriodosFiltrados',
    ])->middleware('permission:todo-acceso-periodos|ver-periodos');

    // RUTA PARA MODULO
    Route::get('modulo', [
        \App\Http\Controllers\ModuloController::class,
        'index',
    ])->middleware('permission:todo-acceso-módulos|ver-módulos');

    Route::get('modulo/{id}', [
        \App\Http\Controllers\ModuloController::class,
        'show',
    ])->middleware('permission:todo-acceso-módulos|ver-módulos');

    Route::post('modulo', [
        \App\Http\Controllers\ModuloController::class,
        'store',
    ])->middleware('permission:todo-acceso-módulos|crear-módulos');

    Route::patch('modulo/{id}', [
        \App\Http\Controllers\ModuloController::class,
        'update',
    ])->middleware('permission:todo-acceso-módulos|editar-módulos');

    Route::delete('modulo/{id}', [
        \App\Http\Controllers\ModuloController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-módulos|eliminar-módulos');


    Route::get('certificado/{idMatricula}', [
        \App\Http\Controllers\GrupoController::class,
        'dataCertificado',
    ])->middleware('permission:todo-acceso-grupos|ver-grupos');

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
    ])->middleware('permission:todo-acceso-ciclo-programa|ver-ciclo-académico');

    Route::get('moduloByEspecialidad/{id}', [
        \App\Http\Controllers\GrupoController::class,
        'getModulosPorEspecialidad',
    ])->middleware('permission:todo-acceso-ciclo-programa|ver-ciclo-programa');

    Route::get('periodoByModulo/{id}', [
        \App\Http\Controllers\GrupoController::class,
        'getPeriodoPorModulo',
    ])->middleware('permission:todo-acceso-grupos|ver-periodos');

    // grupo-docente
    Route::get('docenteGrupo', [
        \App\Http\Controllers\GrupoController::class,
        'docentesPorGrupo',
    ])->middleware('permission:todo-acceso-grupos|ver-docentes');

    // grupos filtrados
    Route::get('gruposFiltrados', [
        \App\Http\Controllers\GrupoController::class,
        'gruposPorCicloAnioPeriodo',
    ])->middleware('permission:todo-acceso-grupos|ver-grupos');

    // Rutas nuevas para filtro de grupo
    Route::get('aniosByCiclo/{idCiclo}', [
        \App\Http\Controllers\GrupoController::class,
        'getAniosPorCiclo',
    ])->middleware('permission:todo-acceso-grupos|ver-grupos');

    Route::get('periodoByAnio/{idAnio}', [
        \App\Http\Controllers\GrupoController::class,
        'getPeriodosPorAnio',
    ])->middleware('permission:todo-acceso-grupos|ver-grupos');

    // para filtros de lista de grupos

    Route::get('periodoByCiclo/{cicloId}', [
        \App\Http\Controllers\GrupoController::class,
        'getPeriodosPorCiclo',
    ])->middleware('permission:todo-acceso-grupos|ver-grupos');

    // lista de grupos
    Route::get('gruposMatricula', [
        \App\Http\Controllers\GrupoController::class,
        'getGruposPorCicloYPeriodo',
    ])->middleware('permission:todo-acceso-matrículas|ver-matrículas');

    // Lista de grupos disponibles para cambio de grupo
    Route::get('gruposDisponibles', [
        \App\Http\Controllers\GrupoController::class,
        'gruposDisponibles',
    ])->middleware('permission:todo-acceso-matrículas|trasladar-estudiante-matrículas');

    Route::get('gruposRecientes', [
        \App\Http\Controllers\GrupoController::class,
        'ultimosGrupos',
    ])->middleware('permission:todo-acceso-grupos|ver-grupos');

    Route::get('gruposCulminados', [
        \App\Http\Controllers\GrupoController::class,
        'gruposCulminados',
    ])->middleware('permission:todo-acceso-grupos|ver-grupos');

    //RUTA PARA CAPACIDAD TERMINAL
    Route::get('capacidad_terminal', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'index',
    ])->middleware('permission:todo-acceso-unidad-didáctica-docente|ver-unidad-didáctica-docente');
    Route::get('capacidad_terminal/{id}', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'indexGrupo',
    ])->middleware('permission:todo-acceso-unidad-didáctica-docente|ver-unidad-didáctica-docente');

    Route::get('unidades_didacticas/{id}', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'indexUnidadDidactica',
    ])->middleware('permission:todo-acceso-unidad-didáctica-docente|ver-unidad-didáctica-docente');

    Route::get('nro_capacidades/{id}', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'nroCapacidades',
    ])->middleware('permission:todo-acceso-unidad-didáctica-docente|ver-unidad-didáctica-docente');

    Route::get('lista_calificaciones/{id}', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'getMatriculadosPorGrupoParaNotas',
    ])->middleware('permission:ver-grupos|todo-acceso-unidad-didáctica-docente|ver-unidad-didáctica-docente');

    Route::post('capacidad_terminal', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'store',
    ])->middleware('permission:todo-acceso-unidad-didáctica-docente|crear-unidad-didáctica-docente');

    Route::patch('capacidad_terminal_reactivar/{id}', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'reactivarNota',
    ])->middleware('permission:todo-acceso-unidad-didáctica-docente|editar-unidad-didáctica-docente|ver-grupos');

    Route::post('capacidad_terminal_aplazar/{id}', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'aplazarCapacidadTerminal',
    ])->middleware('permission:todo-acceso-unidad-didáctica-docente|editar-unidad-didáctica-docente|ver-grupos');

    Route::patch('capacidad_terminal/{id}', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'update',
    ])->middleware('permission:todo-acceso-unidad-didáctica-docente|editar-unidad-didáctica-docente');

    Route::delete('capacidad_terminal/{id}', [
        \App\Http\Controllers\CapacidadTerminalController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-unidad-didáctica-docente|eliminar-unidad-didáctica-docente');

    // RUTA PARA NOTA DE CAPACIDAD TERMINAL
    Route::get('nota_capacidad_terminal', [
        \App\Http\Controllers\NotaCapacidadTerminalController::class,
        'index',
    ])->middleware('permission:todo-acceso-unidad-didáctica-notas-docente|ver-unidad-didáctica-notas-docente');

    Route::get('nota_capacidad_terminal/{id}', [
        \App\Http\Controllers\NotaCapacidadTerminalController::class,
        'index_grupo_alumnos',
    ])->middleware('permission:todo-acceso-unidad-didáctica-notas-docente|ver-unidad-didáctica-notas-docente');

    Route::get('lista_alumnos_notas/{id}', [
        \App\Http\Controllers\NotaCapacidadTerminalController::class,
        'listaAlumnosNotas',
    ])->middleware('permission:ver-grupos|todo-acceso-unidad-didáctica-notas-docente|ver-unidad-didáctica-notas-docente');

    Route::get('nota_capacidad_terminal_restringido/{id}', [
        \App\Http\Controllers\NotaCapacidadTerminalController::class,
        'index_grupo_capacidad_terminal',
    ])->middleware('permission:todo-acceso-unidad-didáctica-notas-docente|ver-unidad-didáctica-notas-docente');

    Route::get('nota_capacidad_terminal_info/{id}', [
        \App\Http\Controllers\NotaCapacidadTerminalController::class,
        'obtenerInfoCapacidad',
    ])->middleware('permission:todo-acceso-unidad-didáctica-notas-docente|ver-unidad-didáctica-notas-docente');

    Route::post('nota_capacidad_terminal', [
        \App\Http\Controllers\NotaCapacidadTerminalController::class,
        'store',
    ])->middleware('permission:ver-grupos|todo-acceso-unidad-didáctica-notas-docente|crear-unidad-didáctica-notas-docente');

    Route::patch('nota_capacidad_terminal/{id}', [
        \App\Http\Controllers\NotaCapacidadTerminalController::class,
        'update',
    ])->middleware('permission:todo-acceso-unidad-didáctica-notas-docente|editar-unidad-didáctica-notas-docente');

    Route::delete('nota_capacidad_terminal/{id}', [
        \App\Http\Controllers\NotaCapacidadTerminalController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-unidad-didáctica-notas-docente|eliminar-unidad-didáctica-notas-docente');


    // RUTA PARA EXPERIENCIA FORMATIVA
    Route::get('experiencia_formativa', [
        \App\Http\Controllers\ExperienciaFormativaController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos|ver-mis-módulos');

    Route::get('experiencia_formativa_index/{id_grupo}', [
        \App\Http\Controllers\ExperienciaFormativaController::class,
        'indexExperienciaFormativa',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos|ver-mis-módulos');

    Route::get('experiencia_formativa_folderDrive/{id_grupo}', [
        \App\Http\Controllers\ExperienciaFormativaController::class,
        'indexExperienciaFormativaFolderDrive',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos|ver-mis-módulos');

    Route::post('experiencia_formativa', [
        \App\Http\Controllers\ExperienciaFormativaController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos|ver-grupos|ver-mis-módulos');

    Route::patch('experiencia_formativa/{id}', [
        \App\Http\Controllers\ExperienciaFormativaController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|editar-permisos|ver-mis-módulos');

    Route::delete('experiencia_formativa/{id}', [
        \App\Http\Controllers\ExperienciaFormativaController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos');


    // RUTA PARA COMPETENCIAS (COMPETENCIAS TECNICAS Y EMPLEABILIDAD)
    Route::get('competencias', [
        \App\Http\Controllers\CompetenciaController::class,
        'index',
    ])->middleware('permission:todo-acceso-mo|ver-permisos|ver-competencias');

    Route::get('competencias_index/{idModulo}', [
        \App\Http\Controllers\CompetenciaController::class,
        'getCompetenciasPorModulo',
    ])->middleware('permission:todo-acceso-competencias |ver-competencias|ver-mis-módulos');

    Route::post('competencias', [
        \App\Http\Controllers\CompetenciaController::class,
        'store',
    ])->middleware('permission:todo-acceso-competencias |crear-competencias');

    Route::patch('competencias/{id}', [
        \App\Http\Controllers\CompetenciaController::class,
        'update',
    ])->middleware('permission:todo-acceso-competencias |editar-competencias');

    Route::delete('competencias/{id}', [
        \App\Http\Controllers\CompetenciaController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|eliminar-permisos|ver-mis-módulos');

    // RUTA PARA CAPACIDADES TERMINALES-COMPETENCIA
    Route::get('capacidad_competencia', [
        \App\Http\Controllers\CapacidadCompetenciaController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos|ver-mis-módulos');

    Route::get('capacidad_competencia_index/{idGrupo}', [
        \App\Http\Controllers\CapacidadCompetenciaController::class,
        'getCapacidadesPorCompetencia',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos|ver-mis-módulos');

    Route::post('capacidad_competencia', [
        \App\Http\Controllers\CapacidadCompetenciaController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos|ver-grupos|ver-mis-módulos');

    Route::patch('capacidad_competencia/{id}', [
        \App\Http\Controllers\CapacidadCompetenciaController::class,
        'update',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos|ver-mis-módulos');

    Route::delete('capacidad_competencia/{id}', [
        \App\Http\Controllers\CapacidadCompetenciaController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-módulos|ver-mis-módulos');


    // RUTA PARA NOTA DE EXPERIENCIA FORMATIVA
    Route::get('nota_experiencia_formativa', [
        \App\Http\Controllers\NotaExperienciaFormativaController::class,
        'index',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::post('nota_experiencia_formativa', [
        \App\Http\Controllers\NotaExperienciaFormativaController::class,
        'store',
    ])->middleware('permission:todo-acceso-permisos|crear-permisos|ver-mis-módulos');

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

    Route::get('sesion_docente', [
        \App\Http\Controllers\SesionesController::class,
        'indexOneSesion',
    ])->middleware('permission:todo-acceso-sesiones-docente|ver-sesiones-docente');

    Route::get('programacion_sesion_docente/{id}', [
        \App\Http\Controllers\SesionesController::class,
        'indexListSesionesDocente',
    ])->middleware('permission:todo-acceso-sesiones-docente|ver-sesiones-docente');

    Route::post('programacion_sesion_docente', [
        \App\Http\Controllers\SesionesController::class,
        'store',
    ])->middleware('permission:todo-acceso-sesiones-docente|crear-sesiones-docente');

    Route::patch('programacion_sesion_docente/{id}', [
        \App\Http\Controllers\SesionesController::class,
        'update',
    ])->middleware('permission:todo-acceso-sesiones-docente|editar-sesiones-docente');

    Route::delete('programacion_sesion_docente/{id}', [
        \App\Http\Controllers\SesionesController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-sesiones-docente|eliminar-sesiones-docente');

    // RUTA PARA ASISTENCIA
    Route::get('asistencia', [
        \App\Http\Controllers\AsistenciaController::class,
        'index',
    ])->middleware('permission:ver-mis-módulos|todo-acceso-permisos|ver-permisos');

    Route::get('sesiones_entrega/{idEntrega}', [
        \App\Http\Controllers\AsistenciaController::class,
        'obtenerSesionPorEntrega',
    ])->middleware('permission:ver-mis-módulos|todo-acceso-permisos|ver-permisos');

    Route::get('sesiones_asistencia/{idEntrega}', [
        \App\Http\Controllers\AsistenciaController::class,
        'obtenerAsistenciaEstudiantes',
    ])->middleware('permission:ver-mis-módulos|todo-acceso-permisos|ver-permisos');

    Route::post('asistencia', [
        \App\Http\Controllers\AsistenciaController::class,
        'store',
    ])->middleware('permission:ver-grupos|ver-mis-módulos|todo-acceso-mis-módulos');

    Route::get('asistencia/{idGrupo}', [
        \App\Http\Controllers\AsistenciaController::class,
        'listAsistenciaEstudiantes',
    ])->middleware('permission:ver-grupos|ver-mis-módulos|todo-acceso-mis-módulos');

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

    Route::delete('entregas_realizadas/{fileId}', [
        \App\Http\Controllers\EntregasRealizadasController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-permisos|ver-mis-módulos');

    //RUTA PARA EGRESADOS
    Route::get('egresados', [
        \App\Http\Controllers\EgresadosController::class,
        'index',
    ])->middleware('permission:todo-acceso-egresados|ver-egresados');
    
    Route::get('egresados/{idEstudiante}', [
        \App\Http\Controllers\EgresadosController::class,
        'datosEstudianteEgresado',
    ])->middleware('permission:todo-acceso-egresados|ver-egresados');

    Route::post('egresados', [
        \App\Http\Controllers\EgresadosController::class,
        'store',
    ])->middleware('permission:todo-acceso-egresados|crear-egresados|ver-grupos');

    Route::patch('egresados/{id}', [
        \App\Http\Controllers\EgresadosController::class,
        'update',
    ])->middleware('permission:todo-acceso-egresados|editar-egresados');

    Route::delete('egresados/{id}', [
        \App\Http\Controllers\EgresadosController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-egresados|eliminar-egresados');

// RUTAS PARA DOCUMENTOS DE EGRESADOS

Route::get('egresado-documento', [
    \App\Http\Controllers\EgresadoDocumentoController::class,
    'index',
])->middleware('permission:todo-acceso-egresados|ver-egresados');

Route::get('egresado-documento/{id}', [
    \App\Http\Controllers\EgresadoDocumentoController::class,
    'show',
])->middleware('permission:todo-acceso-egresados|ver-egresados');

Route::post('egresado-documento', [
    \App\Http\Controllers\EgresadoDocumentoController::class,
    'store',
])->middleware('permission:todo-acceso-egresados|crear-egresados');

Route::patch('egresado-documento/{id}', [
    \App\Http\Controllers\EgresadoDocumentoController::class,
    'update',
])->middleware('permission:todo-acceso-egresados|editar-egresados');

// Route::delete('egresado-documento/{id}', [
//     \App\Http\Controllers\EgresadoDocumentoController::class,
//     'destroy',
// ])->middleware('permission:todo-acceso-egresados|eliminar-egresado-documento');

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
    Route::get('index_comision_docente/{id}', [
        \App\Http\Controllers\ComisionesController::class,
        'comision_docente',
    ])->middleware('permission:ver-comsion-docente');

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

    Route::get('/notificaciones/marcar-todo', [
        \App\Http\Controllers\NotificacionesController::class,
        'marcarTodo',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::get('/notificaciones/leer/{id}', [
        \App\Http\Controllers\NotificacionesController::class,
        'marcarLeido',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos');

    Route::get('/notificaciones/pendientes/{id}', [
        \App\Http\Controllers\NotificacionesController::class,
        'countUnread',
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

    Route::get('actividades_recientes_fecha', [
        \App\Http\Controllers\ActividadesRecientesController::class,
        'indexByDate',
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
    ])->middleware('permission:todo-acceso-matrículas|ver-matrículas');

    Route::get('matriculaUpdate/{id}', [
        \App\Http\Controllers\MatriculaController::class,
        'matriculaAlumnoData',
    ])->middleware('permission:todo-acceso-matrículas|editar-matrículas');

    Route::post('matricula', [
        \App\Http\Controllers\MatriculaController::class,
        'store',
    ])->middleware('permission:todo-acceso-matrículas|crear-matrículas');

    Route::patch('matricula/{id}', [
        \App\Http\Controllers\MatriculaController::class,
        'update',
    ])->middleware('permission:todo-acceso-matrículas|editar-matrículas');

    Route::delete('matricula/{id}', [
        \App\Http\Controllers\MatriculaController::class,
        'destroy',
    ])->middleware('permission:todo-acceso-matrículas|eliminar-matrículas');


    Route::get('ingresos/{id}', [
        \App\Http\Controllers\GrupoController::class,
        'IngresosByGrupo',
    ])->middleware('permission:todo-acceso-matrículas|ver-matrículas');


    //matricula Reserva

    Route::patch('reservaMatricula/{id}', [
        \App\Http\Controllers\MatriculaController::class,
        'reservar',
    ])->middleware('permission:todo-acceso-matrículas|reservar-estudiante-matrículas');

    Route::delete('reserva/{id}', [
        \App\Http\Controllers\MatriculaController::class,
        'usarReservar',
    ])->middleware('permission:todo-acceso-matrículas|reservar-estudiante-matrículas');

    Route::get('listaReserva/{tipo}', [
        \App\Http\Controllers\MatriculaController::class,
        'matriculadosConReserva',
    ])->middleware('permission:ver-matrículas');
    // Alumnos matriculados (en modulo matricula)
    Route::get('matricula/{grupoId}', [
        \App\Http\Controllers\MatriculaController::class,
        'getMatriculadosPorGrupo',
    ])->middleware('permission:ver-matrículas');

    Route::get('fichaMatricula/{estudianteId}', [
        \App\Http\Controllers\MatriculaController::class,
        'getFichaMatricula',
    ])->middleware('permission:ver-matrículas');


    Route::get('matriculados/{grupoId}', [
        \App\Http\Controllers\MatriculaController::class,
        'getMatriculadosPorGrupoExtendido',
    ])->middleware('permission:ver-matrículas|ver-mis-módulos');


    // Cambio de matricula
    Route::patch('cambiarMatricula', [
        \App\Http\Controllers\MatriculaController::class,
        'cambiarGrupo',
    ])->middleware('permission:todo-acceso-matrículas');


    Route::patch('cambiarMatricula/{idMatricula}', [
        \App\Http\Controllers\MatriculaController::class,
        'cambiarGrupo',
    ])->middleware('permission:todo-acceso-matrículas');

    Route::patch('retirarEstudiante', [
        \App\Http\Controllers\MatriculaController::class,
        'retirarAlumno',
    ])->middleware('permission:todo-acceso-matrículas|ver-mis-módulos');


    // programa por ciclo

    Route::get('especialidadByPrograma/{idPrograma}', [
        \App\Http\Controllers\MatriculaController::class,
        'getEspecialidadesPorPrograma',
    ])->middleware('permission:todo-acceso-ciclo-programa|ver-ciclo-programa');

    Route::get('grupoByEspecialidad/{idEspecialidad}', [
        \App\Http\Controllers\MatriculaController::class,
        'getGruposPorEspecialidad',
    ])->middleware('permission:permission:todo-acceso-matrículas|ver-matrículas');

    Route::get('grupoByPeriodo/{idPeriodo}', [
        \App\Http\Controllers\EntregaDocenteAdminController::class,
        'obtenerGruposPorPeriodo',
    ])->middleware('permission:ver-programas-de-estudio');

    // PARA DOCENTES MODULOS ASIGNADOS
    Route::get('modulosAsignados', [
        \App\Http\Controllers\DocenteController::class,
        'getModulosAsignados',
    ])->middleware('permission:ver-mis-módulos|ver-estudiantes-asignados');

    // PARA LA INFO DEL GRUPO
    Route::get('infoGrupo/{id}', [
        \App\Http\Controllers\GrupoController::class,
        'infoGrupo',
    ])->middleware('permission:todo-acceso-permisos|ver-permisos|ver-mis-módulos');

    // RUTA PARA REPORTE DE CUMPLIMIENTO
    Route::get('/reporte-entregas-docentes', [
        \App\Http\Controllers\EntregaDocenteController::class,
        'generarExcel',
    ])->middleware('permission:ver-ciclo-programa');

    Route::get('/reporte-censo', [
        \App\Http\Controllers\ReporteController::class,
        'generarExcelCenso',
    ])->middleware('permission:ver-ciclo-programa');

    Route::get('/censo9b-data', [
        \App\Http\Controllers\CensoController::class,
        'data',
    ])->middleware('permission:ver-ciclo-programa');

    Route::get('/censo9b-anios', [
        \App\Http\Controllers\CensoController::class,
        'anios',
    ])->middleware('permission:ver-ciclo-programa');

    Route::get('/reporte-acta-evaluacion/{idGrupo}', [
        \App\Http\Controllers\ReporteController::class,
        'actaEvaluacionExcel',
    ])->middleware('permission:ver-ciclo-programa');
    Route::get('/reporte-consolidado/{idGrupo}', [
        \App\Http\Controllers\ReporteController::class,
        'consolidadoExcel',
    ])->middleware('permission:ver-ciclo-programa');


    Route::post('cetprodata', [
        \App\Http\Controllers\DatosCetproController::class,
        'store',
    ])->middleware('permission:editar-información-cetpro');

    Route::get('estadistica101', [
        \App\Http\Controllers\EstadisticaController::class,
        'estadistica101Data',
    ])->middleware('permission:ver-estadísticas|todo-acceso-estadísticas');

    Route::get('estadistica104', [
        \App\Http\Controllers\EstadisticaController::class,
        'matriculadosRetiradosPorCarrera',
    ])->middleware('permission:ver-estadísticas|todo-acceso-estadísticas');

    Route::get('estadistica202', [
        \App\Http\Controllers\EstadisticaController::class,
        'matriculadosPorCicloYSexo',
    ])->middleware('permission:ver-estadísticas|todo-acceso-estadísticas');

    Route::get('estadistica203', [
        \App\Http\Controllers\EstadisticaController::class,
        'matriculaPorNivelEducativoCicloSexo',
    ])->middleware('permission:ver-estadísticas|todo-acceso-estadísticas');

    Route::get('estadistica205', [
        \App\Http\Controllers\EstadisticaController::class,
        'seccionesPorCicloTurno',
    ])->middleware('permission:ver-estadísticas|todo-acceso-estadísticas');

    Route::get('estadistica201', [
        \App\Http\Controllers\EstadisticaController::class,
        'matriculaPorCicloSexoEdad',
    ])->middleware('permission:ver-estadísticas|todo-acceso-estadísticas');
});
Route::get('cetprodata', [
    \App\Http\Controllers\DatosCetproController::class,
    'show',
]);

Route::get('reportes/nomina/grupo/{idGrupo}', [
    \App\Http\Controllers\ReporteController::class,
    'nominaMatriculasExcel',
]);

Route::get('reportes/registroMatriculaConEvaluaciones/{idGrupo}', [
    \App\Http\Controllers\ReporteController::class,
    'RegistroMatricula_RegistroEvaluacionPorModulo',
]);

Route::get('reportes/certificadosPorPeriodo/{idPeriodo}', [
    \App\Http\Controllers\ReporteController::class,
    'exportCertificadosPorPeriodo',
]);

// ESTADISTICAS 


Route::middleware('auth:sanctum')->prefix('drive')->group(function () {
    Route::get('/files/{fileId}', [GoogleDriveController::class, 'listFilesNew']);
    Route::post('/folder', [GoogleDriveController::class, 'createFolder']);
    Route::post('/upload', [GoogleDriveController::class, 'uploadFile']);
    Route::post('/uploadDocente', [GoogleDriveController::class, 'uploadFileDocente']);
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
