import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useCertificadoStore = defineStore('Certificado_estudio', () => {
    const {
        show: getCertificados,
        loading: certificadosLoading,
        initialLoading: certificadosFirstTimeLoading,
    } = useHttpRequest('/certificado');

    const certificados = ref([]);

    const loadCertificados = async (idEstudiante) => {
            const res = await getCertificados(idEstudiante);
            certificados.value = res;
    };

    return {
        certificados,
        loadCertificados,
        certificadosLoading,
        certificadosFirstTimeLoading,
    };
});

export default useCertificadoStore;
