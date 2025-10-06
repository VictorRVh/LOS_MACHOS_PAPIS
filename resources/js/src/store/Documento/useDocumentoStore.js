import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from '../../utils/axios';

const useDocumentoStore = defineStore('documento', () => {
    const programaciones = ref([]);
    const loading = ref(false);

    const loadProgramaciones = async (idPeriodo) => {
        if (!idPeriodo) {
            programaciones.value = [];
            return;
        }
        loading.value = true;
        try {
            const response = await axios.get('/entregas-admin', {
                params: { id_periodo: idPeriodo }
            });
            programaciones.value = response.data;
        } catch (error) {
            console.error('Error al cargar las programaciones:', error);
            programaciones.value = [];
        } finally {
            loading.value = false;
        }
    };
    
    const addProgramacion = (programacion) => {
        programaciones.value.unshift(programacion);
    };

    return { programaciones, loading, loadProgramaciones, addProgramacion };
});

export default useDocumentoStore;