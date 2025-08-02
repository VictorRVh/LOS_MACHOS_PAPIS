import useProgramaStore from '../store/Programa/useProgramaStore';
import useEspecialidadProgramaStore from '../store/EspecialidadPrograma/useEspecialidadProgramaStore';

export default [
    { path: '/', name: 'login', component: () => import('../pages/Login.vue'), meta: { layout: 'full', permissions: [], }, },
    { path: '/start', name: 'start', component: () => import('../pages/Start.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-usuarios', 'ver-usuarios'], breadcrumb: [{ text: 'Inicio', to: { name: 'start' } }] }, },
    { path: '/groups', name: 'groups', component: () => import('../pages/Users.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-usuarios', 'ver-usuarios'], }, },
    { path: '/programs', name: 'programs', component: () => import('../pages/Users.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-usuarios', 'ver-usuarios'], }, },
    { path: '/califications', name: 'califications', component: () => import('../pages/Users.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-usuarios', 'ver-usuarios'], }, },
    { path: '/users', name: 'users', component: () => import('../pages/Users.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-usuarios', 'ver-usuarios'], breadcrumb: [{ text: 'Usuarios', to: { name: 'users' } }] }, },
    { path: '/users/crear', name: 'users.crear', component: () => import('../pages/Users.vue'), meta: { layout: 'dashboard', permissions: ['crear-usuarios'], breadcrumb: [ { text: 'Usuarios', to: { name: 'users' } }, { text: 'Crear' } ] } },
    { path: '/roles', name: 'roles', component: () => import('../pages/Roles.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-roles', 'ver-roles'], breadcrumb: [{ text: 'Roles', to: { name: 'roles' } }] } },
    { path: '/roles/crear', name: 'roles.crear', component: () => import('../pages/RolesCrear.vue'), meta: { layout: 'dashboard', breadcrumb: [ { text: 'Roles', to: { name: 'roles' } }, { text: 'Crear' } ] } },
    { path: '/roles/confirmar', name: 'roles.confirmar', component: () => import('../pages/Confirmar.vue'), meta: { layout: 'dashboard', breadcrumb: [ { text: 'Roles', to: { name: 'roles' } }, { text: 'Crear', to: { name: 'roles.crear' } }, { text: 'Confirmar' } ] } },
    { path: '/permissions', name: 'permissions', component: () => import('../pages/newPermission.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos', 'ver-permisos'], breadcrumb: [{ text: 'Permisos', to: { name: 'permissions' } }] }, },
    { path: '/docente', name: 'docente', component: () => import('../pages/Docente/Docente.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos', 'ver-permisos'], breadcrumb: [{ text: 'Docentes', to: { name: 'docente' } }] }, },
    { path: '/convenio', name: 'convenio', component: () => import('../pages/Convenio/Convenio.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos', 'ver-permisos'], breadcrumb: [{ text: 'Convenios', to: { name: 'convenio' } }] }, },
    { path: '/periodo', name: 'periodo', component: () => import('../pages/Periodo/Periodo.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos', 'ver-permisos'], breadcrumb: [{ text: 'Periodos', to: { name: 'periodo' } }] }, },
    { path: '/administrativos', name: 'administrativos', component: () => import('../pages/Administrativo/Administrativo.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos', 'ver-permisos'], breadcrumb: [{ text: 'Administrativos', to: { name: 'administrativos' } }] }, },
    { path: '/especialidad', name: 'especialidad', component: () => import('../pages/Especialidad/Especialidad.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos', 'ver-permisos'], breadcrumb: [{ text: 'Especialidades', to: { name: 'especialidad' } }] }, },
    { path: '/comision', name: 'comision', component: () => import('../pages/Comision/Comision.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos', 'ver-permisos'], breadcrumb: [{ text: 'Comisiones', to: { name: 'comision' } }] }, },
    {
        path: '/programa',
        name: 'programa',
        component: () => import('../pages/Programa/Programa.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: [{ text: 'Programas de Estudio', to: { name: 'programa' } }]
        },
    },
    {
        path: '/especialidadPrograma/:idPrograma',
        name: 'especialidadPrograma',
        component: () => import('../pages/EspecialidadPrograma/EspecialidadPrograma.vue'),
        props: true,
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            parent: 'programa',
            breadcrumb: async (route) => {
                const programaStore = useProgramaStore();
                const programa = await programaStore.findProgramaById(route.params.idPrograma);
                return {
                    text: programa?.nameCiclo || 'Cargando programa...',
                    to: { name: 'especialidadPrograma', params: { idPrograma: route.params.idPrograma } }
                };
            }
        },
    },
    {
        path: '/modulo/:idPrograma/:idEspecialidadPrograma',
        name: 'modulo',
        component: () => import('../pages/Modulo/Modulo.vue'),
        props: true,
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            parent: 'especialidadPrograma',
            breadcrumb: async (route) => {
                const espProgramaStore = useEspecialidadProgramaStore();
                await espProgramaStore.loadEspecialidadProgramaById(route.params.idPrograma);
                const espPrograma = espProgramaStore.especialidadProgramaFiltrado?.especialidad_programas?.find(
                    ep => ep.id == route.params.idEspecialidadPrograma
                );
                return {
                    crumb: {
                        text: 'Especialidad: ' + (espPrograma?.especialidad_madre?.nombre_especialidad || 'Cargando...'),
                        to: { name: 'modulo', params: route.params }
                    },
                    parentParams: { idPrograma: route.params.idPrograma }
                };
            }
        },
    },
    { path: '/programa/crear', name: 'programa.crear', component: () => import('../pages/Programa/Programa.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos', 'ver-permisos'], breadcrumb: [ { text: 'Programas de Estudio', to: { name: 'programa' } }, { text: 'Crear' } ] } },
    { path: '/programa/editar/:id', name: 'programa.editar', component: () => import('../pages/Programa/Programa.vue'), props: true, meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos', 'ver-permisos'], breadcrumb: [{ text: 'Programas de Estudio', to: { name: 'programa' } }] }, },
    { path: '/especialidadPrograma/:id/editar', name: 'especialidadPrograma.editar', component: () => import('../pages/EspecialidadPrograma/EspecialidadPrograma.vue'), props: true, meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos', 'ver-permisos'], }, },
    { path: '/matricula', name: 'matricula', component: () => import('../pages/Estudiante/Estudiante.vue'), props: true, meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos', 'ver-permisos'], breadcrumb: [{ text: 'Matrícula', to: { name: 'matricula' } }] }, },
    { path: '/grupo', name: 'grupo', component: () => import('../pages/Grupo/Grupo.vue'), props: true, meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos', 'ver-permisos'], }, },
    
    // --- NUEVA RUTA INTEGRADA AQUÍ ---
    { 
        path: '/configuracion-cuenta', 
        name: 'cuenta.editar', 
        component: () => import('../pages/edit_date.vue'), 
        meta: { 
            layout: 'dashboard', 
            breadcrumb: [{ text: 'Configuración de mi Cuenta' }] 
        } 
    },
    { 
        path: '/notificaciones', 
        name: 'notificaciones.index', 
        component: () => import('../pages/FullNotificaciones.vue'), 
        meta: { 
            layout: 'dashboard', 
            breadcrumb: [{ text: 'Todas las Notificaciones' }] 
        } 
    },
];