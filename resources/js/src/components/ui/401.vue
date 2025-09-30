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
    <div class="w-full min-h-[calc(100vh-80px)] flex items-center justify-center bg-white dark:bg-gray-900 p-4 transition-colors duration-300">
        <div class="bg-white dark:bg-gray-800 dark:border dark:border-gray-700 rounded-2xl  text-center transform transition-all duration-300 ease-in-out animate-fade-in-up">
            
            <img 
                src="/img/zona-prohibida.png" 
                alt="Ilustración de Acceso Prohibido" 
                class="mx-auto w-full max-w-[250px] mb-8 dark:opacity-90"
            >

            <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-100">Acceso Denegado</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Lo sentimos, tu rol actual no te permite acceder a esta sección.
            </p>

            <div v-if="permissions.length > 0" class="mt-8 text-left bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border dark:border-gray-600">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                    Se requiere al menos uno de los siguientes permisos:
                </p>
                <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                    <span
                        v-for="permission in permissions"
                        :key="permission"
                        class="bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-300 text-xs font-medium px-2.5 py-1 rounded-full"
                    >
                        {{ stringCapitalize(permission?.replaceAll('-', ' ')) }}
                    </span>
                </div>
            </div>

            <div class="mt-8">
                <router-link
                    to="/"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 shadow-sm transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Volver al Inicio
                </router-link>
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