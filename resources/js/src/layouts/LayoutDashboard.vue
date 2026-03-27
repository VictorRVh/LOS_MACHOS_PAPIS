<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useLayoutStore } from '@/store/useLayoutStore';
import Sidebar from './components/Sidebar.vue';
import Header from './components/Header.vue';
import PageLoader from './PageLoader.vue';
import SuspenseFallback from './SuspenseFallback.vue';
import useUserStore from "../store/useUserStore";
import useHttpRequest from '../composables/useHttpRequest';
import useAppRouter from '../composables/useAppRouter';
import useModalToast from '../composables/useModalToast';

const userStore = useUserStore();
const layoutStore = useLayoutStore();
const route = useRoute();
const { index: logout } = useHttpRequest('/logout');
const { pushToRoute } = useAppRouter();
const { showToast } = useModalToast();

let inactivityTimer = null;
const INACTIVITY_LIMIT = 60 * 60 * 1000;

const clearInactivityTimer = () => {
    if (inactivityTimer) {
        clearTimeout(inactivityTimer);
        inactivityTimer = null;
    }
};

const forceLogoutByInactivity = async () => {
    try {
        await logout();
    } catch {
        // Si la sesion ya expiro en backend, igual continuamos con la limpieza local.
    }

    userStore.setUser(null);
    showToast('La sesion se cerro por inactividad.', 'warning');
    await pushToRoute({ name: 'login' });
};

const resetInactivityTimer = () => {
    clearInactivityTimer();

    inactivityTimer = setTimeout(async () => {
        await forceLogoutByInactivity();
    }, INACTIVITY_LIMIT);
};

const activityEvents = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'];

onMounted(() => {
    activityEvents.forEach((eventName) => {
        window.addEventListener(eventName, resetInactivityTimer, { passive: true });
    });

    resetInactivityTimer();
});

onUnmounted(() => {
    clearInactivityTimer();

    activityEvents.forEach((eventName) => {
        window.removeEventListener(eventName, resetInactivityTimer);
    });
});
</script>

<template>
    <div class="flex h-screen bg-gray-100 dark:bg-slate-600 dark:text-gray-100 font-sans">
        
        <div 
            v-if="layoutStore.isSidebarOpenMobile" 
            @click="layoutStore.toggleSidebarMobile" 
            class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"
        ></div>
        
        <Sidebar />

        <div class="flex-1 flex flex-col overflow-hidden">
            <Header />
            <PageLoader :loading="layoutStore.isPageLoading" />
            <main class="flex-1 overflow-y-auto">
                 <RouterView v-slot="{ Component }">
                    <Suspense
                        @pending="layoutStore.setPageLoading(true)"
                        @resolve="layoutStore.setPageLoading(false)"
                        @fallback="layoutStore.setPageLoading(false)"
                    >
                        <component :is="Component"></component>
                        <template #fallback>
                            <SuspenseFallback />
                        </template>
                    </Suspense>
                </RouterView>
            </main>
        </div>
    </div>
</template>
