// store/EspecialidadPrograma/useEspecialidadProgramaStore.js

import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useEspecialidadProgramaStore = defineStore('especialidad_programa', () => {
    const {
        index: getEspecialidadPrograma,
        show: getEspecialidadProgramaById,
        loading: especialidadProgramaLoading,
        initialLoading: especialidadProgramaFirstTimeLoading,
    } = useHttpRequest('/especialidad_programa');

    const especialidadPrograma = ref([]);
    const especialidadProgramaFiltrado = ref(null);

    const loadEspecialidadPrograma = async () => {
        const res = await getEspecialidadPrograma();
        especialidadPrograma.value = res;
    };

    const loadEspecialidadProgramaById = async (id) => {
        const res = await getEspecialidadProgramaById(id);
        especialidadProgramaFiltrado.value = res;
    };

    // --- FUNCIÓN AÑADIDA ---
    async function findEspecialidadProgramaById(id) {
        if (!id) return null;

        const listaEspecialidades = especialidadProgramaFiltrado.value?.especialidad_programas || [];
        const especialidadExistente = listaEspecialidades.find(ep => ep.id == id);
        
        if (especialidadExistente) {
            return especialidadExistente;
        }

        try {
            const res = await getEspecialidadProgramaById(id);
            // La respuesta de la API debe ser el objeto que contiene el id_programa
            // Asumiendo que el endpoint show devuelve el objeto directamente
            return res;
        } catch (error) {
            console.error(`Error al buscar especialidad_programa con ID ${id}:`, error);
            return null;
        }
    }
    // --- FIN DE LA FUNCIÓN AÑADIDA ---


    return {
        especialidadPrograma,
        especialidadProgramaFiltrado,
        loadEspecialidadPrograma,
        loadEspecialidadProgramaById,
        findEspecialidadProgramaById, // Se retorna la nueva función
        especialidadProgramaLoading,
        especialidadProgramaFirstTimeLoading,
    };
});

export default useEspecialidadProgramaStore;