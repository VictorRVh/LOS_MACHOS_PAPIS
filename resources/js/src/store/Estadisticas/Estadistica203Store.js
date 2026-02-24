import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEstadistica203Store = defineStore('Estadistica203', () => {
    const {
        indexWithParams: getEstadistica203,
        loading: estadistica203Loading,
        initialLoading: estadistica203FirstTimeLoading,
    } = useHttpRequest('/estadistica203');

    const estadistica203 = ref([]);
    const loadEstadistica203 = async (fechStart, fechEnd) => {
        const res = await getEstadistica203({ fechStart, fechEnd });
        estadistica203.value = res;
    };

    return {
        estadistica203,
        loadEstadistica203,
        estadistica203Loading,
        estadistica203FirstTimeLoading,
    };
});

export default useEstadistica203Store;
