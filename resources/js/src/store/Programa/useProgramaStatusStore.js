import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useProgramaStore = defineStore('programa_estudio_status', () => {
    const {
        index: getProgramas,
        loading: programasLoading,
        initialLoading: programasFirstTimeLoading,
    } = useHttpRequest('/programa_estudio_status');

    const programa = ref([]);
    const loadPrograma = async () => {
        const res = await getProgramas();
        programa.value = res;
    };

    return {
        programa,
        loadPrograma,
        programasLoading,
        programasFirstTimeLoading,
    };
});

export default useProgramaStore;
