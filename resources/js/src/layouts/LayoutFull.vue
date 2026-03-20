<script setup>
import { ref } from 'vue';

import PageLoader from './PageLoader.vue';
import SuspenseFallback from './SuspenseFallback.vue';

const asyncLoading = ref(false);
</script>

<template>
    <main class="min-h-screen w-full relative">
        <PageLoader :loading="asyncLoading" />

        <div class="container mx-auto max-w-full px-4 lg:px-0">

            <RouterView v-slot="{ Component }">
                <template v-if="Component">
                    <Suspense
                        @pending="asyncLoading = true"
                        @resolve="asyncLoading = false"
                    >
                        <component :is="Component"></component>
                        <template #fallback>
                            <SuspenseFallback />
                        </template>
                    </Suspense>
                </template>
            </RouterView>
        </div>
    </main>
</template>
