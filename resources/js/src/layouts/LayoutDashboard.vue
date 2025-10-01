<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useLayoutStore } from '@/store/useLayoutStore';
import Sidebar from './components/Sidebar.vue';
import Header from './components/Header.vue';
import PageLoader from './PageLoader.vue';
import SuspenseFallback from './SuspenseFallback.vue';
import useUserStore from "../store/useUserStore";

const userStore = useUserStore();
const layoutStore = useLayoutStore();
const route = useRoute();
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