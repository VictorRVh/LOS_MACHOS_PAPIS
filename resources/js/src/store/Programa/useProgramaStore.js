import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';
import useCicloStore from '../Ciclo/useCicloStore';

const useProgramaStore = defineStore('programa_estudio', () => {
    const {
        index: getPrograma,
        show: getProgramaById,
        store: createProgramaOnApi,
        update: updateProgramaOnApi, // <-- AÑADIDO
        destroy: deleteProgramaOnApi,
        loading: programaLoading,
        initialLoading: programaFirstTimeLoading,
    } = useHttpRequest('/programa_estudio');

    const programa = ref({ programas: [] });

    const loadPrograma = async () => {
        if (programa.value?.programas?.length > 0) {
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
            if (programa.value?.programas) {
                programa.value.programas = programa.value.programas.filter(p => p.id !== id);
            }
        } catch (error) {
            console.error(`Error al eliminar el programa con ID ${id}:`, error);
            throw error;
        }
    }

    async function addPrograma(nuevoProgramaData) {
        try {
            const cicloStore = useCicloStore();
            const programaCreado = await createProgramaOnApi(nuevoProgramaData);

            if (programaCreado) {
                const cicloCompleto = cicloStore.ciclo.find(c => c.id === programaCreado.id_ciclo);
                const nombreDelCiclo = cicloCompleto ? cicloCompleto.nombre_ciclo : '';
                
                programaCreado.nameCiclo = `${nombreDelCiclo} - ${programaCreado.año}`;
                
                if (programa.value?.programas) {
                    programa.value.programas.push(programaCreado);
                }
            }
            
            return programaCreado;
        } catch (error) {
            console.error('Error al crear el programa:', error);
            throw error;
        }
    }
    
    // --- NUEVA FUNCIÓN PARA ACTUALIZAR ---
    async function updatePrograma(id, programaDataToUpdate) {
        try {
            const cicloStore = useCicloStore();
            const programaActualizado = await updateProgramaOnApi(id, programaDataToUpdate);
            
            if (programaActualizado) {
                const cicloCompleto = cicloStore.ciclo.find(c => c.id === programaActualizado.id_ciclo);
                const nombreDelCiclo = cicloCompleto ? cicloCompleto.nombre_ciclo : '';
                
                programaActualizado.nameCiclo = `${nombreDelCiclo} - ${programaActualizado.año}`;
                
                const index = programa.value.programas.findIndex(p => p.id === id);
                if (index !== -1) {
                    programa.value.programas[index] = programaActualizado;
                }
            }

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
        updatePrograma, // <-- AÑADIDO
        programaLoading,
        programaFirstTimeLoading,
    };
});

export default useProgramaStore;