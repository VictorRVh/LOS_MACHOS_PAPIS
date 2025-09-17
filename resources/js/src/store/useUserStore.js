import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../composables/useHttpRequest';

const useUserStore = defineStore('users', () => {
    const {
        index: getUsers,
        loading: usersLoading,
        initialLoading: usersFirstTimeLoading,
    } = useHttpRequest('/users');

    const user = ref(null);
    const userIdTemporal = ref(null);
    const users = ref([]);
    const requiereCambioPassword = ref(false);

    const setUser = (authUser) => {
        user.value = authUser;
    };

    const setRequiereCambioPassword = (valor) => {
        requiereCambioPassword.value = valor;
    };
    const setUserIdTemporal = (valor) =>{
        userIdTemporal.value =valor;
    }

    const loadUsers = async () => {
        const response = await getUsers();
        users.value = response;
    };

    return {
        user,
        setUser,
        requiereCambioPassword,
        userIdTemporal,
        setRequiereCambioPassword,
        setUserIdTemporal,
        users,
        usersLoading,
        usersFirstTimeLoading,
        loadUsers,
    };
});

export default useUserStore;
