import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useCapacidadTerminalStore = defineStore('CapacidadTerminal', () => {
    const {
        //index: getCapacidadTerminal,
        show: getCapacidadTerminal,
        loading: capacidadTerminalLoading,
        initialLoading: capacidadTerminalFirstTimeLoading,
    } = useHttpRequest('/capacidad_terminal');

    const {
        //index: getCapacidadTerminal,
        show: getNroCapacidades,
        // loading: capacidadTerminalLoading,
        // initialLoading: capacidadTerminalFirstTimeLoading,
    } = useHttpRequest('/nro_capacidades');

    const {
        //index: getCapacidadTerminal,
        show: getCapacidadTerminalInfo,
        loading: capacidadTerminalInfoLoading,
        initialLoading: capacidadTerminalInfoFirstTimeLoading,
    } = useHttpRequest('/nota_capacidad_terminal_info');

    const capacidadTerminal = ref([]);
    const nroCapacidades = ref([]);
    const estadoCapacidad = ref(null);

    const loadCapacidadTerminal = async (idGrupo) => {
        const res = await getCapacidadTerminal(idGrupo);
        capacidadTerminal.value = res;
    };

    const loadNroCapacidades = async (idGrupo) => {
        const res = await getNroCapacidades(idGrupo);
        nroCapacidades.value = res;
    };

    const verificarEstadoCapacidad = async (id) => {
        const res = await getCapacidadTerminalInfo(id);
        console.log('respuesta de notas', res);
        estadoCapacidad.value = res.data;
        return res.data;
    };


    const puedeSubirNotas = () => {
        return estadoCapacidad.value?.puede_subir_notas ?? false;
    };

    const getMensajeEstado = () => {
        return estadoCapacidad.value?.mensaje ?? '';
    };

    const getFechaLimite = () => {
        return estadoCapacidad.value?.fecha_limite_subida ?? null;
    };

    return {
        capacidadTerminal,
        loadCapacidadTerminal,
        capacidadTerminalLoading,
        capacidadTerminalFirstTimeLoading,

        loadNroCapacidades,
        nroCapacidades,

        verificarEstadoCapacidad,
        estadoCapacidad,
        puedeSubirNotas,
        getMensajeEstado,
        getFechaLimite,
        capacidadTerminalInfoLoading
    };
});

export default useCapacidadTerminalStore;
