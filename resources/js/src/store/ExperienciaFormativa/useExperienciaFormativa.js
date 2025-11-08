import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useExperienciaFormativaStore = defineStore('ExperienciaFormativa', () => {
    const {
        index: getExperienciaFormativaAdmin,
        show: getExperienciaFormativaAdminByPerido,
        loading: ExperienciaFormativaAdminLoading,
        initialLoading: ExperienciaFormativaAdminFirstTimeLoading,
    } = useHttpRequest('/experiencia_formativa');

    const {
        show: getExperienciaFormativaByGrupo,
        loading: ExperienciaFormativaByGrupoLoading,
        initialLoading: ExperienciaFormativaByGrupoFirstTimeLoading,
    } = useHttpRequest('/nota_experiencia_formativa');

    const ExperienciaFormativaAdmin = ref([]);
    const ExperienciaFormativaPorGrupo = ref([]);


    const loadgetExperienciaFormativaAdminByPerido = async (id) => {
        const res = await getExperienciaFormativaAdminByPerido(id);
        ExperienciaFormativaAdmin.value = res;
    };

    const loadGetExperienciaFormativaByGrupo = async (id) => {
        const res = await getExperienciaFormativaByGrupo(id);
        ExperienciaFormativaPorGrupo.value = res;
    };

    return {
        ExperienciaFormativa,
        loadgetExperienciaFormativaByPerido,
        ExperienciaFormativaLoading,
        ExperienciaFormativaFirstTimeLoading,

        loadGetExperienciaFormativaByGrupo,
        ExperienciaFormativaPorGrupo,
        ExperienciaFormativaByGrupoLoading,
        ExperienciaFormativaByGrupoFirstTimeLoading
    };
});

export default useExperienciaFormativaStore;
