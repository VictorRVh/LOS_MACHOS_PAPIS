import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useCapacidadesStore = defineStore('Capacidades_academico', () => {
    const {
        show: getCapacidades,
        loading: capacidadesLoading,
        initialLoading: capacidadesFirstTimeLoading,
    } = useHttpRequest('/capacidad_competencia_index');

    const capacidades = ref([]);
    const loadCapacidades = async (idGrupo) => {
        const res = await getCapacidades(idGrupo);
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
