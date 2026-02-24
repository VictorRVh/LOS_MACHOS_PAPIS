import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEstadistica201Store = defineStore('estadistica201', () => {
    const {
        indexWithParams: getEstadistica201,
        loading: estadistica201Loading,
        initialLoading: estadistica201FirstTimeLoading,
    } = useHttpRequest('/estadistica201');

    const estadistica201 = ref([]);
    const loadEstadistica201 = async (fechStart, fechEnd) => {
        const res = await getEstadistica201({ fechStart, fechEnd });
        estadistica201.value = res;
    };

    return {
        estadistica201,
        loadEstadistica201,
        estadistica201Loading,
        estadistica201FirstTimeLoading,
    };
});

export default useEstadistica201Store;
