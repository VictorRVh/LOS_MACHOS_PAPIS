import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useProgramaStore = defineStore('programa_estudio', () => {
    const {
        index: getProgramas,
        loading: programasLoading,
        initialLoading: programasFirstTimeLoading,
    } = useHttpRequest('/programa_estudio');

    const programas = ref([]);

    const loadProgramas = async () => {
            const res = await getProgramas();
            programas.value = res;
    };

    return {
        programas,
        loadProgramas,
        programasLoading,
        programasFirstTimeLoading,
    };
});

export default useProgramaStore;
