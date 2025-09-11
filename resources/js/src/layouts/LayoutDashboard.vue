<script setup>
import { computed } from 'vue';
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

        <transition name="slide-fade">
            <SubSidebar v-if="submenuLinks.length > 0" :links="submenuLinks" />
        </transition>

        <div class="flex-1 flex flex-col overflow-hidden">
            <Header />
            <PageLoader :loading="layoutStore.isPageLoading" />
            <main class="flex-1 overflow-y-auto p-6">
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

<style>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateX(-20px);
  opacity: 0;
}
</style>