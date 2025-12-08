import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useDocenteStore = defineStore('docentes', () => {
    const {
        index: getDocentes,
        loading: docentesLoading,
        show: getDocenteData,
        initialLoading: docentesFirstTimeLoading,
    } = useHttpRequest('/docente');

    const {
        index: getDocentesGrupo,
        loading: docentesGrupoLoading,
    } = useHttpRequest('/docenteGrupo');

    const {
        index: getModulosAsignados,
        // show: getModulosAsignados,
    } = useHttpRequest('/modulosAsignados');

    const {
        indexWithParams: getDocentesDisponibles,
        // show: getModulosAsignados,
        loading: docentesDisponiblesLoading,
    } = useHttpRequest('/docenteGrupo');

    const docente = ref(null);
    const docentes = ref([]);
    const docentesGrupo = ref([]);
    const requiereCambioPassword = ref(false);

    const docentesDisponibles = ref([]);

    const modulosAsignados = ref([]);
    const docenteData = ref(null);

    const setDocente = (authDocente) => {
        docente.value = authDocente;
    };

    const setRequiereCambioPassword = (valor) => {
        requiereCambioPassword.value = valor;
    };

    const getDatosDocente = async (idDocente) => {
        const response = await getDocenteData(idDocente);
        docenteData.value = response;
    };


    const loadDocentes = async () => {
        const response = await getDocentes();
        docentes.value = response;
    };

    const loadDocentesGrupo = async () => {
        const response = await getDocentesGrupo();
        docentesGrupo.value = response;
    }

    const loadModulosAsignados = async () => {
        const response = await getModulosAsignados();
        modulosAsignados.value = response;
    }

    const loadDocentesDisponibles = async ({ turno, id_periodo, id_modulo, id_grupo }) => {

        const response = await getDocentesDisponibles({
            turno,
            id_periodo,
            id_modulo,
            id_grupo
        });

        docentesDisponibles.value = response;
    };

    return {
        docente,
        setDocente,
        docenteData,
        requiereCambioPassword,
        setRequiereCambioPassword,
        docentes,
        getDatosDocente,
        docentesLoading,
        docentesFirstTimeLoading,
        loadDocentes,
        loadDocentesGrupo,
        docentesGrupo,

        loadDocentesDisponibles,
        docentesDisponibles,
        docentesDisponiblesLoading,

        loadModulosAsignados,
        modulosAsignados
    };
});

export default useDocenteStore;
