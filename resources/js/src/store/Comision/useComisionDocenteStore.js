import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useComisionesStore = defineStore('comisiones_docente', () => {
    const {
        //index: getComisiones,
        show: getComisionesDocente,
        loading: comisionesLoading,
        initialLoading: comisionesFirstTimeLoading,
    } = useHttpRequest('/index_comision_docente');
    const comisiones = ref([]);
    const loadComisiones = async (idDocente) => {
        const res = await getComisionesDocente(idDocente);
        comisiones.value = res;
    };
    return {
        comisiones,
        loadComisiones,
        comisionesLoading,
        comisionesFirstTimeLoading,
    };
});

export default useComisionesStore;
