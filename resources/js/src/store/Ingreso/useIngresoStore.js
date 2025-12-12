import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useIngresosStore = defineStore('Ingresos', () => {
    const {
        show: getIngresos,
        loading: ingresosLoading,
        initialLoading: ingresosFirstTimeLoading,
    } = useHttpRequest('/ingresos');

    const ingresos = ref([]);
    const loadIngresos = async (idPerido) => {
        const res = await getIngresos(idPerido);
        ingresos.value = res;
    };

    return {
        ingresos,
        loadIngresos,
        ingresosLoading,
        ingresosFirstTimeLoading,
    };
});

export default useIngresosStore;
