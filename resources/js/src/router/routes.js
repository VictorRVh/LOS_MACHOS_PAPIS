

import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';



export default [
    { path: '/', name: 'login', component: () => import('../pages/Login.vue'), meta: { layout: 'full', permissions: [], }, },
    {
        path: '/consulta-notas',
        name: 'consulta.notas.publica',
        component: () => import('../pages/web/ConsultaNotas.vue'),
        meta: {
            layout: 'full',
            public: true,
        }
    },
    {
        path: '/verificarCertificado/:codigo',
        name: 'verificarCertificado',
        props: true,
        component: () => import('../pages/Estudiante/VerificarCertificado.vue'),
        meta: {
            public: true   // 👈 MUY IMPORTANTE
        }
    },
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
    { path: '/convenio', name: 'convenio', component: () => import('../pages/Convenio/Convenio.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Modalidades', to: { name: 'Modalidad' } }] }, },
    { path: '/periodo', name: 'periodo', component: () => import('../pages/Periodo/Periodo.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Periodos', to: { name: 'periodo' } }] }, },
    { path: '/administrativos', name: 'administrativos', component: () => import('../pages/Administrativo/Administrativo.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Administrativos', to: { name: 'administrativos' } }] }, },
    { path: '/especialidad', name: 'especialidad', component: () => import('../pages/Especialidad/Especialidad.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Especialidades', to: { name: 'especialidad' } }] }, },
    { path: '/comision', name: 'comision', component: () => import('../pages/Comision/Comision.vue'), meta: { layout: 'dashboard', permissions: ['todo-acceso-permisos'], breadcrumb: [{ text: 'Comisiones', to: { name: 'comision' } }] }, },

    { path: '/comisionDocente', name: 'comsion.docente', component: () => import('../pages/Docente-menu/DocenteComsion.vue'), meta: { layout: 'dashboard', permissions: ['ver-comisión-docente'], breadcrumb: [{ text: 'Comisiones', to: { name: 'comsion.docente' } }] }, },
    { path: '/buscar-estudiante', name: 'buscar.estudiante', component: () => import('../pages/Estudiante/BuscarEstudiante.vue'), meta: { layout: 'dashboard', permissions: ['ver-comisión-docente'], breadcrumb: [{ text: 'Buscar estudiante', to: { name: 'buscar.estudiante' } }] }, },
    {
        path: '/egresados',
        name: 'egresados',
        component: () => import('../pages/Estudiante/Egresados.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['ver-comisión-docente'],
            breadcrumb: [{ text: 'Egresados', to: { name: 'egresados' } }]
        }
    },
    {
        path: '/egresados/:id/:periodoId',
        name: 'egresadosLista',
        component: () => import('../pages/Estudiante/EgresadosLista.vue'),
        props: true,
        meta: {
            layout: 'dashboard',
            parent: 'egresados',
            breadcrumb: [
                { text: 'Lista de Egresados', to: { name: 'egresadosLista' } }
            ]
        }
    },
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
        redirect: { name: 'matricula.registrar' },
        props: true,
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
            breadcrumb: [{ text: 'Matrícula', to: { name: 'matricula.index' } }],
            submenu: () => [
                { text: 'Matricular', to: { name: 'matricula.registrar' } },
                { text: 'Lista por Grupos', to: { name: 'matricula.grupos' } },
                { text: 'Reservas', to: { name: 'matricula.reservas' } },
            ]
        },

        children: [
            {
                path: 'registrar',
                name: 'matricula.registrar',
                component: () => import('../components/page/Matricula/MatriculaSlider.vue'),
                meta: { breadcrumb: [{ text: 'Matricular' }] }
            },
            {
                path: 'grupos',
                name: 'matricula.grupos',
                props: true,
                component: () => import('../pages/Matricula/ListaGrupos.vue'),
                meta: {
                    layout: 'dashboard',
                    permissions: ['todo-acceso-permisos'],
                    breadcrumb: [{ text: 'Lista por Grupos', to: { name: 'matricula.grupos' } }]
                }
            },
            {
                path: 'reservas',
                name: 'matricula.reservas',

                component: () => import('../pages/Matricula/Reservas.vue'),
                meta: { breadcrumb: [{ text: 'Reservas' }] }
            },

            // 👉 DETALLE DE GRUPO (ALUMNOS) desde matrícula
            {
                path: 'grupos/:id/alumnos',
                name: 'matricula.grupos.alumnos',
                component: () => import('../pages/Matricula/GrupoDetalle.vue'),
                props: true,
                meta: {
                    parent: 'matricula.grupos',
                    breadcrumb: async (route) => {
                        const breadcrumbStore = useBreadcrumbStore();
                        let item = await breadcrumbStore.findTextById(route.params.id);
                        return {
                            text: item?.name || "Cargando...",
                            to: { name: "matricula.grupos.alumnos", params: { id: item?.id } }
                        };
                    }
                }
            },
            {
                path: ':id/editar',
                name: 'matricula.grupos.alumnos.editar',
                props: true,
                component: () => import('../components/page/Matricula/MatriculaEditarSlider.vue'),
                meta: {
                    parent: 'matricula.grupos.alumnos',
                    breadcrumb: async (route) => {
                        const breadcrumbStore = useBreadcrumbStore();
                        let item = await breadcrumbStore.findTextById(route.params.id);
                        return {
                            text: item?.name || "Cargando...",
                            to: { name: "matricula.grupos.alumnos.editar", params: { id: item?.id } }
                        };
                    }
                },
            }
        ]
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
                { text: 'Capacidades terminales', to: { name: 'grupo.capacidades.terminales', params: { id: route.params.id } } },
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
                path: 'capacidadesTerminales',
                name: 'grupo.capacidades.terminales',
                component: () => import('../pages/Grupo/GrupoCapacidadesTerminales.vue'),
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
    { path: '/notificaciones', name: 'notifi.index', component: () => import('../pages/FullNotificaciones.vue'), meta: { layout: 'dashboard', breadcrumb: [{ text: 'Todas las Notificaciones' }] } },
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
                'ver-mis-módulos',
                'ver-estudiantes-asignados',
            ],
            breadcrumb: [{ text: 'Modulos Asignados', to: { name: 'moduloAsignado' } }],
        }
    },
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
            breadcrumb: async (route) => {
                const breadcrumbStore = useBreadcrumbStore();
                // Buscar el nombre del módulo en el store
                let item = await breadcrumbStore.findTextById(route.params.id);
                return {
                    text: item?.name || "Cargando módulo...",
                    to: { name: route?.name, params: { id: route.params.id } },
                };
            },
            submenu: (route) => [
                { text: 'Documentos', to: { name: 'docente.modulo.detalle.documentos', params: { id: route.params.id } } },
                { text: 'Sesiones y asistencia', to: { name: 'docente.modulo.detalle.asistencia', params: { id: route.params.id } } },
                { text: 'Calificaciones', to: { name: 'docente.modulo.detalle.calificaciones', params: { id: route.params.id } } },
                { text: 'Prácticas', to: { name: 'docente.modulo.detalle.practicas', params: { id: route.params.id } } },
                { text: 'Alumnos', to: { name: 'docente.modulo.detalle.alumnos', params: { id: route.params.id } } },
                { text: 'Capacidades', to: { name: 'docente.modulo.detalle.capacidades', params: { id: route.params.id, idModulo: route.params.idModulo } } },
                { text: 'Unidades Didácticas', to: { name: 'docente.modulo.detalle.unidades', params: { id: route.params.id } } },
            ]
        },
        children: [
            {
                path: 'documentos',
                name: 'docente.modulo.detalle.documentos',
                component: () => import('../pages/Docente/DocenteModuloDetalleDocumento.vue'),
                props: true,
                meta: { parent: 'docente.modulo.detalle', breadcrumb: { text: 'Documentos' } }
            },
            {
                path: 'asistencia',
                name: 'docente.modulo.detalle.asistencia',
                component: () => import('../pages/Docente/DocenteModuloDetalleAsistencia.vue'),
                props: true,
                meta: { parent: 'docente.modulo.detalle', breadcrumb: { text: 'Sesiones y asistencia' } }
            },
            {
                path: 'calificaciones',
                name: 'docente.modulo.detalle.calificaciones',
                component: () => import('../pages/Docente/DocenteModuloCalificacion.vue'),
                props: true,
                meta: { parent: 'docente.modulo.detalle', breadcrumb: { text: 'Calificaciones' } }
            },
            {
                path: 'practicas',
                name: 'docente.modulo.detalle.practicas',
                component: () => import('../pages/Docente/DocenteModuloPracticas.vue'),
                props: true,
                meta: { parent: 'docente.modulo.detalle', breadcrumb: { text: 'Prácticas' } }
            },
            {
                path: 'alumnos',
                name: 'docente.modulo.detalle.alumnos',
                component: () => import('../pages/Docente/DocenteAlumnosList.vue'),
                props: true,
                meta: { parent: 'docente.modulo.detalle', breadcrumb: { text: 'Alumnos' } }
            },
            {
                path: 'capacidades/:idModulo',
                name: 'docente.modulo.detalle.capacidades',
                component: () => import('../pages/Docente/DocenteCapacidades.vue'),
                props: true,
                meta: { parent: 'docente.modulo.detalle', breadcrumb: { text: 'Capacidades' } }
            },
            {
                path: 'unidades',
                name: 'docente.modulo.detalle.unidades',
                component: () => import('../pages/Docente/DocenteCapacidadTerminal.vue'),
                props: true,
                meta: { parent: 'docente.modulo.detalle', breadcrumb: { text: 'Capacidades Terminales' } }
            },
        ]
    },
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
        path: '/ingresos',
        name: 'ingresos',
        component: () => import('../pages/Ingresos/Ingreso.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
            breadcrumb: [{ text: 'Gestión de ingresos', to: { name: 'ingresos' } }],
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
    {
        path: '/notificaciones',
        name: 'notificaciones.index',
        component: () => import('../pages/Notificacion.vue'),
        meta: {
            layout: 'dashboard',
            permissions: [],
            breadcrumb: [{ text: 'Todas las Notificaciones', to: { name: 'notificaciones.index' } }]
        }
    },

    {
        path: '/cetpro',
        name: 'cetpro.index',
        component: () => import('../pages/InfoCetpro.vue'),
        meta: {
            layout: 'dashboard',
            permissions: [],
            breadcrumb: [{ text: 'Ajustes', to: { name: 'cetpro.index' } }]
        }
    },

    {
        path: '/estadistica',
        name: 'estadistica',
        component: () => import('../pages/Estadistica/Estadistica.vue'),
        meta: {
            layout: 'dashboard',
            permissions: ['todo-acceso-permisos'],
            breadcrumb: [{ text: 'Estadísticas', to: { name: 'estadistica' } }]
        }
    },




];
