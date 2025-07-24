import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEstudianteStore = defineStore('estudiante', () => {
    const {
        index: getEstudiantes,
        loading: estudiantesLoading,
        initialLoading: estudiantesFirstTimeLoading,
    } = useHttpRequest('/estudiante');

    const estudiante = ref(null);
    const estudiantes = ref([]);
    const requiereCambioPassword = ref(false);

    const setEstudiante = (authEstudiante) => {
        estudiante.value = authEstudiante;
    };

    const setRequiereCambioPassword = (valor) => {
        requiereCambioPassword.value = valor;
    };

    const loadEstudiantes = async () => {
        const response = await getEstudiantes();
        estudiantes.value = response;
    };

    return {
        estudiante,
        setEstudiante,
        requiereCambioPassword,
        setRequiereCambioPassword,
        estudiantes,
        estudiantesLoading,
        estudiantesFirstTimeLoading,
        loadEstudiantes,
    };
});

export default useEstudianteStore;
