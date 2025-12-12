import { defineStore } from 'pinia';
import axios from 'axios';
import useHttpRequest from '../../composables/useHttpRequest';
import { ref } from 'vue';

const useEstudianteStore = defineStore('estudiante', () => {

    const {
        // index: getEstudiante,
        indexWithParams: getEstudiantesEgresados,
        loading: estudianteLoading,
        initialLoading: estudianteFirstTimeLoading,
    } = useHttpRequest('/estudiantesEgresados');

    const estudiantesEgresados = ref([]);

    const buscarPorDni = async (dni) => {
        try {
            const response = await axios.post('/api/buscar-dni', { dni });
            if (response.data && response.data.success) {
                return response.data;
            }
            return { success: false, message: response.data.message || 'DNI no encontrado' };
        } catch (error) {
            console.error("Error al consultar DNI:", error);
            return { success: false, message: 'Error en la consulta de DNI.' };
        }
    };

    const loadEstudiantesEgresados = async (idEspecialidad, idPeriodo) => {

        const res = await getEstudiantesEgresados({ especialidad: idEspecialidad, periodo: idPeriodo });
        estudiantesEgresados.value = res;
    };

    return {
        buscarPorDni,
        loadEstudiantesEgresados,
        estudiantesEgresados
    };
});

export default useEstudianteStore;