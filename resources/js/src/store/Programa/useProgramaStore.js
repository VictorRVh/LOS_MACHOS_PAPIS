import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useProgramaStore = defineStore('programa_estudio', () => {
    const {
        index: getPrograma,
        show: getProgramaById,
        store: createProgramaOnApi,
        update: updateProgramaOnApi,
        destroy: deleteProgramaOnApi,
        loading: programaLoading,
        initialLoading: programaFirstTimeLoading,
    } = useHttpRequest('/programa_estudio');

    const programa = ref({ programas: [] });

    const loadPrograma = async (force = false) => {
        if (programa.value?.programas?.length > 0 && !force) {
            return;
        }
        try {
            const res = await getPrograma();
            programa.value = res.programas ? res : { programas: res };
        } catch (error) {
            console.error("Error al cargar programas:", error);
            programa.value = { programas: [] };
        }
    };

    async function removePrograma(id) {
        try {
            await deleteProgramaOnApi(id);
            await loadPrograma(true);
        } catch (error) {
            console.error(`Error al eliminar el programa con ID ${id}:`, error);
            throw error;
        }
    }

    async function addPrograma(nuevoProgramaData) {
        try {
            const programaCreado = await createProgramaOnApi(nuevoProgramaData);
            await loadPrograma(true);
            return programaCreado;
        } catch (error) {
            console.error('Error al crear el programa:', error);
            throw error;
        }
    }
    
    async function updatePrograma(id, programaDataToUpdate) {
        try {
            const programaActualizado = await updateProgramaOnApi(id, programaDataToUpdate);
            await loadPrograma(true);
            return programaActualizado;
        } catch (error) {
            console.error(`Error al actualizar el programa con ID ${id}:`, error);
            throw error;
        }
    }

    async function findProgramaById(id) {
        if (!id) return null;
        if (!programa.value?.programas || programa.value.programas.length === 0) {
            await loadPrograma();
        }
        const listaProgramas = programa.value?.programas || [];
        const programaExistente = listaProgramas.find(p => p.id == id);
        if (programaExistente) {
            return programaExistente;
        }
        try {
            const res = await getProgramaById(id);
            return res.programa;
        } catch (error) {
            console.error(`Error al buscar programa con ID ${id}:`, error);
            return null;
        }
    }

    return {
        programa,
        loadPrograma,
        findProgramaById,
        removePrograma,
        addPrograma,
        updatePrograma,
        programaLoading,
        programaFirstTimeLoading,
    };
});

export default useProgramaStore;