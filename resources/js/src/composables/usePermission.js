import { computed } from 'vue'
import useUserStore from '../store/useUserStore'

export default function usePermission() {
    const userStore = useUserStore()

    const user = computed(() => userStore.user)

    const can = (permission) => {
        return user.value?.permissions?.some(
            p => p.name === permission
        ) ?? false
    }

    const hasRole = (role) => {
        return user.value?.roles?.some(
            r => r.name === role
        ) ?? false
    }

    return {
        user,
        can,
        hasRole,
    }
}