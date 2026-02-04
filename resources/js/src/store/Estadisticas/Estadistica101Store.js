import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEstadistica101Store = defineStore('estadistica101', () => {
    const {
        indexWithParams: getEstadistica101,
        loading: estadistica101Loading,
        initialLoading: estadistica101FirstTimeLoading,
    } = useHttpRequest('/estadistica101');

    const estadistica101 = ref([]);
    const loadEstadistica101 = async (fechStart, fechEnd) => {
        const res = await getEstadistica101({ fechStart, fechEnd });
        estadistica101.value = res;
    };

    return {
        estadistica101,
        loadEstadistica101,
        estadistica101Loading,
        estadistica101FirstTimeLoading,
    };
});

export default useEstadistica101Store;
