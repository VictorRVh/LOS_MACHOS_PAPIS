import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useCompetenciasStore = defineStore('Competencias', () => {
    const {
        index: getCompetencias,
        loading: competenciasLoading,
        initialLoading: competenciasFirstTimeLoading,
    } = useHttpRequest('/competencias');

    const competencias = ref([]);
    const loadCompetencias = async (idGrupo) => {
        const res = await getCompetencias();
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
