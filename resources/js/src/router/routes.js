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
        path: '/users',
        name: 'users',
        component: () => import('../pages/Users.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-usuarios', 'users-view'],
        },
    },
    {
        path: '/roles',
        name: 'roles',
        component: () => import('../pages/Roles.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['roles-all', 'roles-view'],
        },
    },
    {
       
        path: '/permissions',
        name: 'permissions',
        component: () => import('../pages/newPermission.vue'), 
        meta: {
            layout: 'dashboard',
            permissions: ['permissions-all', 'permissions-view'],
        },
    },
];