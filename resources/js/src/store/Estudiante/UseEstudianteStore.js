import { defineStore } from 'pinia';
import axios from 'axios';

const useEstudianteStore = defineStore('estudiante', () => {
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

    return {
        buscarPorDni,
    };
});

export default useEstudianteStore;