import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useUserStore = defineStore('administrativo', () => {
    const {
        index: getUsers,
        loading: usersLoading,
        initialLoading: usersFirstTimeLoading,
    } = useHttpRequest('/personal_administrativo');

    const user = ref(null);
    const users = ref([]);
    const requiereCambioPassword = ref(false);

    const setUser = (authUser) => {
        user.value = authUser;
    };
    
    const setRequiereCambioPassword = (valor) => {
        requiereCambioPassword.value = valor;
    };

    const loadUsers = async () => {
        const response = await getUsers();
        users.value = response;
    };

    return {
        user,
        setUser,
        requiereCambioPassword,
        setRequiereCambioPassword,
        users,
        usersLoading,
        usersFirstTimeLoading,
        loadUsers,
    };
});

export default useUserStore;
