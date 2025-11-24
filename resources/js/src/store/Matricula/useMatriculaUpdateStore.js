import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useMatriculaUpdateStore = defineStore('matriculaUpdatealumno', () => {
    const {
        //index: getMatriculaUpdate,
        show: getMatriculaUpdate,
        loading: matriculaUpdateLoading,
        initialLoading: matriculaUpdateFirstTimeLoading,
    } = useHttpRequest('/matriculaUpdate');

    const matriculaUpdate = ref([]);
    const loadMatriculaUpdate = async (idMatricula) => {
        const res = await getMatriculaUpdate(idMatricula);
        matriculaUpdate.value = res;
    };

    return {
        matriculaUpdate,
        loadMatriculaUpdate,
        matriculaUpdateLoading,
        matriculaUpdateFirstTimeLoading,
    };
});

export default useMatriculaUpdateStore;
