<script setup>
import { inject, ref, computed, watch, onMounted, onUnmounted } from 'vue';
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
    Bars3Icon
} from '@heroicons/vue/24/outline';
import useNotificacionesStore from '../../store/Notificaciones/UseNotificacionesStore';
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';
import { useRouter } from 'vue-router';
import usePermission from '../../composables/usePermission';

const router = useRouter();
const { can } = usePermission();

const breadcrumb = useBreadcrumbStore();

const handleGoBack = async () => {
    const items = breadcrumb.itemsText;
    if (!items.length) return;

    const lastIndex = items.length - 2;
    if (lastIndex < 0) return;

    const lastItem = items[lastIndex];
    if (lastItem?.to) {
        await router.push(lastItem.to);
    }

    breadcrumb.goBack(lastIndex);
};

const showBackButton = computed(() => {
    return breadcrumb.itemsText.length > 1;
});

const { isDarkMode, updateDarkMode } = inject('theme');
const { pushToRoute } = useAppRouter();
const userStore = useUserStore();
const layoutStore = useLayoutStore();
const { index: logout } = useHttpRequest('/logout');

const notificacionesStore = useNotificacionesStore();

const isUserMenuOpen = ref(false);
const isNotificationsOpen = ref(false);
const userMenuContainer = ref(null);
const notificationsContainer = ref(null);

let pollInterval = null;

const RolUser = computed(() => userStore.user?.roles?.[0]?.name?.toUpperCase() || 'USUARIO');
const userInitial = computed(() => userStore.user?.name?.[0]?.toUpperCase() || '?');
const userFullName = computed(() => {
    if (!userStore.user) return 'Usuario';
    const { name, apellido_paterno, apellido_materno } = userStore.user;
    return [name, apellido_paterno, apellido_materno].filter(Boolean).join(' ');
});

const handleClickOutside = (event) => {
    if (userMenuContainer.value && !userMenuContainer.value.contains(event.target)) {
        isUserMenuOpen.value = false;
    }
    if (notificationsContainer.value && !notificationsContainer.value.contains(event.target)) {
        isNotificationsOpen.value = false;
    }
};

watch([isUserMenuOpen, isNotificationsOpen], ([userOpen, notifOpen]) => {
    if (userOpen || notifOpen) {
        document.addEventListener('mousedown', handleClickOutside);
    } else {
        document.removeEventListener('mousedown', handleClickOutside);
    }
});

onMounted(() => {
    if (can('ver-actividades-recientes')) {
        notificacionesStore.loadNotificaciones()
    }
})

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});

onMounted(() => {
    if (can('ver-actividades-recientes')) {
        notificacionesStore.loadNotificaciones();
        // En la prueba de sesion corta evitamos refrescos demasiado frecuentes
        pollInterval = setInterval(() => {
            notificacionesStore.loadNotificaciones();
        }, 120_000);
    }
});

onUnmounted(() => {
    clearInterval(pollInterval);
});

const onLogout = async () => {
    const isLoggedOut = await logout();
    if (isLoggedOut) {
        userStore.setUser(null);
        await pushToRoute({ name: 'login' });
    }
};
</script>

<template>
    <header
        class="border-b border-slate-200 bg-white transition-colors duration-300 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex h-[68px] items-center justify-between gap-4 px-4 sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <button @click.prevent="layoutStore.toggleSidebarMobile"
                    class="rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:bg-slate-100 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 lg:hidden">
                    <Bars3Icon class="h-5 w-5" />
                </button>

                <button v-if="showBackButton" @click="handleGoBack"
                    class="inline-flex items-center gap-1 rounded-md bg-cetpro px-3 py-2 text-sm font-medium text-white transition hover:bg-cetpro-light dark:bg-cetpro-dark dark:hover:bg-cetpro-light">
                    <ArrowLeftIcon class="h-4 w-4 shrink-0" />
                    <span>Atrás</span>
                </button>

                <div class="min-w-0 overflow-hidden">
                    <Breadcrumbs />
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span
                    class="hidden rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-orange-600 lg:inline-flex">
                    {{ RolUser }}
                </span>

                <div class="hidden items-center gap-1 lg:flex">
                    <button @click="updateDarkMode(!isDarkMode)"
                        class="rounded-full p-2 text-gray-500 transition-colors hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
                        <SunIcon v-if="isDarkMode" class="h-5 w-5" />
                        <MoonIcon v-else class="h-5 w-5" />
                    </button>

                    <div ref="notificationsContainer" class="relative" v-if="can('ver-actividades-recientes')">
                        <button @click="isNotificationsOpen = !isNotificationsOpen"
                            class="relative rounded-full p-2 text-gray-500 transition-colors hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
                            <BellIcon class="h-5 w-5" />
                            <div v-if="notificacionesStore.notificacionesPendientes">
                                <span class="absolute right-1.5 top-1.5 flex h-2.5 w-2.5">
                                    <span
                                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                                </span>
                            </div>
                        </button>
                        <Notificacion :show="isNotificationsOpen" @close="isNotificationsOpen = false" />
                    </div>
                </div>

                <div ref="userMenuContainer" class="relative">
                    <button @click="isUserMenuOpen = !isUserMenuOpen"
                        class="flex items-center gap-3 rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">
                        <span class="hidden lg:block text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ userFullName }}
                        </span>
                        <div class="block h-10 w-10 shrink-0 overflow-hidden rounded-full">
                            <img v-if="userStore.user?.avatar_url" :src="userStore.user.avatar_url" alt="Avatar"
                                class="h-full w-full object-cover">
                            <div v-else
                                class="flex h-full w-full items-center justify-center rounded-full bg-cetpro text-xl font-bold text-white">
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
                            :email="userStore.user?.email" :avatar-url="userStore.user?.avatar_url"
                            :is-dark-mode="isDarkMode" @logout="onLogout" @toggle-theme="updateDarkMode(!isDarkMode)"
                            @close-menu="isUserMenuOpen = false" />
                    </Transition>
                </div>
            </div>
        </div>
    </header>
</template>
