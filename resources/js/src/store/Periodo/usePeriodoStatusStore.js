
import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const usePeriodosStore = defineStore('Periodos_filter', () => {
    const {
        index: getPeriodos,
        loading: periodosLoading,
        initialLoading: periodosFirstTimeLoading,
    } = useHttpRequest('/periodo_filter_status');

    const periodos = ref([]);
    const loadPeriodos = async () => {
        const res = await getPeriodos();
        periodos.value = res;
    };

    return {
        periodos,
        loadPeriodos,
        periodosLoading,
        periodosFirstTimeLoading,
    };
});

export default usePeriodosStore;
