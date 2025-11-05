import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useCapacidadTerminalStore = defineStore('CapacidadTerminalNotasEstudiante', () => {
    const {
        //index: getCapacidadTerminal,
        show : getCapacidadTerminal,
        loading: capacidadTerminalLoading,
        initialLoading: capacidadTerminalFirstTimeLoading,
    } = useHttpRequest('/nota_capacidad_terminal_restringido');

    const capacidadTerminal = ref([]);
    const loadCapacidadTerminal = async (idGrupo) => {
        const res = await getCapacidadTerminal(idGrupo);
        capacidadTerminal.value = res;
    };

    return {
        capacidadTerminal,
        loadCapacidadTerminal,
        capacidadTerminalLoading,
        capacidadTerminalFirstTimeLoading,
    };
});

export default useCapacidadTerminalStore;
