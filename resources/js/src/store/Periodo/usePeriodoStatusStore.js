
import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const usePeriodosStore = defineStore('Periodos_filter', () => {
    const {
        index: getPeriodos,
        loading: periodosLoading,
        initialLoading: periodosFirstTimeLoading,
    } = useHttpRequest('/periodo_filter_status');

    const {
        index: getPeriodosAnios,
        loading: periodosAniosLoading,
        initialLoading: periodosAniosFirstTimeLoading,
    } = useHttpRequest('/periodosAnios');

    const {
        show: getPeriodosAniosFiltrado,
        loading: periodosAniosFiltradoLoading,
        initialLoading: periodosAniosFiltradoFirstTimeLoading,
    } = useHttpRequest('/periodosAniosFiltrado');

    const periodos = ref([]);
    const periodosAnios = ref([]);
    const periodosAniosFiltrado = ref([]);

    const loadPeriodos = async () => {
        const res = await getPeriodos();
        periodos.value = res;
    };

    const loadPeriodosAnios = async () => {
        const res = await getPeriodosAnios();
        periodosAnios.value = res;
    };

    const loadPeriodosAniosFiltrado = async (anio) => {
        const res = await getPeriodosAniosFiltrado(anio);
        periodosAniosFiltrado.value = res;
    };

    return {
        periodos,
        loadPeriodos,
        periodosLoading,
        periodosFirstTimeLoading,

        loadPeriodosAnios,
        periodosAnios,
        periodosAniosLoading,

        loadPeriodosAniosFiltrado,
        periodosAniosFiltrado,
        periodosAniosFiltradoLoading
    };
});

export default usePeriodosStore;
