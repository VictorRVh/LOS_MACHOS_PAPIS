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

    const programacionAdmin = ref([]);
    const programacionPorGrupo = ref([]);


    const loadgetProgramacionAdminByPerido = async (id) => {
        console.log("gola al id: ",id)
        const res = await getProgramacionAdminByPerido(id);
        console.log("gola al otodo",res)
        programacionAdmin.value = res;
    };

    const loadGetProgramacionByGrupo = async (id) => {
        const res = await getProgramacionByGrupo(id);
        programacionPorGrupo.value = res;
    };

    return {
        programacionAdmin,
        loadgetProgramacionAdminByPerido,
        ProgramacionAdminLoading,
        ProgramacionAdminFirstTimeLoading,

        loadGetProgramacionByGrupo,
        programacionPorGrupo,
        ProgramacionByGrupoLoading
    };
});

export default useProgramacionAdmintore;
