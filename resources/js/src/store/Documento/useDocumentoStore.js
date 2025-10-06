import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useProgramacionAdmintore = defineStore('programacion_admin', () => {
    const {
        index: getProgramacionAdmin,
        show: getProgramacionAdminByPerido,
        loading: ProgramacionAdminLoading,
        initialLoading: ProgramacionAdminFirstTimeLoading,
    } = useHttpRequest('/programacion_admin');

    const programacionAdmin = ref([]);
    const loadgetProgramacionAdminByPerido = async (id) => {
        const res = await getProgramacionAdminByPerido(id);
        programacionAdmin.value = res;
    };

    return {
        programacionAdmin,
        loadgetProgramacionAdminByPerido,
        ProgramacionAdminLoading,
        ProgramacionAdminFirstTimeLoading,
    };
});

export default useProgramacionAdmintore;
