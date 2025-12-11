import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';
import axios from 'axios';

const useMatriculaStore = defineStore('matricula', () => {

    const {
        show: getEstudiantesPorGrupo,
        // loading: especialidadByProgramLoading,
        // initialLoading: especialidadByProgramtesFirstTimeLoading,
    } = useHttpRequest('/matricula');

    const {
        show: getFichaMatricula,
        // loading: especialidadByProgramLoading,
        // initialLoading: especialidadByProgramtesFirstTimeLoading,
    } = useHttpRequest('/fichaMatricula');

    const {
        show: getMatriculadosGrupo,
        // loading: especialidadByProgramLoading,
        // initialLoading: especialidadByProgramtesFirstTimeLoading,
    } = useHttpRequest('/matricula');

    const {
        show: getMatriculadosGrupoExtendido,
        // loading: especialidadByProgramLoading,
        // initialLoading: especialidadByProgramtesFirstTimeLoading,
    } = useHttpRequest('/matriculados');


    const {
        update: getReservaMatricula,
        // loading: especialidadByProgramLoading,
        // initialLoading: especialidadByProgramtesFirstTimeLoading,
    } = useHttpRequest('/reservaMatricula');

    const {
        show: getListaReservaMatricula,
        // loading: especialidadByProgramLoading,
        // initialLoading: especialidadByProgramtesFirstTimeLoading,
    } = useHttpRequest('/listaReserva');

    const grupos = ref([]);
    const matriculasEnGrupo = ref([]);
    const estudiantesConReserva = ref([]);
    const programasConEspecialidades = ref([]);
    const estudiantesMatriculados = ref([]);
    const datosMatricula = ref([]);

    const matriculadosPorGrupo = ref([]);
    const matriculadosPorGrupoExtendido = ref([]);

    const matriculasReservadas = ref([]);

    const fetchEstudiantesPorGrupo = async (grupoId) => {
        const response = await getEstudiantesPorGrupo(grupoId)
        estudiantesMatriculados.value = response;
    };

    const fetchFichaMatricula = async (estudianteId) => {
        const response = await getFichaMatricula(estudianteId)
        datosMatricula.value = response;
    };

    const fetchMatriculadosPorGrupo = async (grupoId) => {
        const response = await getMatriculadosGrupo(grupoId)
        matriculadosPorGrupo.value = response;
    };

    const fetchMatriculadosPorGrupoExtendido = async (grupoId) => {
        const response = await getMatriculadosGrupoExtendido(grupoId)
        matriculadosPorGrupoExtendido.value = response;
    }

    const loadCambioMatricula = async (idsMatriculas, nuevoGrupoId) => {
       // console.log('cambiando matrículas:', idsMatriculas, nuevoGrupoId);
        try {
            // siempre mando array
            const response = await axios.patch('/cambiarMatricula', {
                ids: Array.isArray(idsMatriculas) ? idsMatriculas : [idsMatriculas],
                id_grupo: nuevoGrupoId,
            });

            return response.data;
        } catch (error) {
            console.error('Error al cambiar grupo:', error.response?.data || error);
            throw error;
        }
    };

    const loadReservaMatricula = async (idMatricula) => {
        const response = await getReservaMatricula(idMatricula)
        //console.log("store: ",response)
        return response;
    };


    const loadListaReserva = async ( idTipo = 1) => {
        const response = await getListaReservaMatricula(idTipo);
        matriculasReservadas.value = response
    };

    return {
        grupos,
        matriculasEnGrupo,
        estudiantesConReserva,
        programasConEspecialidades,
        fetchEstudiantesPorGrupo,
        estudiantesMatriculados,

        fetchFichaMatricula,
        datosMatricula,

        fetchMatriculadosPorGrupo,
        matriculadosPorGrupo,

        loadCambioMatricula,

        loadReservaMatricula,

        loadListaReserva,
        matriculasReservadas,

        fetchMatriculadosPorGrupoExtendido,
        matriculadosPorGrupoExtendido
    };
});

export default useMatriculaStore;