import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEspecialidadProgramaStore = defineStore('especialidad_programa', () => {
    const {
        index: getEspecialidadPrograma,
        show: getEspecialidadProgramaById,
        loading: especialidadProgramaLoading,
        initialLoading: especialidadProgramaFirstTimeLoading,
    } = useHttpRequest('/especialidad_programa');

    const especialidadPrograma = ref([]);
    const especialidadProgramaFiltrado = ref(null);
    const loadEspecialidadPrograma = async () => {
        const res = await getEspecialidadPrograma();
        especialidadPrograma.value = res;
    };

    const loadEspecialidadProgramaById = async (id) => {

                console.log('respuesta del show 1111', id)

        const res = await getEspecialidadProgramaById(id);
        console.log('respuesta del show', res)
        especialidadProgramaFiltrado.value = res;
    };

    return {
        especialidadPrograma,
        especialidadProgramaFiltrado,
        loadEspecialidadPrograma,
        loadEspecialidadProgramaById,
        especialidadProgramaLoading,
        especialidadProgramaFirstTimeLoading,
    };
});

export default useEspecialidadProgramaStore;
