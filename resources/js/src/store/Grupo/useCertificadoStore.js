import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useCertificadoStore = defineStore('Certificado_estudio', () => {
    const {
        show: getCertificados,
        loading: certificadosLoading,
        initialLoading: certificadosFirstTimeLoading,
    } = useHttpRequest('/certificado');
    
    const {
        indexWithParams: getCheckCertificados,
        loading: CheckCertificadosLoading,
        initialLoading: CheckCertificadosFirstTimeLoading,
    } = useHttpRequest('/estudianteDocumentoValidar');

    const certificados = ref([]);
    const certificadosCheck = ref([]);

    const loadCertificados = async (idEstudiante) => {
        const res = await getCertificados(idEstudiante);
        certificados.value = res;
    };

    const loadCheckCertificados = async ({id_matricula, tipo_documento}) => {
        const res = await getCheckCertificados({id_matricula, tipo_documento});
        return res;
    };

    return {
        certificados,
        loadCertificados,
        certificadosLoading,
        certificadosFirstTimeLoading,

        loadCheckCertificados
    };
});

export default useCertificadoStore;
