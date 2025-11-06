import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useAsistenciaStore = defineStore('Asistencia', () => {
    const {
        //index: getAsistencia,
        show : getAsistencia,
        loading: asistenciaLoading,
        initialLoading: AsistenciaFirstTimeLoading,
    } = useHttpRequest('/asistencia');
    
    const {
        //index: getAsistencia,
        show : getSesionesEntrega,
        // loading: asistenciaLoading,
        // initialLoading: asistenciaFirstTimeLoading,
    } = useHttpRequest('/sesiones_entrega');

    const sesionesPorEntrega = ref([]);

    const loadSesionesEntrega = async (idEntrega) => {
        const res = await getSesionesEntrega(idEntrega);
        sesionesPorEntrega.value = res;
    };

    return {
        loadSesionesEntrega,
        sesionesPorEntrega,
    };
});

export default useAsistenciaStore;
