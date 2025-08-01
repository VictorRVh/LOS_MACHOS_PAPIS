<script setup>
import { inject, ref } from 'vue';
import userMenu from './UserMenu.vue';
import useHttpRequest from '../../composables/useHttpRequest';
import useUserStore from '../../store/useUserStore';
import useRoleStore from '../../store/useRoleStore';
import usePermissionStore from '../../store/usePermissionStore';
import useAppRouter from '../../composables/useAppRouter';
import Breadcrumbs from '../../components/breadcrumbs/Breadcrumbs.vue';
import { ArrowLeftIcon, UserCircleIcon, GlobeEuropeAfricaIcon } from '@heroicons/vue/24/outline';

const { isDarkMode, updateDarkMode } = inject('theme');
const { index: logout } = useHttpRequest('/logout');
const { pushToRoute } = useAppRouter();
const userStore = useUserStore();
const roleStore = useRoleStore();
const permissionStore = usePermissionStore();

const isMenuOpen = ref(false);

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

let RolUser = userStore?.user?.roles?.[0]?.name?.toUpperCase() || 'USUARIO';

const onLogout = async () => {
    const isLoggedOut = await logout();
    if (isLoggedOut) {
        userStore.setUser(null);
        userStore.users = [];
        roleStore.roles = [];
        permissionStore.permissions = [];
        await pushToRoute({ name: 'login' });
    }
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 border-b-2 border-cetpro">
        <header class="h-16 flex items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-4">
                <button @click="$router.back()" class="text-gray-500 dark:text-gray-400 hover:text-cetpro dark:hover:text-cetpro-light">
                    <ArrowLeftIcon class="h-6 w-6" />
                </button>
            </div>

            <div class="flex items-center gap-4">
                <span class="bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full hidden sm:block">
                    {{ RolUser }}
                </span>

                <div class="flex items-center gap-3 pl-4 border-l-2 border-gray-200 dark:border-gray-700">
                    <span v-if="userStore.user" class="text-sm font-medium text-gray-700 dark:text-gray-200 hidden md:block">
                        {{ userStore.user.name }}
                    </span>
                    
                    <div class="relative">
                        <button @click="toggleMenu" class="block h-10 w-10 overflow-hidden rounded-full focus:outline-none focus:ring-2 focus:ring-cetpro focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            <img v-if="userStore.user?.avatar_url" :src="userStore.user.avatar_url" alt="Avatar de usuario" class="h-full w-full object-cover">
                            <UserCircleIcon v-else class="h-10 w-10 text-gray-500" />
                        </button>

                        <Transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <userMenu
                                v-if="isMenuOpen"
                                class="absolute right-0 mt-2 z-50"
                                :nombre="userStore?.user?.name"
                                :apellido="userStore?.user?.apellido"
                                :email="userStore?.user?.email"
                                :is-dark-mode="isDarkMode"
                                @logout="onLogout"
                                @toggle-theme="updateDarkMode(!isDarkMode)"
                                @close-menu="isMenuOpen = false"
                            />
                        </Transition>
                    </div>
                </div>

                <button class="relative text-gray-500 dark:text-gray-400 hover:text-cetpro dark:hover:text-cetpro-light">
                    <GlobeEuropeAfricaIcon class="h-7 w-7" />
                    <span
                        class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-xs text-white ring-2 ring-white dark:ring-gray-800">
                        5
                    </span>
                </button>
            </div>
        </header>

        <div class="h-10 flex items-center px-4 sm:px-6 border-t border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <Breadcrumbs />
            </div>
        </div>
    </div>
</template>