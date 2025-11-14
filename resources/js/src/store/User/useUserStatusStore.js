import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useUserStore = defineStore('user_status', () => {
    const {
        index: getUser,
        loading: usersLoading,
        initialLoading: usersFirstTimeLoading,
    } = useHttpRequest('/users_active');

    const users = ref([])

    const loadUsers = async () => {
        const res = await getUser();
        users.value = res;
    };

    return {
        users,
        loadUsers,
        usersLoading,
        usersFirstTimeLoading,
    };
});

export default useUserStore;
