import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useCicloStore = defineStore('ciclo_academico', () => {
    const {
        index: getCiclo,
        loading: cicloLoading,
        initialLoading: cicloFirstTimeLoading,
    } = useHttpRequest('/ciclo_academico');

    const ciclo = ref([]);
    const loadCiclo = async () => {
        const res = await getCiclo();
        console.log('response ciclo vic', res)
        ciclo.value = res;
    };

    return {
        ciclo,
        loadCiclo,
        cicloLoading,
        cicloFirstTimeLoading,
    };
});

export default useCicloStore;
