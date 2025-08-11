import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useMatriculaStore = defineStore('matricula', () => {
    const { get, loading } = useHttpRequest();

    const grupos = ref([]);
    const matriculasEnGrupo = ref([]);
    const estudiantesConReserva = ref([]);
    const programasConEspecialidades = ref([]);
    const gruposDisponiblesParaMatricula = ref([]);

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

    return {
        grupos,
        matriculasEnGrupo,
        estudiantesConReserva,
        programasConEspecialidades,
        gruposDisponiblesParaMatricula,
        loading,
        fetchGruposConMatriculados,
        fetchMatriculasPorGrupo,
        fetchEstudiantesConReserva,
        fetchProgramasPorCiclo,
        fetchGruposPorEspecialidad,
    };
});

export default useMatriculaStore;