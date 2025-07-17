import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useProgramaStore = defineStore('programa_estudio', () => {
    const {
        index: getPrograma,
        loading: programaLoading,
        initialLoading: programaFirstTimeLoading,
    } = useHttpRequest('/programa_estudio');

    const programa = ref([]);
    const loadPrograma = async () => {
        const res = await getPrograma();
        programa.value = res;
    };

    return {
        programa,
        loadPrograma,
        programaLoading,
        programaFirstTimeLoading,
    };
});

export default useProgramaStore;
