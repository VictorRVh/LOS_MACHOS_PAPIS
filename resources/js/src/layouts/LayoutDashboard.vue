<script setup>
import { useLayoutStore } from '@/store/useLayoutStore';
import Sidebar from './components/Sidebar.vue';
import Header from './components/Header.vue';
import PageLoader from './PageLoader.vue';
import SuspenseFallback from './SuspenseFallback.vue';

const layoutStore = useLayoutStore();
</script>

<template>
    <div class="flex h-screen bg-gray-100 dark:bg-cc-10 font-sans">
        <!-- 1. SIDEBAR LATERAL (AZUL) -->
        <Sidebar />

        <!-- 2. ÁREA DE CONTENIDO PRINCIPAL -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- 2A. HEADER SUPERIOR (BLANCO) -->
            <Header />

            <!-- PageLoader tipo barra de progreso -->
            <PageLoader :loading="layoutStore.isPageLoading" />

            <!-- 2B. CONTENIDO DE LA PÁGINA (CON SCROLL) -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto">
                <RouterView v-slot="{ Component }">
                    <Suspense
                        @pending="layoutStore.setPageLoading(true)"
                        @resolve="layoutStore.setPageLoading(false)"
                        @fallback="layoutStore.setPageLoading(false)"
                    >
                        <!-- El componente de la página actual se renderiza aquí -->
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