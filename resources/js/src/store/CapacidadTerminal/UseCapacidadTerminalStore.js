import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useCapacidadTerminalStore = defineStore('CapacidadTerminal', () => {
    const {
        //index: getCapacidadTerminal,
        show : getCapacidadTerminal,
        loading: capacidadTerminalLoading,
        initialLoading: capacidadTerminalFirstTimeLoading,
    } = useHttpRequest('/capacidad_terminal');

    const {
        //index: getCapacidadTerminal,
        show : getNroCapacidades,
        // loading: capacidadTerminalLoading,
        // initialLoading: capacidadTerminalFirstTimeLoading,
    } = useHttpRequest('/nro_capacidades');

    const capacidadTerminal = ref([]);
    const nroCapacidades = ref([])

    const loadCapacidadTerminal = async (idGrupo) => {
        const res = await getCapacidadTerminal(idGrupo);
        capacidadTerminal.value = res;
    };

    const loadNroCapacidades = async (idGrupo) => {
        const res = await getNroCapacidades(idGrupo);
        nroCapacidades.value = res;
    };

    return {
        capacidadTerminal,
        loadCapacidadTerminal,
        capacidadTerminalLoading,
        capacidadTerminalFirstTimeLoading,

        loadNroCapacidades,
        nroCapacidades
    };
});

export default useCapacidadTerminalStore;
