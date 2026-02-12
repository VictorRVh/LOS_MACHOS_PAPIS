import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEstadistica104Store = defineStore('estadistica104', () => {
    const {
        indexWithParams: getEstadistica104,
        loading: estadistica104Loading,
        initialLoading: estadistica104FirstTimeLoading,
    } = useHttpRequest('/estadistica104');

    const estadistica104 = ref([]);
    const loadEstadistica104 = async (fechStart, fechEnd) => {
        const res = await getEstadistica104({ fechStart, fechEnd });
        estadistica104.value = res;
    };

    return {
        estadistica104,
        loadEstadistica104,
        estadistica104Loading,
        estadistica104FirstTimeLoading,
    };
});

export default useEstadistica104Store;
