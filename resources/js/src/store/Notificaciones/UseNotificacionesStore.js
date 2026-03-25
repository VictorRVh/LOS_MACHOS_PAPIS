// useNotificacionesStore.js
import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useNotificacionesStore = defineStore('Notificaciones', () => {
    const {
        index: getNotificaciones,
        loading: notificacionesLoading,
        initialLoading: notificacionesFirstTimeLoading,
    } = useHttpRequest('/notificaciones');

    const {
        index: getNotificacionesMarcarTodo,
    } = useHttpRequest('/notificaciones/marcar-todo');

    const {
        show: getNotificacionesLeerById,
    } = useHttpRequest('/notificaciones/leer');

    const notificaciones = ref([]);

    // ✅ Una sola fuente de verdad — computed, no ref separada
    const notificacionesPendientes = computed(
        () => notificaciones.value.filter(n => n.leido == 0).length
    );

    const loadNotificaciones = async () => {
        const res = await getNotificaciones();
        notificaciones.value = res ?? [];
        // notificacionesPendientes se actualiza solo por ser computed
    };

    const loadNotificacionesMarcarTodo = async () => {
        await getNotificacionesMarcarTodo();
        // Actualiza el array local sin volver a llamar al backend
        notificaciones.value = notificaciones.value.map(n => ({ ...n, leido: 1 }));
    };

    const loadNotificacionesMarcarLeido = async (id) => {
        await getNotificacionesLeerById(id);
        const n = notificaciones.value.find(n => n.id === id);
        if (n) n.leido = 1; // Actualiza local, sin refetch
    };

    return {
        notificaciones,
        notificacionesPendientes,
        notificacionesLoading,
        notificacionesFirstTimeLoading,
        loadNotificaciones,
        loadNotificacionesMarcarTodo,
        loadNotificacionesMarcarLeido,
    };
});

export default useNotificacionesStore;