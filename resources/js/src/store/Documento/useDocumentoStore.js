import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useProgramacionAdmintore = defineStore('programacion_admin_c', () => {
    const {
        index: getProgramacionAdmin,
        show: getProgramacionAdminByPerido,
        loading: ProgramacionAdminLoading,
        initialLoading: ProgramacionAdminFirstTimeLoading,
    } = useHttpRequest('/programacion_admin');

    const {
        show: getProgramacionByGrupo,
        loading: ProgramacionByGrupoLoading,
        initialLoading: ProgramacionByGrupoFirstTimeLoading,
    } = useHttpRequest('/programacion_grupo');
    
    const {
        show: getGrupoByGrupo,
        loading: grupoByGrupoLoading,
        initialLoading: grupoByGrupoFirstTimeLoading,
    } = useHttpRequest('/grupoByPeriodo');

    const programacionAdmin = ref([]);
    const programacionPorGrupo = ref([]);
    const gruposByPeriodo = ref([]);

    const loadgetProgramacionAdminByPerido = async (id) => {
        const res = await getProgramacionAdminByPerido(id);
        programacionAdmin.value = res;
    };

    const loadGetProgramacionByGrupo = async (id) => {
        const res = await getProgramacionByGrupo(id);
        programacionPorGrupo.value = res;
    };
    
    const loadGruposByPeriodo = async (id) => {
        const res = await getGrupoByGrupo(id);
        gruposByPeriodo.value = res;
    };

    return {
        programacionAdmin,
        loadgetProgramacionAdminByPerido,
        ProgramacionAdminLoading,
        ProgramacionAdminFirstTimeLoading,

        loadGetProgramacionByGrupo,
        programacionPorGrupo,
        ProgramacionByGrupoLoading,
        ProgramacionByGrupoFirstTimeLoading,

        loadGruposByPeriodo,
        gruposByPeriodo
    };
});

export default useProgramacionAdmintore;
