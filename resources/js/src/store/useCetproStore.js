import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../composables/useHttpRequest';

const useCetproStore = defineStore('cetpro-información', () => {
    const {
        index: getCetpro,
        loading: cetproLoading,
        initialLoading: cetproFirstTimeLoading,
    } = useHttpRequest('/cetprodata');

    const cetpro = ref([]);
    const loadCetpro = async () => {
        const res = await getCetpro();
        cetpro.value = res;
    };

    return {
        cetpro,
        loadCetpro,
        cetproLoading,
        cetproFirstTimeLoading,
    };
});

export default useCetproStore;
