import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useGrupoStore = defineStore('grupo', () => {
    // HTTP requests individuales
    const {
        index: getGrupo,
        loading: gruposLoading,
        initialLoading: gruposFirstTimeLoading,
    } = useHttpRequest('/grupo');

    const {
        index: getDocentes,
        loading: docentesLoading,
        initialLoading: docentesFirstTimeLoading,
    } = useHttpRequest('/docente');

    const {
        show: getEspecialidadesPorPrograma,
        loading: especialidadByProgramLoading,
        initialLoading: especialidadByProgramtesFirstTimeLoading,
    } = useHttpRequest('/especialidadByPrograma');

    const {
        show: getModulosPorEspecialidad,
        loading: moduloByEspecialidadLoading,
        initialLoading: moduloByEspecialidadFirstTimeLoading,
    } = useHttpRequest('/moduloByEspecialidad');

    const {
        show: getPeriodoPorModulo,
        loading: docenteperiodoByModuloLoading,
        initialLoading: periodoByModuloFirstTimeLoading,
    } = useHttpRequest('/periodoByModulo');

    // Datos
    const grupos = ref([]);
    const especialidades = ref([]);
    const docentes =ref([]);
    const modulos = ref([]);
    const periodo = ref([]);

    // Métodos
    const loadGrupos = async () => {
        const response = await getGrupo();
        grupos.value = response;
    };

    const loadDocentes = async () => {
        const response = await getDocentes();
        docentes.value = response;
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
        // Datos
        grupos,
        docentes,
        especialidades,
        modulos,
        periodo,

        // Métodos
        loadGrupos,
        loadDocentes,
        loadEspecialidades,
        loadModulos,
        loadPeriodo,

        // Estados de carga individuales
        gruposLoading,
        gruposFirstTimeLoading,

        docentesLoading,
        docentesFirstTimeLoading,

        especialidadByProgramLoading,
        especialidadByProgramtesFirstTimeLoading,

        moduloByEspecialidadLoading,
        moduloByEspecialidadFirstTimeLoading,

        docenteperiodoByModuloLoading,
        periodoByModuloFirstTimeLoading,
    };
});

export default useGrupoStore;
