import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import useHttpRequest from '../composables/useHttpRequest'

const useUserStore = defineStore('users', () => {

    const {
        index: getUsers,
        show: getUserData,
        loading: usersLoading,
        initialLoading: usersFirstTimeLoading,
    } = useHttpRequest('/users')

    const user = ref(null)
    const userIdTemporal = ref(null)
    const users = ref([])
    const userData = ref(null)
    const requiereCambioPassword = ref(false)

    /* =========================
       NUEVO (CLAVE)
    ========================= */

    const permissions = computed(() => {
        return user.value?.permissions?.map(p => p.name) ?? []
    })

    const roles = computed(() => {
        return user.value?.roles?.map(r => r.name) ?? []
    })

    const hasPermission = (permission) => {
        return permissions.value.includes(permission)
    }

    const hasRole = (role) => {
        return roles.value.includes(role)
    }

    const canVerActividadesRecientes = computed(() =>
        permissions.value.includes('ver-actividades-recientes')
    )

    /* ========================= */

    const setUser = (authUser) => {
        user.value = authUser
    }

    const setRequiereCambioPassword = (valor) => {
        requiereCambioPassword.value = valor
    }

    const setUserIdTemporal = (valor) => {
        userIdTemporal.value = valor
    }

    const loadUsers = async () => {
        users.value = await getUsers()
    }

    const loadUserData = async (idUser) => {
        userData.value = await getUserData(idUser)
    }

    return {
        // state
        user,
        users,
        userData,
        userIdTemporal,
        requiereCambioPassword,

        // permisos
        permissions,
        roles,
        hasPermission,
        hasRole,
        canVerActividadesRecientes,

        // actions
        setUser,
        setRequiereCambioPassword,
        setUserIdTemporal,
        loadUsers,
        loadUserData,

        // loading
        usersLoading,
        usersFirstTimeLoading,
    }
})

export default useUserStore