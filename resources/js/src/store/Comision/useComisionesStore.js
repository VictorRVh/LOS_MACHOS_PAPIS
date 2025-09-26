import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useComisionesStore = defineStore('comisiones', () => {
    const {
        index: getComisiones,
        loading: comisionesLoading,
        initialLoading: comisionesFirstTimeLoading,
    } = useHttpRequest('/comisiones');
    const comisiones = ref([]);
    const loadComisiones = async () => {
        const res = await getComisiones();
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
