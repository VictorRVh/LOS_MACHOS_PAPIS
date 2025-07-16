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
        component: () => import('../pages/Users.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-usuarios', 'ver-usuarios'],
            breadcrumb: 'Start'
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
            breadcrumb: 'Usuarios'
        },
    },
    {
        path: '/roles',
        name: 'roles',
        component: () => import('../pages/Roles.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-roles', 'ver-roles'],
            breadcrumb: [
                { name: 'ROLES', to: { name: 'roles' } }
            ]
        }
    },
    {
        path: '/roles/crear',
        name: 'roles.crear',
        component: () => import('../pages/RolesCrear.vue'),
        meta: {
            layout: 'dashboard',
            breadcrumb: [
                { name: 'ROLES', to: { name: 'roles' } },
                { name: 'CREAR' }
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
                { name: 'ROLES', to: { name: 'roles' } },
                { name: 'CREAR', to: { name: 'roles.crear' } },
                { name: 'CONFIRMAR' }
            ]
        }
    }
    ,
    {

        path: '/permissions',
        name: 'permissions',
        component: () => import('../pages/newPermission.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: 'Permisos'
        },
    },
    ///teacher
    {

        path: '/docente',
        name: 'docente',
        component: () => import('../pages/Docente/Docente.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: 'Permisos'
        },
    },

    // CONVENIO
    {

        path: '/convenio',
        name: 'convenio',
        component: () => import('../pages/Convenio/Convenio.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: 'Permisos'
        },
    },
    {

        path: '/periodo',
        name: 'periodo',
        component: () => import('../pages/Periodo/Periodo.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: 'Permisos'
        },
    },
    {

        path: '/administrativos',
        name: 'administrativos',
        component: () => import('../pages/Periodo/Periodo.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: 'Permisos'
        },
    },
    {

        path: '/especialidad',
        name: 'especialidad',
        component: () => import('../pages/Periodo/Periodo.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
            breadcrumb: 'Permisos'
        },
    },
];