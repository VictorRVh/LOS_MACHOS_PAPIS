import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useCapacidadesStore = defineStore('Capacidades', () => {
    const {
        index: getCapacidades,
        loading: capacidadesLoading,
        initialLoading: capacidadesFirstTimeLoading,
    } = useHttpRequest('/capacidad_terminal_competencia');

    const capacidades = ref([]);
    const loadCapacidades = async (idGrupo) => {
        const res = await getCapacidades();
        capacidades.value = res;
    };
    
    return {
        capacidades,
        loadCapacidades,
        capacidadesLoading,
        capacidadesFirstTimeLoading,
    };
});

export default useCapacidadesStore;
