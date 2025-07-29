import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useGrupoStore = defineStore('grupo', () => {
    const {
        index: getGrupos,
        loading: docentesLoading,
        initialLoading: docentesFirstTimeLoading,
    } = useHttpRequest('/docente');

    const {
        show: getEspecialidadesPorPrograma,
    } = useHttpRequest('/especialidadByPrograma');

    const {
        show: getModulosPorEspecialidad,
    } = useHttpRequest('/moduloByEspecialidad');

    const {
        show: getPeriodoPorModulo,
    } = useHttpRequest('/periodoByModulo');

    // const docente = ref(null);
    const grupos = ref([]);

    const especialidades = ref([]);
    const modulos = ref([]);
    const periodo = ref([]);

    const setDocente = (authDocente) => {
        docente.value = authDocente;
    };

    const loadGrupos = async () => {
        const response = await getGrupos();
        grupos.value = response;
    };

    const loadEspecialidades = async (programaId) => {
        const response = await getEspecialidadesPorPrograma(programaId);
        especialidades.value = response;
    };

    const loadModulos = async (especialidadId) => {
        const response = await getModulosPorEspecialidad(especialidadId);
        modulos.value = response;
    };

    const loadPeriodo = async (moduloId) => {
        const response = await getPeriodoPorModulo(moduloId);
        periodo.value = response;
    };

    return {
        especialidades,
        modulos,
        periodo,
        loadEspecialidades,
        loadModulos,
        loadGrupos,
        loadPeriodo,
        grupos
    };
});

export default useGrupoStore;
