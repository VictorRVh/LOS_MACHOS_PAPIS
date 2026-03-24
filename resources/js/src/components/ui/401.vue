<script setup>
import useUtils from '../../composables/useUtils';

defineProps({
    permissions: {
        type: Array,
        default: () => [],
    },
});

const { stringCapitalize } = useUtils();
</script>

<template>
    <div class="w-full flex items-center justify-center bg-white dark:bg-gray-900 p-4 transition-colors duration-300">
        <div
            class="bg-white dark:bg-gray-800 dark:border dark:border-gray-700 rounded-2xl  text-center transform transition-all duration-300 ease-in-out animate-fade-in-up">

            <img src="/img/zona-prohibida.png" alt="Ilustración de Acceso Prohibido"
                class="mx-auto w-full max-w-[210px] mb-8 dark:opacity-90">

            <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-100">Acceso Denegado</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Lo sentimos, tu rol actual no te permite acceder a esta sección.
            </p>

            <div v-if="permissions.length > 0"
                class="mt-4 text-center bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border dark:border-gray-600">

                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                    Se requiere al menos uno de los siguientes permisos:
                </p>

                <div class="flex flex-wrap justify-center gap-2">
                    <span v-for="permission in permissions" :key="permission"
                        class="bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-300 text-xs font-medium px-2.5 py-1 rounded-full text-center">
                        {{ stringCapitalize(permission?.replaceAll('-', ' ')) }}
                    </span>
                </div>

            </div>


        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.4s ease-out forwards;
}
</style>