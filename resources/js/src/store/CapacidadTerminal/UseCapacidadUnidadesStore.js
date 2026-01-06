import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useUnidadesDidacticasStore = defineStore('unidades_didacticas_capacidades', () => {
    const {
        show: getUnidadesDidacticas,
        loading: unidadesDidacticasLoading,
        initialLoading: unidadesDidacticasFirstTimeLoading,
    } = useHttpRequest('/unidades_didacticas');

    const unidadesDidacticas = ref([]);
    const loadUnidadesDidacticas = async (idGrupo) => {
        const res = await getUnidadesDidacticas(idGrupo);
        unidadesDidacticas.value = res;
    };

    return {
        unidadesDidacticas,
        loadUnidadesDidacticas,
        unidadesDidacticasLoading,
        unidadesDidacticasFirstTimeLoading,
    };
});

export default useUnidadesDidacticasStore;
