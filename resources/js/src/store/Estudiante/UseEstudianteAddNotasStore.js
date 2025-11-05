import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useAlumnosNotasStore = defineStore('AlumnosNotasEstudianteAdd', () => {
    const {
        //index: getAlumnosNotas,
        show : getAlumnosNotas,
        loading: alumnosNotasLoading,
        initialLoading: alumnosNotasFirstTimeLoading,
    } = useHttpRequest('/lista_alumnos_notas');

    const alumnosNotas = ref([]);
    const loadAlumnosNotas = async (idGrupo) => {
        const res = await getAlumnosNotas(idGrupo);
        alumnosNotas.value = res;
    };

    return {
        alumnosNotas,
        loadAlumnosNotas,
        alumnosNotasLoading,
        alumnosNotasFirstTimeLoading,
    };
});

export default useAlumnosNotasStore;
