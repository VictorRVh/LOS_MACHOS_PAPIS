import useProgramaStore from '../store/Programa/useProgramaStore';
import useEspecialidadProgramaStore from '../store/EspecialidadPrograma/useEspecialidadProgramaStore';
import useGrupoStore from '../store/Grupo/useGrupoStore';

import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';



export default [
    { path: '/', name: 'login', component: () => import('../pages/Login.vue'), meta: { layout: 'full', permissions: [], }, },
    { path: '/start', name: 'start', component: () => import('../pages/Start.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Inicio', to: { name: 'start' } }] }, },
    { path: '/groups', name: 'groups', alias: ['/matricula'], component: () => import('../pages/Users.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], }, },
    { path: '/programs', name: 'programs', alias: ['/matricula'], component: () => import('../pages/Users.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], }, },
    { path: '/califications', name: 'califications', component: () => import('../pages/Users.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], }, },
    { path: '/users', name: 'users', component: () => import('../pages/Users.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Usuarios', to: { name: 'users' } }] }, },
    { path: '/users/crear', name: 'users.crear', component: () => import('../pages/Users.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Usuarios', to: { name: 'users' } }, { text: 'Crear' }] } },
    { path: '/roles', name: 'roles', component: () => import('../pages/Roles.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Roles', to: { name: 'roles' } }] } },
    { path: '/roles/crear', name: 'roles.crear', component: () => import('../pages/RolesCrear.vue'), meta: { layout: 'dashboard', breadcrumb: [{ text: 'Roles', to: { name: 'roles' } }, { text: 'Crear' }] } },
    { path: '/roles/confirmar', name: 'roles.confirmar', component: () => import('../pages/Confirmar.vue'), meta: { layout: 'dashboard', breadcrumb: [{ text: 'Roles', to: { name: 'roles' } }, { text: 'Crear', to: { name: 'roles.crear' } }, { text: 'Confirmar' }] } },
    { path: '/permissions', name: 'permissions', component: () => import('../pages/newPermission.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Permisos', to: { name: 'permissions' } }] }, },
    { path: '/docente', name: 'docente', component: () => import('../pages/Docente/Docente.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Docentes', to: { name: 'docente' } }] }, },
    { path: '/convenio', name: 'convenio', component: () => import('../pages/Convenio/Convenio.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Convenios', to: { name: 'convenio' } }] }, },
    { path: '/periodo', name: 'periodo', component: () => import('../pages/Periodo/Periodo.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Periodos', to: { name: 'periodo' } }] }, },
    { path: '/administrativos', name: 'administrativos', component: () => import('../pages/Administrativo/Administrativo.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Administrativos', to: { name: 'administrativos' } }] }, },
    { path: '/especialidad', name: 'especialidad', component: () => import('../pages/Especialidad/Especialidad.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Especialidades', to: { name: 'especialidad' } }] }, },
    { path: '/comision', name: 'comision', component: () => import('../pages/Comision/Comision.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Comisiones', to: { name: 'comision' } }] }, },
    {
        path: '/programa',
        name: 'programa',
        component: () => import('../pages/Programa/Programa.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
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
            permissions: ['todo-acceso-permisos'],
            parent: 'programa', // ✅ correcto
            breadcrumb: async (route) => {
                const breadcrumbStore = useBreadcrumbStore();
                const item = await breadcrumbStore.findTextById(route.params.idPrograma);
                return {
                    text: item?.text || 'Cargando...',
                    to: { name: 'especialidadPrograma', params: { idPrograma: route.params.idPrograma } }
                };
            },
        },
    },
    {
        path: '/modulo/:idEspecialidadPrograma',
        name: 'modulo',
        component: () => import('../pages/Modulo/Modulo.vue'),
        props: true,
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
            parent: 'especialidadPrograma', // ✅ correcto
            breadcrumb: async (route) => {
                const breadcrumbStore = useBreadcrumbStore();
                const item = await breadcrumbStore.findTextById(route.params.idEspecialidadPrograma);
                return {
                    text: item?.text || 'Módulos',
                    to: { name: 'modulo', params: { idEspecialidadPrograma: route.params.idEspecialidadPrograma } },
                };
            },
        },
    },
    {
        path: '/matricula',
        name: 'matricula.index',

        component: () => import('../pages/Matricula/Matricula.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
            parent: 'grupo',
            breadcrumb: [{ text: 'Matrícula', to: { name: 'matricula.index' } }]
        },
    },
    {
        path: '/matricula/registrar',
        name: 'matricula.registrar',
        component: () => import('../pages/Matricula/MatriculaForm.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
            breadcrumb: [{ text: 'Matrícula', to: { name: 'matricula.index' } }, { text: 'Matricular Estudiante' }]
        },
    },
    {
        path: '/matricula/grupos',
        name: 'matricula.grupos',
        component: () => import('../pages/Matricula/ListaGrupos.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
            breadcrumb: [{ text: 'Matrícula', to: { name: 'matricula.index' } }, { text: 'Lista por Grupos' }]
        },
    },
    {
        path: '/matricula/grupo/:id',
        name: 'matricula.grupo.detalle',
        component: () => import('../pages/Matricula/GrupoDetalle.vue'),
        props: true,
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
            parent: 'matricula.grupos',
            breadcrumb: { text: 'Detalle de Grupo' }
        },
    },
    {
        path: '/matricula/reservas',
        name: 'matricula.reservas',
        component: () => import('../pages/Matricula/Reservas.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
            breadcrumb: [{ text: 'Matrícula', to: { name: 'matricula.index' } }, { text: 'Estudiantes con Reserva' }]
        },
    },
    {
        path: '/grupo',
        name: 'grupo',
        component: () => import('../pages/Grupo/Grupo.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
            breadcrumb: [{ text: 'Grupos', to: { name: 'grupo' } }],
        }
    },
    {
        path: '/grupo/:id',
        name: 'grupo.detalle',
        component: () => import('../pages/Grupo/GrupoDetalle.vue'),
        props: true,
        redirect: route => ({ name: 'grupo.documentos', params: { id: route.params.id } }),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
            parent: 'grupo',
            breadcrumb: async (route) => {
                const breadcrumbStore = useBreadcrumbStore();
                // Intentamos buscar el item en el store por id
                let item = await breadcrumbStore.findTextById(route.params.id);
                return {
                    text: item?.name || "Cargando..",
                    to: { name: route?.name, params: { id: item?.id } }, // mantiene la misma ruta
                };
            },
            submenu: (route) => [
                { text: 'Documentos', to: { name: 'grupo.documentos', params: { id: route.params.id } } },
                { text: 'Sesiones y asistencia', to: { name: 'grupo.asistencia', params: { id: route.params.id } } },
                { text: 'Calificaciones', to: { name: 'grupo.calificaciones', params: { id: route.params.id } } },
                { text: 'Prácticas', to: { name: 'grupo.practicas', params: { id: route.params.id } } },
                { text: 'Alumnos', to: { name: 'grupo.alumnos', params: { id: route.params.id } } },
            ]
        },
        children: [
            {
                path: 'documentos',
                name: 'grupo.documentos',
                component: () => import('../pages/Grupo/GrupoDocumentos.vue'),
                props: true,
                meta: { parent: 'grupo.detalle', breadcrumb: { text: 'Documentos' } }
            },
            {
                path: 'asistencia',
                name: 'grupo.asistencia',
                component: () => import('../pages/Grupo/GrupoAsistencia.vue'),
                props: true,
                meta: { parent: 'grupo.detalle', breadcrumb: { text: 'Asistencia' } }
            },
            {
                path: 'calificaciones',
                name: 'grupo.calificaciones',
                component: () => import('../pages/Grupo/GrupoCalificaciones.vue'),
                props: true,
                meta: { parent: 'grupo.detalle', breadcrumb: { text: 'Calificaciones' } }
            },
            {
                path: 'practicas',
                name: 'grupo.practicas',
                component: () => import('../pages/Grupo/GrupoPracticas.vue'),
                props: true,
                meta: { parent: 'grupo.detalle', breadcrumb: { text: 'Prácticas' } }
            },
            {
                path: 'alumnos',
                name: 'grupo.alumnos',
                component: () => import('../pages/Grupo/GrupoAlumnos.vue'),
                props: true,
                meta: { parent: 'grupo.detalle', breadcrumb: { text: 'Alumnos' } }
            },
        ]
    },
    { path: '/configuracion-cuenta', name: 'cuenta.editar', component: () => import('../pages/edit_date.vue'), meta: { layout: 'dashboard', breadcrumb: [{ text: 'Configuración de mi Cuenta' }] } },
    { path: '/notificaciones', name: 'notificaciones.index', component: () => import('../pages/FullNotificaciones.vue'), meta: { layout: 'dashboard', breadcrumb: [{ text: 'Todas las Notificaciones' }] } },
    { path: '/mi-asistencia', name: 'biometrico.asistencia', component: () => import('../pages/Biometrico/AsistenciaBiometrica.vue'), meta: { layout: 'dashboard', breadcrumb: [{ text: 'Mi Asistencia Biométrica' }] } },
    { path: '/mi-asistencia/:idAsignacion', name: 'biometrico.detalle', component: () => import('../pages/Biometrico/AsistenciaDetalle.vue'), props: true, meta: { layout: 'dashboard', parent: 'biometrico.asistencia', breadcrumb: { text: 'Detalle de Asistencia' } } },
    {
        path: '/moduloAsignado',
        name: 'moduloAsignado',
        component: () => import('../pages/Docente/DocenteModuloAsignado.vue'),
        meta: {
            layout: 'dashboard',
            permissions: [
                'ver-perfil-docente',
                'editar-perfil-docente',
                'ver-mis-modulos',
                'ver-estudiantes-asignados',
            ],
            breadcrumb: [{ text: 'Grupos', to: { name: 'grupo' } }],
        }
    },
    ////////////// DOCENTE SUS MODULOS 
    {
        path: '/docente/modulo/:id',
        name: 'docente.modulo.detalle',
        component: () => import('../pages/Docente/DocenteModuloDetalle.vue'),
        props: true,
        redirect: route => ({ name: 'docente.modulo.detalle.documentos', params: { id: route.params.id } }),
        meta: {
            layout: 'dashboard',
            permissions: ['ver-estudiantes-asignados'],
            parent: 'moduloAsignado',
            breadcrumb: [{ text: 'Mis Módulos', to: { name: 'moduloAsignado' } }],
            submenu: (route) => [
                { text: 'Documentos', to: { name: 'docente.modulo.detalle.documentos', params: { id: route.params.id } } },
                { text: 'Documentos', to: { name: 'docente.modulo.detalle.asistencia', params: { id: route.params.id } } },
            ],
        },
        children: [
            {
                path: 'documentos',
                name: 'docente.modulo.detalle.documentos',
                component: () => import('../pages/Docente/DocenteModuloDetalleDocumento.vue'),
                props: route => ({ id: route.params.id }),
                meta: {
                    parent: 'docente.modulo.detalle',
                    breadcrumb: { text: 'Documentos' },
                },
            },
            {
                path: 'asistencia',
                name: 'docente.modulo.detalle.asistencia',
                component: () => import('../pages/Docente/DocenteModuloDetalleAsistencia.vue'),
                props: true,
                meta: {
                    parent: 'docente.modulo.detalle',
                    breadcrumb: { text: 'Asistencia' },
                },
            },
        ],
    },

    ///////////////////////////////
    {
        path: '/documentos',
        name: 'documentos',
        component: () => import('../pages/Documento/Documento.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
            breadcrumb: [{ text: 'Gestión de Documentos', to: { name: 'documentos' } }],
        }
    },
    {
        path: '/programacion/:id',
        name: 'programacion.detalle',
        component: () => import('../pages/Documento/ProgramacionDetalle.vue'),
        props: true,
        meta: {
            layout: 'dashboard',
            parent: 'documentos',
            breadcrumb: { text: 'Estado de Entregas por Grupo' }
        }
    },

];