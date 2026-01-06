import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useCompetenciasStore = defineStore('Competencias_academico', () => {
    const {
        show: getCompetencias,
        loading: competenciasLoading,
        initialLoading: competenciasFirstTimeLoading,
    } = useHttpRequest('/competencias_index');

    const competencias = ref([]);
    const loadCompetencias = async (idModulo) => {
        const res = await getCompetencias(idModulo);
        competencias.value = res;
    };

    return {
        competencias,
        loadCompetencias,
        competenciasLoading,
        competenciasFirstTimeLoading,
    };
});

export default useCompetenciasStore;
