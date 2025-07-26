import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useModuloStore = defineStore('modulo', () => {
    const {
        index: getModulo,
        show: getModuloById,
        loading: moduloLoading,
        initialLoading: moduloFirstTimeLoading,
    } = useHttpRequest('/modulo');

    const modulo = ref([]);
    const moduloFiltrado = ref(null);

    const loadModulo = async () => {
        const res = await getModulo();
        modulo.value = res;
    };

    const loadModuloById = async (id) => {
        const res = await getModuloById(id);
        console.log('respuesta del show', res)
        moduloFiltrado.value = res;
    };

    return {
        modulo,
        loadModulo,
        loadModuloById,
        moduloFiltrado,
        moduloLoading,
        moduloFirstTimeLoading,
    };
});

export default useModuloStore;
