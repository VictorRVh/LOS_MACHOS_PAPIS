import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const usePeriodosStore = defineStore('Periodos', () => {
    const {
        index: getPeriodos,
        show: exportMatriculaInstitucinal,
        loading: periodosLoading,
        initialLoading: periodosFirstTimeLoading,
    } = useHttpRequest('/periodo');

    const periodos = ref([]);
    const loadPeriodos = async () => {
        const res = await getPeriodos();
        periodos.value = res;
    };

    const loadExportMatriculasInst = async (idPeriodo) => {
        const res = await exportMatriculaInstitucinal(idPeriodo);
        //  periodos.value = res;
    };

    return {
        periodos,
        loadPeriodos,
        loadExportMatriculasInst,
        periodosLoading,
        periodosFirstTimeLoading,
    };
});

export default usePeriodosStore;
