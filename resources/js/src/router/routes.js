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
        },
    },
    {
        path: '/roles',
        name: 'roles',
        component: () => import('../pages/Roles.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-roles', 'ver-roles'],
        },
    },
    {
       
        path: '/permissions',
        name: 'permissions',
        component: () => import('../pages/newPermission.vue'), 
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos', 'ver-permisos'],
        },
    },
];