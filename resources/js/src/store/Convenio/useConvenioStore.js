import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useConveniosStore = defineStore('convenios', () => {
    const {
        index: getConvenios,
        loading: conveniosLoading,
        initialLoading: conveniosFirstTimeLoading,
    } = useHttpRequest('/convenio');

    const convenios = ref([]);
    const loadConvenios = async () => {
        const res = await getConvenios();
        convenios.value = res;
    };

    return {
        convenios,
        loadConvenios,
        conveniosLoading,
        conveniosFirstTimeLoading,
    };
});

export default useConveniosStore;
