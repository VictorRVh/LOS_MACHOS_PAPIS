import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useProgramacionSubidostore = defineStore('lista_programacion', () => {
    const {
        index: getProgramacionSubidosList,
        show: getProgramacionSubidos,
        loading: programacionSubidosLoading,
        initialLoading: programacionSubidosFirstTimeLoading,
    } = useHttpRequest('/lista_programacion');

    const programacionSubidos = ref([]);
    const loadgetProgramacionSubidos = async (id) => {
        const res = await getProgramacionSubidos(id);
        programacionSubidos.value = res;
    };

    return {
        programacionSubidos,
        loadgetProgramacionSubidos,
        programacionSubidosLoading,
        programacionSubidosFirstTimeLoading,
        
    };
});

export default useProgramacionSubidostore;
