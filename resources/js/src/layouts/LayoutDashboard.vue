<script setup>
import { ref, computed, provide } from 'vue';
import { useRoute } from 'vue-router';
import { useLayoutStore } from '@/store/useLayoutStore';
import Sidebar from './components/Sidebar.vue';
import Header from './components/Header.vue';
import PageLoader from './PageLoader.vue';
import SuspenseFallback from './SuspenseFallback.vue';
import SubSidebar from './components/SubSidebar.vue';
import useUserStore from "../store/useUserStore";

const userStore = useUserStore();
const layoutStore = useLayoutStore();
const route = useRoute();

const isSubSidebarOpen = ref(true);
const toggleSubSidebar = () => {
  isSubSidebarOpen.value = !isSubSidebarOpen.value;
};
provide('subSidebarState', { isSubSidebarOpen, toggleSubSidebar });

const submenuLinks = computed(() => {
  const submenuMeta = route.meta.submenu;
  if (!submenuMeta) return [];
  if (typeof submenuMeta === 'function') {
    return submenuMeta(route);
  }
  return submenuMeta;
});
</script>

<template>
    <div class="flex h-screen bg-gray-100 dark:bg-slate-600 dark:text-gray-100 font-sans">
        <Sidebar />

        <transition
            enter-active-class="transition-all duration-300 ease-in-out"
            enter-from-class="-translate-x-full opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition-all duration-200 ease-in-out"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="-translate-x-full opacity-0"
        >
            <SubSidebar v-if="submenuLinks.length > 0 && isSubSidebarOpen" :links="submenuLinks" />
        </transition>

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