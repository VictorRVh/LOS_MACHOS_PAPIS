import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEstudianteStore = defineStore('estudianteGrupoNotas', () => {
    const {
        // index: getEstudiante,
        show: getEstudianteGrupoNotas,
        loading: estudianteLoading,
        initialLoading: estudianteFirstTimeLoading,
    } = useHttpRequest('/nota_capacidad_terminal');

    const estudiantes = ref([]);
    const loadEstudiantes = async (idGrupo) => {
        const res = await getEstudianteGrupoNotas(idGrupo);
        estudiantes.value = res;
    };

    return {
        estudiantes,
        loadEstudiantes,
        estudianteLoading,
        estudianteFirstTimeLoading,
    };
});

export default useEstudianteStore;
