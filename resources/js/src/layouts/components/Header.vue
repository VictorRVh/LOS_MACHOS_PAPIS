<script setup>
import { inject, ref, computed, watch, onUnmounted, onMounted } from 'vue';
import userMenu from './UserMenu.vue';
import useAppRouter from '../../composables/useAppRouter';
import useUserStore from '../../store/useUserStore';
import useHttpRequest from '../../composables/useHttpRequest';
import Breadcrumbs from '../../components/breadcrumbs/Breadcrumbs.vue';
import Notificacion from '../../pages/Notificacion.vue';
import { useLayoutStore } from '@/store/useLayoutStore';
import {
    ArrowLeftIcon,
    BellIcon,
    SunIcon,
    MoonIcon,
    UserPlusIcon,
    KeyIcon,
    AcademicCapIcon,
    Bars3Icon
} from '@heroicons/vue/24/outline';
import useNotificacionesStore from '../../store/Notificaciones/UseNotificacionesStore';
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';


import { useRouter } from 'vue-router';
const router = useRouter();

const handleGoBack = async () => {
    const items = breadcrumb.itemsText;
    if (!items.length) return;

    // Tomamos el penúltimo item para retroceder
    const lastIndex = items.length - 2;
    if (lastIndex < 0) return; // nada que retroceder

    const lastItem = items[lastIndex];
    if (lastItem?.to) {
        await router.push(lastItem.to); // navegamos
    }

    // Recortamos el breadcrumb actual
    breadcrumb.goBack(lastIndex);
};



const breadcrumb = useBreadcrumbStore();

const { isDarkMode, updateDarkMode } = inject('theme');
const { pushToRoute } = useAppRouter();
const userStore = useUserStore();
const layoutStore = useLayoutStore();
const { index: logout } = useHttpRequest('/logout');

const notificacionesStore = useNotificacionesStore();

const isUserMenuOpen = ref(false);
const isCreateMenuOpen = ref(false);
const isNotificationsOpen = ref(false);
const userMenuContainer = ref(null);
const createMenuContainer = ref(null);
const notificationsContainer = ref(null);

onMounted(async () => {
    await notificacionesStore.loadNotificaciones();
    // await notificacionesStore.loadNotificacionesPendientes();
});

const RolUser = computed(() => userStore.user?.roles?.[0]?.name?.toUpperCase() || 'USUARIO');
const userInitial = computed(() => userStore.user?.name?.[0]?.toUpperCase() || '?');
const userFullName = computed(() => {
    if (!userStore.user) return 'Usuario';
    const { name, apellido_paterno, apellido_materno } = userStore.user;
    return [name, apellido_paterno, apellido_materno].filter(Boolean).join(' ');
});

const quickCreateActions = [
    { label: 'Nuevo Usuario', icon: UserPlusIcon, route: { name: 'user.create' } },
    { label: 'Nuevo Rol', icon: KeyIcon, route: { name: 'role.create' } },
    { label: 'Nuevo Programa', icon: AcademicCapIcon, route: { name: 'program.create' } },
];

const externalLinks = ref([
    {
        id: 'website',
        tooltip: 'Página Web Oficial',
        href: 'https://cetpropuno.edu.pe/',
        iconSrc: '/img/navegador.png',
        show: true
    },
    {
        id: 'facebook',
        tooltip: 'Visítanos en Facebook',
        href: '#',
        iconSrc: '/img/facebook.png',
        show: true
    }
]);

const handleClickOutside = (event) => {
    if (userMenuContainer.value && !userMenuContainer.value.contains(event.target)) {
        isUserMenuOpen.value = false;
    }
    if (createMenuContainer.value && !createMenuContainer.value.contains(event.target)) {
        isCreateMenuOpen.value = false;
    }
    if (notificationsContainer.value && !notificationsContainer.value.contains(event.target)) {
        isNotificationsOpen.value = false;
    }
};

watch([isUserMenuOpen, isCreateMenuOpen, isNotificationsOpen], ([userOpen, createOpen, notifOpen]) => {
    if (userOpen || createOpen || notifOpen) {
        document.addEventListener('mousedown', handleClickOutside);
    } else {
        document.removeEventListener('mousedown', handleClickOutside);
    }
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});

const navigateToAction = (action) => {
    pushToRoute(action.route);
    isCreateMenuOpen.value = false;
};

const onLogout = async () => {
    const isLoggedOut = await logout();
    if (isLoggedOut) {
        userStore.setUser(null);
        await pushToRoute({ name: 'login' });
    }
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 border-b-2 border-cetpro transition-colors duration-300">
        <div class="flex h-16 items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-4">
                <button @click.prevent="layoutStore.toggleSidebarMobile"
                    class="p-2 text-gray-500 rounded-full lg:hidden hover:bg-gray-100 dark:hover:bg-gray-700">
                    <Bars3Icon class="w-6 h-6" />
                </button>
                <h1 class="text-xl font-bold text-cetpro dark:text-gray-100 tracking-wide">
                    CETPRO - Puno
                </h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full hidden lg:block">
                    {{ RolUser }}
                </span>
                <div class="hidden lg:flex items-center gap-1">
                    <button @click="updateDarkMode(!isDarkMode)"
                        class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <SunIcon v-if="isDarkMode" class="h-6 w-6" />
                        <MoonIcon v-else class="h-6 w-6" />
                    </button>

                    <div ref="notificationsContainer" class="relative">
                        <button @click="isNotificationsOpen = !isNotificationsOpen"
                            class="relative p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <BellIcon class="h-6 w-6" />
                            <div v-if="notificacionesStore.notificacionesPendientes">
                                <span class=" absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                                    <span
                                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                                </span>
                            </div>
                        </button>
                        <Notificacion :show="isNotificationsOpen" @close="isNotificationsOpen = false" />
                    </div>

                    <div class="w-px h-6 bg-gray-200 dark:bg-gray-700 mx-2"></div>
                </div>
                <div ref="userMenuContainer" class="relative">
                    <button @click="isUserMenuOpen = !isUserMenuOpen"
                        class="flex items-center gap-3 rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <span class="hidden lg:block text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ userFullName }}
                        </span>
                        <div class="block h-10 w-10 shrink-0 overflow-hidden rounded-full">
                            <img v-if="userStore.user?.avatar_url" :src="userStore.user.avatar_url" alt="Avatar"
                                class="h-full w-full object-cover">
                            <div v-else
                                class="h-full w-full rounded-full bg-cetpro flex items-center justify-center text-white font-bold text-xl">
                                <span>{{ userInitial }}</span>
                            </div>
                        </div>
                    </button>
                    <Transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <userMenu v-if="isUserMenuOpen" class="absolute right-0 mt-2 z-50"
                            :nombre="userStore.user?.name" :apellido="userStore.user?.apellido_paterno"
                            :email="userStore.user?.email" :is-dark-mode="isDarkMode" @logout="onLogout"
                            @toggle-theme="updateDarkMode(!isDarkMode)" @close-menu="isUserMenuOpen = false" />
                    </Transition>
                </div>
            </div>
        </div>

        <div
            class="flex h-14 items-center justify-between gap-2 px-2 sm:px-2 border-t border-gray-200 dark:border-gray-700">
            <div class="flex min-w-0 items-center gap-3">
                <button @click="handleGoBack"
                    class="flex items-center gap-1 p-1 px-4 rounded-md bg-cetpro hover:bg-cetpro-light dark:bg-cetpro-dark dark:hover:bg-cetpro-light text-white transition-colors">
                    <ArrowLeftIcon class="h-6 w-6 shrink-0" />
                    <span class="text-sm font-medium">Atrás</span>
                </button>


                <div class="min-w-0 truncate">
                    <Breadcrumbs />
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div ref="createMenuContainer" class="relative group">
                    <button @click="isCreateMenuOpen = !isCreateMenuOpen"
                        class="flex items-center p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors">
                        <img src="/img/mas.png" class="h-6 w-6" alt="Crear Nuevo" />
                    </button>
                    <div
                        class="absolute bottom-full mb-2 hidden group-hover:block w-max bg-gray-800 text-white text-xs rounded py-1 px-2">
                        Crear Nuevo
                    </div>
                    <Transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <div v-if="isCreateMenuOpen"
                            class="absolute right-0 top-full mt-2 w-56 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a v-for="action in quickCreateActions" :key="action.label"
                                    @click="navigateToAction(action)"
                                    class="cursor-pointer flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <component :is="action.icon" class="h-5 w-5" />
                                    <span>{{ action.label }}</span>
                                </a>
                            </div>
                        </div>
                    </Transition>
                </div>
                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600"></div>
                <template v-for="link in externalLinks" :key="link.id">
                    <a v-if="link.show" :href="link.href" target="_blank" rel="noopener noreferrer"
                        class="relative group p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors">
                        <img :src="link.iconSrc" class="h-6 w-6" :alt="link.tooltip" />
                        <div
                            class="absolute bottom-full mb-2 hidden group-hover:block w-max bg-gray-800 text-white text-xs rounded py-1 px-2">
                            {{ link.tooltip }}
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </div>
</template>