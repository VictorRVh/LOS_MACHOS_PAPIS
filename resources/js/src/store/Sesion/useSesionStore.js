import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useSesionesStore = defineStore('Sesiones', () => {
    const {
        indexWithParams: getSesiones,
        show: getSesionesOne,
        loading: sesionesLoading,
        initialLoading: sesionesFirstTimeLoading,
    } = useHttpRequest('/sesion_docente');

    const sesion = ref([]);

    const loadSesion = async ({ id_grupo, tipo_entrega }) => {
        const res = await getSesiones({
            id_grupo,
            tipo_entrega
        });
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
