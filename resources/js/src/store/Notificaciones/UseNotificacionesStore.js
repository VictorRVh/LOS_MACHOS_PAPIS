import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useNotificacionesStore = defineStore('Notificaciones', () => {
    const {
        index: getNotificaciones,
        show: getNotificacionesById,
        loading: notificacionesLoading,
        initialLoading: notificacionesFirstTimeLoading,
    } = useHttpRequest('/notificaciones');

    const {
        index: getNotificacionesMarcarTodo,
        show: getNotificacionesMarcarTodoById,
        loading: notificacionesMarcarTodoLoading,
        initialLoading: notificacionesMarcarTodoFirstTimeLoading,
    } = useHttpRequest('/notificaciones/marcar-todo');

    const {
        index: getNotificacionesLeer,
        show: getNotificacionesLeerById,
        loading: notificacionesLeerLoading,
        initialLoading: notificacionesLeerFirstTimeLoading,
    } = useHttpRequest('/notificaciones/leer');

    const {
        index: getNotificacionesPendientes,
        show: getNotificacionesPendientesById,
        loading: notificacionesPendientesLoading,
        initialLoading: notificacionesPendientesFirstTimeLoading,
    } = useHttpRequest('/notificaciones/pendientes');

    const notificaciones = ref([]);
    const notificacionesFiltrado = ref(null);

    const notificacionesPendientes = ref(0);

    const loadNotificaciones = async () => {
        const res = await getNotificaciones();
        notificaciones.value = res;
        notificacionesPendientes.value = res.filter(n => n.leido == 0).length;
    };

    const loadNotificacionesMarcarTodo = async () => {
        const res = await getNotificacionesMarcarTodo();
        return res;
    };

    const loadNotificacionesMarcarLeido = async (id) => {
        const res = await getNotificacionesLeerById(id);
        return res;
    };

    const loadNotificacionesPendientes = async () => {
        const res = await getNotificacionesPendientes()
        notificacionesPendientes.value = res;
    }

    return {
        notificaciones,
        loadNotificaciones,
        notificacionesFiltrado,
        notificacionesLoading,
        notificacionesFirstTimeLoading,

        loadNotificacionesMarcarTodo,
        loadNotificacionesMarcarLeido,
        loadNotificacionesPendientes,

        notificacionesPendientes,
    };
});

export default useNotificacionesStore;
