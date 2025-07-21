import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEspecialidadStore = defineStore('especialidad_madre', () => {
    const {
        index: getEspecialidad,
        loading: especialidadLoading,
        initialLoading: especialidadFirstTimeLoading,
    } = useHttpRequest('/especialidad_madre');

    const especialidad = ref([]);
    const loadEspecialidad = async () => {
        const res = await getEspecialidad();
        especialidad.value = res;
    };

    return {
        especialidad,
        loadEspecialidad,
        especialidadLoading,
        especialidadFirstTimeLoading,
    };
});

export default useEspecialidadStore;
