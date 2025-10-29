import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useSesionesStore = defineStore('Sesiones_docente', () => {
    const {
        index: getSesioneses,
        show: getSesionesesGrupo,
        loading: sesionesLoading,
        initialLoading: sesionesFirstTimeLoading,
    } = useHttpRequest('/programacion_sesion_docente');

    const sesiones = ref([]);
    const loadSesiones = async (idGrupo) => {
        const res = await getSesionesesGrupo(idGrupo);
        sesiones.value = res;
    };

    return {
        sesiones,
        loadSesiones,
        sesionesLoading,
        sesionesFirstTimeLoading,
    };
});

export default useSesionesStore;
