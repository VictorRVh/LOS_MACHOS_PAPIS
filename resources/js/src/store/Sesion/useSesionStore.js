import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useSesionesStore = defineStore('Sesiones', () => {
    const {
        index: getSesiones,
        show: getSesionesOne,
        loading: sesionesLoading,
        initialLoading: sesionesFirstTimeLoading,
    } = useHttpRequest('/sesion_docente');

    const sesion = ref([]);
    const loadSesion = async (idGrupo) => {
        const res = await getSesionesOne(idGrupo);
        sesion.value = res;
    };

    return {
        sesion,
        loadSesion,
        sesionesLoading,
        sesionesFirstTimeLoading,
    };
});

export default useSesionesStore;
