import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useExperienciaFormativaStore = defineStore('ExperienciaFormativa', () => {


    const {
        show: getExperienciaFormativaByGrupo,
        loading: ExperienciaFormativaByGrupoLoading,
        initialLoading: ExperienciaFormativaByGrupoFirstTimeLoading,
    } = useHttpRequest('/experiencia_formativa_index');

    const ExperienciaFormativaPorGrupo = ref([]);

    const loadgetExperienciaFormativaByGrupo = async (id) => {
        const res = await getExperienciaFormativaByGrupo(id);
        ExperienciaFormativaPorGrupo.value = res;
    };

    return {
        loadgetExperienciaFormativaByGrupo,
        ExperienciaFormativaPorGrupo
    };
});

export default useExperienciaFormativaStore;
