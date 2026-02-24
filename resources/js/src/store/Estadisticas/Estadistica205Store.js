import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEstadistica205Store = defineStore('Estadistica205', () => {
    const {
        indexWithParams: getEstadistica205,
        loading: estadistica205Loading,
        initialLoading: estadistica205FirstTimeLoading,
    } = useHttpRequest('/estadistica205');

    const estadistica205 = ref([]);
    const loadEstadistica205 = async (fechStart, fechEnd) => {
        const res = await getEstadistica205({ fechStart, fechEnd });
        estadistica205.value = res;
    };

    return {
        estadistica205,
        loadEstadistica205,
        estadistica205Loading,
        estadistica205FirstTimeLoading,
    };
});

export default useEstadistica205Store;
