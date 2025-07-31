import Breadcrumbs from '../components/breadcrumbs/Breadcrumbs.vue';

export default [
    {
        path: '/',
        name: 'login',
        component: () => import('../pages/Login.vue'),
        meta: {
            layout: 'full',
            permissions: [],
        },
    },
    {
        path: '/start',
        name: 'start',
        component: () => import('../pages/Start.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-usuarios', 'ver-usuarios'],
            breadcrumb: [{ name: 'Inicio', to: { name: 'start' } }]
        },
    },
    {
        path: '/groups',
        name: 'groups',
        component: () => import('../pages/Users.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-usuarios', 'ver-usuarios'],
        },
    },
    {
        path: '/programs',
        name: 'programs',
        component: () => import('../pages/Users.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-usuarios', 'ver-usuarios'],
        },
    },
    {
        path: '/califications',
        name: 'califications',
        component: () => import('../pages/Users.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-usuarios', 'ver-usuarios'],
        },
    },
    {
        path: '/users',
        name: 'users',
        component: () => import('../pages/Users.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-usuarios', 'ver-usuarios'],
            breadcrumb: [{ name: 'Usuarios', to: { name: 'users' } }]
        },
    },
    {
        path: '/users/crear',
        name: 'users.crear',
        component: () => import('../pages/Users.vue'), // Asumo que te lleva a la misma página para abrir un slider
        meta: {
            layout: 'dashboard',
            permissions: ['crear-usuarios'],
            breadcrumb: [
                { name: 'Usuarios', to: { name: 'users' } },
                { name: 'Crear' }
            ]
        }
    },
    {
        path: '/roles',
        name: 'roles',
        component: () => import('../pages/Roles.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-roles', 'ver-roles'],
            breadcrumb: [{ name: 'Roles', to: { name: 'roles' } }]
        }
    },
    {
        path: '/roles/crear',
        name: 'roles.crear',
        component: () => import('../pages/RolesCrear.vue'),
        meta: {
            layout: 'dashboard',
            breadcrumb: [
                { name: 'Roles', to: { name: 'roles' } },
                { name: 'Crear' }
            ]
        }
    },
    {
        path: '/roles/confirmar',
        name: 'roles.confirmar',
        component: () => import('../pages/Confirmar.vue'),
        meta: {
            layout: 'dashboard',
            breadcrumb: [
                { name: 'Roles', to: { name: 'roles' } },
                { name: 'Crear', to: { name: 'roles.crear' } },
                { name: 'Confirmar' }
            ]
        }
    },
    {
        path: '/permissions',
        name: 'permissions',
        component: () => import('../pages/newPermission.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: [{ name: 'Permisos', to: { name: 'permissions' } }]
        },
    },
    {
        path: '/docente',
        name: 'docente',
        component: () => import('../pages/Docente/Docente.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: [{ name: 'Docentes', to: { name: 'docente' } }]
        },
    },
    {
        path: '/convenio',
        name: 'convenio',
        component: () => import('../pages/Convenio/Convenio.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: [{ name: 'Convenios', to: { name: 'convenio' } }]
        },
    },
    {
        path: '/periodo',
        name: 'periodo',
        component: () => import('../pages/Periodo/Periodo.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: [{ name: 'Periodos', to: { name: 'periodo' } }]
        },
    },
    {
        path: '/administrativos',
        name: 'administrativos',
        component: () => import('../pages/Administrativo/Administrativo.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: [{ name: 'Administrativos', to: { name: 'administrativos' } }]
        },
    },
    {
        path: '/especialidad',
        name: 'especialidad',
        component: () => import('../pages/Especialidad/Especialidad.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: [{ name: 'Especialidades', to: { name: 'especialidad' } }]
        },
    },
    {
        path: '/comision',
        name: 'comision',
        component: () => import('../pages/Comision/Comision.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: [{ name: 'Comisiones', to: { name: 'comision' } }]
        },
    },
    {
        path: '/programa',
        name: 'programa',
        component: () => import('../pages/Programa/Programa.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: [{ name: 'Programas de Estudio', to: { name: 'programa' } }]
        },
    },
    {
        path: '/programa/crear',
        name: 'programa.crear',
        component: () => import('../pages/Programa/Programa.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: [
                { name: 'Programas de Estudio', to: { name: 'programa' } },
                { name: 'Crear' }
            ]
        },
    },
    {
        path: '/programa/editar/:id',
        name: 'programa.editar',
        component: () => import('../pages/Programa/Programa.vue'),
        props: true,
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: [{ name: 'Programas de Estudio', to: { name: 'programa' } }]
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
        },
    },
    {
        path: '/especialidadPrograma/:id/editar',
        name: 'especialidadPrograma.editar',
        component: () => import('../pages/EspecialidadPrograma/EspecialidadPrograma.vue'),
        props: true,
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
        },
    },
    {
        path: '/matricula',
        name: 'matricula',
        component: () => import('../pages/Estudiante/Estudiante.vue'),
        props: true,
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: [{ name: 'Matrícula', to: { name: 'matricula' } }]
        },
    },
    {
        path: '/modulo/:idEspecialidadPrograma',
        name: 'modulo',
        component: () => import('../pages/Modulo/Modulo.vue'),
        props: true,
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
        },
    },
    {
        path: '/grupo',
        name: 'grupo',
        component: () => import('../pages/Grupo/Grupo.vue'),
        props: true,
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
        },
    },
];