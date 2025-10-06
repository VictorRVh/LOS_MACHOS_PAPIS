import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from '../../utils/axios';

const useDocumentoStore = defineStore('documento', () => {
    const documentos = ref([]);
    const loading = ref(false);

    const loadDocumentos = async (idPeriodo) => {
        if (!idPeriodo) {
            documentos.value = [];
            return;
        }
        loading.value = true;
        try {
            const response = await axios.get('/entregas-admin', {
                params: { id_periodo: idPeriodo }
            });
            documentos.value = response.data;
        } catch (error) {
            console.error('Error al cargar la programación de entregas:', error);
            documentos.value = [];
        } finally {
            loading.value = false;
        }
    };

    return {
        documentos,
        loading,
        loadDocumentos,
    };
});

export default useDocumentoStore;