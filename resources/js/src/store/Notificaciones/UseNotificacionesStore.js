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

    const notificaciones = ref([]);
    const notificacionesFiltrado = ref(null);

    const loadNotificaciones = async () => {
        const res = await getNotificaciones();
        notificaciones.value = res;
    };

    return {
        notificaciones,
        loadNotificaciones,
        notificacionesFiltrado,
        notificacionesLoading,
        notificacionesFirstTimeLoading,
    };
});

export default useNotificacionesStore;
