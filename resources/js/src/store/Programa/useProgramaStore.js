import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useProgramaStore = defineStore('programa_estudio', () => {
    const {
        index: getPrograma,
        show: getProgramaById,
        loading: programaLoading,
        initialLoading: programaFirstTimeLoading,
    } = useHttpRequest('/programa_estudio');

    const programa = ref([]);

    const loadPrograma = async () => {
        if (programa.value?.programas && programa.value.programas.length > 0) {
            return;
        }
        const res = await getPrograma();
        programa.value = res;
    };

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
        programaLoading,
        programaFirstTimeLoading,
    };
});

export default useProgramaStore;