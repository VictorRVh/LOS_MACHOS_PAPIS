import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useExperienciaFormativaStore = defineStore('ExperienciaFormativa', () => {


    const {
        show: getExperienciaFormativaByGrupo,
        loading: ExperienciaFormativaByGrupoLoading,
        initialLoading: ExperienciaFormativaByGrupoFirstTimeLoading,
    } = useHttpRequest('/experiencia_formativa_index');

    const {
        show: getDriveFolderId,
        // loading: ExperienciaFormativaByGrupoLoading,
        // initialLoading: ExperienciaFormativaByGrupoFirstTimeLoading,
    } = useHttpRequest('/experiencia_formativa_folderDrive');

    const experienciaFormativaPorGrupo = ref([]);
    const driveFolderId = ref([]);

    const loadGetExperienciaFormativaByGrupo = async (id) => {
        const res = await getExperienciaFormativaByGrupo(id);
        experienciaFormativaPorGrupo.value = res;
    };

    const loadDriveFolderId = async (id) => {
        const res = await getDriveFolderId(id);
        driveFolderId.value = res;
    };

    return {
        loadGetExperienciaFormativaByGrupo,
        experienciaFormativaPorGrupo,

        loadDriveFolderId,
        driveFolderId
    };
});

export default useExperienciaFormativaStore;
