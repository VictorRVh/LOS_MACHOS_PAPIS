import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEstadistica202Store = defineStore('estadistica202', () => {
    const {
        indexWithParams: getEstadistica202,
        loading: estadistica202Loading,
        initialLoading: estadistica202FirstTimeLoading,
    } = useHttpRequest('/estadistica202');

    const estadistica202 = ref([]);
    const loadEstadistica202 = async (fechStart, fechEnd) => {
        const res = await getEstadistica202({ fechStart, fechEnd });
        estadistica202.value = res;
    };

    return {
        estadistica202,
        loadEstadistica202,
        estadistica202Loading,
        estadistica202FirstTimeLoading,
    };
});

export default useEstadistica202Store;
