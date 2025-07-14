import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useDocenteStore = defineStore('docentes', () => {
    const {
        index: getDocentes,
        loading: docentesLoading,
        initialLoading: docentesFirstTimeLoading,
    } = useHttpRequest('/docente');

    const docente = ref(null);
    const docentes = ref([]);
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

    return {
        docente,
        setDocente,
        requiereCambioPassword,
        setRequiereCambioPassword,
        docentes,
        docentesLoading,
        docentesFirstTimeLoading,
        loadDocentes,
    };
});

export default useDocenteStore;
