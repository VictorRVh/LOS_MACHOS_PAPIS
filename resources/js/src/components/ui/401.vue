<script setup>
import { computed } from 'vue';
import { ExclamationTriangleIcon, LockClosedIcon } from '@heroicons/vue/24/outline';
import { useRoute } from 'vue-router';
import useUtils from '../../composables/useUtils';

const props = defineProps({
    permissions: {
        type: Array,
        default: () => [],
    },
});

const { stringCapitalize } = useUtils();
const route = useRoute();

const normalizedPermissions = computed(() =>
    props.permissions.map((permission) =>
        stringCapitalize(permission?.replaceAll('-', ' '))
    )
);

const currentModule = computed(() => {
    const breadcrumb = route.meta?.breadcrumb;

    if (Array.isArray(breadcrumb) && breadcrumb.length > 0) {
        return breadcrumb[breadcrumb.length - 1]?.text || 'este módulo';
    }

    if (breadcrumb && typeof breadcrumb === 'object' && breadcrumb.text) {
        return breadcrumb.text;
    }

    return 'este módulo';
});

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <div class="w-full bg-white px-4 py-8 transition-colors duration-300 dark:bg-gray-900">
        <div class="mx-auto max-w-3xl text-center">
            <div class="flex flex-col items-center gap-4">
                <div class="min-w-0">
                    <div class="flex items-center justify-center gap-2">
                        <LockClosedIcon class="h-5 w-5 shrink-0 text-red-600 dark:text-red-400" />
                        <h1 class="text-lg font-semibold text-red-600 dark:text-red-400">
                            No tienes permisos para acceder a {{ currentModule }}
                        </h1>
                    </div>

                </div>

                <div class="flex flex-wrap justify-center gap-2">
                    <a
                        href="/"
                        class="inline-flex h-8 items-center justify-center rounded-sm border border-cetpro/20 bg-cetpro/10 px-3 text-sm font-medium text-cetpro transition-colors duration-150 hover:bg-cetpro/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cetpro/20 dark:border-cetpro-light/25 dark:bg-cetpro-light/10 dark:text-cetpro-light dark:hover:bg-cetpro-light/15"
                    >
                        Inicio
                    </a>

                    <button
                        type="button"
                        class="inline-flex h-8 items-center justify-center rounded-sm border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 transition-colors duration-150 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        @click="goBack"
                    >
                        Volver
                    </button>
                </div>
            </div>

            <div v-if="normalizedPermissions.length > 0" class="mt-5">
                <div class="flex flex-col items-center gap-2">
                    <ExclamationTriangleIcon class="h-4 w-4 shrink-0 text-red-600 dark:text-red-400" />
                    <div class="min-w-0">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-red-600 dark:text-red-400">
                            Permisos necesarios
                        </div>
                        <ul class="mt-3 space-y-1.5 text-sm text-slate-700 dark:text-slate-200">
                            <li
                                v-for="permission in normalizedPermissions"
                                :key="permission"
                                class="flex items-start justify-center gap-2"
                            >
                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                <span>{{ permission }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
