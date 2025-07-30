import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useDocenteStore = defineStore('docentes', () => {
    const {
        index: getDocentes,
        loading: docentesLoading,
        initialLoading: docentesFirstTimeLoading,
    } = useHttpRequest('/docente');

    const {
        index: getDocentesGrupo,
        loading: docentesGrupoLoading,
    } = useHttpRequest('/docenteGrupo');

    const docente = ref(null);
    const docentes = ref([]);
    const docentesGrupo = ref([]);
    const requiereCambioPassword = ref(false);

    const setDocente = (authDocente) => {
        docente.value = authDocente;
    };

    const setRequiereCambioPassword = (valor) => {
        requiereCambioPassword.value = valor;
    };

    const loadDocentes = async () => {
        const response = await getDocentes();
        docentes.value = response;
    };

    const loadDocentesGrupo = async () => {
        const response = await getDocentesGrupo();
        docentesGrupo.value = response;
    }

    return {
        docente,
        setDocente,
        requiereCambioPassword,
        setRequiereCambioPassword,
        docentes,
        docentesLoading,
        docentesFirstTimeLoading,
        loadDocentes,
        loadDocentesGrupo,
        docentesGrupo
    };
});

export default useDocenteStore;
