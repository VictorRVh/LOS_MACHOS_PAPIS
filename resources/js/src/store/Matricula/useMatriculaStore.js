import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

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

    const grupos = ref([]);
    const matriculasEnGrupo = ref([]);
    const estudiantesConReserva = ref([]);
    const programasConEspecialidades = ref([]);
    const gruposDisponiblesParaMatricula = ref([]);
    const estudiantesMatriculados = ref([]);
    const datosMatricula = ref([]);

    const fetchGruposConMatriculados = async () => {
        grupos.value = await get('/api/grupos-con-matriculados');
    };

    const fetchMatriculasPorGrupo = async (grupoId) => {
        matriculasEnGrupo.value = [];
        const response = await get(`/api/grupos/${grupoId}/matriculas`);
        if (response) {
            matriculasEnGrupo.value = response;
        }
    };

    const fetchEstudiantesConReserva = async () => {
        estudiantesConReserva.value = [];
        const response = await get('/api/reservas');
        if (response) estudiantesConReserva.value = response;
    };

    const fetchProgramasPorCiclo = async (cicloId) => {
        programasConEspecialidades.value = [];
        const response = await get(`/api/ciclo/${cicloId}/programas`);
        if (response) programasConEspecialidades.value = response;
    };

    const fetchGruposPorEspecialidad = async (especialidadId) => {
        gruposDisponiblesParaMatricula.value = [];
        const response = await get(`/api/especialidad/${especialidadId}/grupos`);
        if (response) gruposDisponiblesParaMatricula.value = response;
    };

    const fetchEstudiantesPorGrupo = async (grupoId) => {
        const response = await getEstudiantesPorGrupo(grupoId)
        estudiantesMatriculados.value = response;
    };

    const fetchFichaMatricula = async (estudianteId) => {
        const response = await getFichaMatricula(estudianteId)
        datosMatricula.value = response;
    };

    return {
        grupos,
        matriculasEnGrupo,
        estudiantesConReserva,
        programasConEspecialidades,
        gruposDisponiblesParaMatricula,
        // loading,
        fetchGruposConMatriculados,
        fetchMatriculasPorGrupo,
        fetchEstudiantesConReserva,
        fetchProgramasPorCiclo,
        fetchGruposPorEspecialidad,

        fetchEstudiantesPorGrupo,
        estudiantesMatriculados,

        fetchFichaMatricula,
        datosMatricula
    };
});

export default useMatriculaStore;