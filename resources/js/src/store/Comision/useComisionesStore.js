import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useComisionesStore = defineStore('comisiones', () => {
    const {
        index: getComisiones,
        loading: comisionesLoading,
        initialLoading: comisionesFirstTimeLoading,
    } = useHttpRequest('/comisiones');

    const {
        index: getComisionesFilter,
        loading: comisionesFilterLoading,
        initialLoading: comisionesFilterFirstTimeLoading,
    } = useHttpRequest('/comisiones_filter');

    const comisiones = ref([]);
    const users = ref([]);

    const loadComisiones = async () => {
        const res = await getComisiones();
        comisiones.value = res;
    };

    const loadComisionesUserFilter = async () => {
        const res = await getComisionesFilter();
        users.value = res;
    };

    // 🔑 Combinar en uno solo
    const loading = computed(() => comisionesLoading.value || comisionesFilterLoading.value);
    const initialLoading = computed(() => comisionesFirstTimeLoading.value || comisionesFilterFirstTimeLoading.value);

    return {
        comisiones,
        users,
        loadComisiones,
        loadComisionesUserFilter,
        loading,
        initialLoading,
    };
});

export default useComisionesStore;
