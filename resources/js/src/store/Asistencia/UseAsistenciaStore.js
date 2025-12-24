import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useAsistenciaStore = defineStore('Asistencia', () => {
    const {
        //index: getAsistencia,
        show: getAsistenciaAlumnos,
        loading: asistenciaAlumnosLoading,
        // initialLoading: AsistenciaFirstTimeLoading,
    } = useHttpRequest('/asistencia');

    const {
        //index: getAsistencia,
        show: getSesionesEntrega,
        loading: asistenciaLoading,
        initialLoading: asistenciaFirstTimeLoading,
    } = useHttpRequest('/sesiones_asistencia');

    const sesionesPorEntrega = ref([]);
    const asistenciaEstudents = ref([]);

    const loadSesionesEntrega = async (idEntrega) => {
        const res = await getSesionesEntrega(idEntrega);
        sesionesPorEntrega.value = res;
    };
    const loadAsistenciaEstudents = async (idGrupo) => {
        const res = await getAsistenciaAlumnos(idGrupo);
        asistenciaEstudents.value = res;
    };


    return {
        loadSesionesEntrega,
        loadAsistenciaEstudents,
        sesionesPorEntrega,
        asistenciaLoading,
        asistenciaEstudents,
        asistenciaAlumnosLoading,
        asistenciaFirstTimeLoading

    };
});

export default useAsistenciaStore;
