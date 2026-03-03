import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEgresadoDocumentoStore = defineStore('EgresadoDocumento', () => {

    const {
        indexWithParams: verificarDocumento,
        loading,
        initialLoading,
    } = useHttpRequest('/egresado-documento');

    const egresadoDocumento = ref(null);

    const loadEgresadoDocumento = async (idEgresado, tipoDocumento) => {
        const res = await verificarDocumento({
            id_egresado: idEgresado,
            tipo_documento: tipoDocumento
        });

        egresadoDocumento.value = res;
        return res;
    };

    return {
        egresadoDocumento,
        loadEgresadoDocumento,
        loading,
        initialLoading,
    };
});

export default useEgresadoDocumentoStore;
