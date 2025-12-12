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

    const {
        indexWithParams: getGruposFiltrados,
        loading: gruposFiltradosLoading,
        initialLoading: gruposFiltradosFirstTimeLoading,
    } = useHttpRequest('/gruposFiltrados');

    const {
        show: getAniosByCiclo,
        loading: aniosByCicloLoading,
        initialLoading: aniosByCicloFirstTimeLoading,
    } = useHttpRequest('/aniosByCiclo');

    const {
        show: getPeriodoByAnio,
        loading: periodoByAnioLoading,
        initialLoading: periodoByAnioFirstTimeLoading,
    } = useHttpRequest('/periodoByAnio');

    // LISTA DE GRUPOS POR CICLO

    const {
        show: getPeriodoByCiclo,
        loading: periodoByCicloLoading,
        // initialLoading: moduloByEspecialidadFirstTimeLoading,
    } = useHttpRequest('/periodoByCiclo');

    const {
        indexWithParams: getGruposByCicloPeriodo,
        // loading: moduloByEspecialidadLoading,
        // initialLoading: moduloByEspecialidadFirstTimeLoading,
    } = useHttpRequest('/gruposMatricula');

    const {
        indexWithParams: getGruposDisponibles,
        // loading: moduloByEspecialidadLoading,
        // initialLoading: moduloByEspecialidadFirstTimeLoading,
    } = useHttpRequest('/gruposDisponibles');

    const {
        show: getInfoGrupo,
        // loading: moduloByEspecialidadLoading,
        // initialLoading: moduloByEspecialidadFirstTimeLoading,
    } = useHttpRequest('/infoGrupo');

    const {
        index: getGrupoRecientes,
        loading: gruposRecientesLoading,
        initialLoading: gruposRecientesFirstTimeLoading,
    } = useHttpRequest('/gruposRecientes');
    
    const {
        index: getGrupoCulminados,
        loading: gruposCulminadosLoading,
        initialLoading: gruposCulminadosFirstTimeLoading,
    } = useHttpRequest('/gruposCulminados');

    // Datos
    const grupos = ref([]);
    const especialidades = ref([]);
    const docentes = ref([]);
    const modulos = ref([]);
    const periodo = ref([]);
    const gruposFiltrados = ref([]);

    const anios = ref([])
    const periodoAnio = ref([])

    const periodoCiclo = ref([])
    const gruposCicloPeriodo = ref([])

    const gruposDisponibles = ref([])

    const infoGrupo = ref([])
    const gruposRecientes = ref([])
    const gruposCulminados = ref([])

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

    const loadAnios = async (cicloId) => {
        const response = await getAniosByCiclo(cicloId)
        anios.value = response;
    };

    const loadPeriodoAnio = async (anio) => {
        const response = await getPeriodoByAnio(anio)
        periodoAnio.value = response;
    };

    const loadGruposFiltrados = async ({ id_ciclo, anio, id_periodo }) => {
        try {
            const response = await getGruposFiltrados({
                id_ciclo,
                anio,
                id_periodo,
            });

            console.log('response de grupos filtrados', response)

            gruposFiltrados.value = response;
        } catch (error) {
            console.error('Error al cargar grupos filtrados:', error);
        }
    };

    const loadPeriodoCiclo = async (ciclo) => {
        const response = await getPeriodoByCiclo(ciclo)
        periodoCiclo.value = response;
    };

    const loadGruposCicloPeriodo = async ({ id_ciclo, id_periodo }) => {

        const response = await getGruposByCicloPeriodo({
            id_ciclo,
            id_periodo,
        });

        console.log('responde utilop', response)
        gruposCicloPeriodo.value = response;
    };

    const loadGruposDisponibles = async (periodo, grupo) => {
        try {
            const response = await getGruposDisponibles({ periodo, grupo })
            gruposDisponibles.value = response
        } catch (error) {
            console.error("Error cargando grupos disponibles", error)
            gruposDisponibles.value = []
        }
    }

    const loadInfoGrupo = async (grupo) => {
        const response = await getInfoGrupo(grupo)
        infoGrupo.value = response;
    };

    const loadGruposRecientes = async () => {
        const response = await getGrupoRecientes()
        gruposRecientes.value = response;
    };
    
    const loadGruposCulminados = async () => {
        const response = await getGrupoCulminados()
        gruposCulminados.value = response;
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

        gruposFiltrados,
        loadGruposFiltrados,

        gruposFiltradosLoading,
        gruposFiltradosFirstTimeLoading,

        loadAnios,
        anios,
        aniosByCicloLoading,

        loadPeriodoAnio,
        periodoAnio,
        periodoByAnioLoading,

        loadPeriodoCiclo,
        periodoCiclo,
        periodoByCicloLoading,

        loadGruposCicloPeriodo,
        gruposCicloPeriodo,

        loadGruposDisponibles,
        gruposDisponibles,

        loadInfoGrupo,
        infoGrupo,

        loadGruposRecientes,
        gruposRecientesLoading,
        gruposRecientes,

        loadGruposCulminados,
        gruposCulminadosLoading,
        gruposCulminados

    };
});

export default useGrupoStore;
