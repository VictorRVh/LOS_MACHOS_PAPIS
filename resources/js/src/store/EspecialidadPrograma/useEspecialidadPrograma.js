import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEspecialidadProgramaStore = defineStore('especialidad_programa', () => {
    const {
        index: getEspecialidadPrograma,
        loading: especialidadProgramaLoading,
        initialLoading: especialidadProgramaFirstTimeLoading,
    } = useHttpRequest('/especialidad_programa');

    const especialidadPrograma = ref([]);
    const loadEspecialidadPrograma = async () => {
        const res = await getEspecialidadPrograma();
        especialidadPrograma.value = res;
    };

    return {
        especialidadPrograma,
        loadEspecialidadPrograma,
        especialidadProgramaLoading,
        especialidadProgramaFirstTimeLoading,
    };
});

export default useEspecialidadProgramaStore;
