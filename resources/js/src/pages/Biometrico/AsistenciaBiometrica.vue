<script setup>
import { ref, onMounted, computed } from 'vue';
import { RouterLink } from 'vue-router';

// Simulación de los ciclos y turnos asignados al docente logueado
const asignaciones = ref([]);

const getProgressStyle = (porcentaje) => computed(() => ({
  strokeDasharray: `${porcentaje}, 100`
}));

onMounted(() => {
  // Aquí harías una llamada a la API para traer las asignaciones del docente
  asignaciones.value = [
    { id: 1, ciclo: 'Ciclo Técnico - 2025 I', programa: 'Peluquería', turno: 'Mañana', porcentajeAsistencia: 98 },
    { id: 2, ciclo: 'Ciclo Técnico - 2025 I', programa: 'Barbería', turno: 'Noche', porcentajeAsistencia: 95 },
    { id: 3, ciclo: 'Ciclo Auxiliar - 2025 II', programa: 'Maquillaje Profesional', turno: 'Tarde', porcentajeAsistencia: 100 },
    { id: 4, ciclo: 'Ciclo Auxiliar - 2025 II', programa: 'Carpintería', turno: 'Mañana', porcentajeAsistencia: 87 },
  ];
});
</script>

<template>
  <div class="p-4 sm:p-6 space-y-4">
    <header>
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
        Mis Asignaciones de Asistencia
      </h1>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Selecciona un ciclo para ver el historial detallado de tus registros.
      </p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="asignacion in asignaciones" :key="asignacion.id" class="bg-white dark:bg-slate-800 rounded-lg shadow-md flex flex-col overflow-hidden">
        <div class="p-4 flex-grow">
          <div class="flex justify-between items-start">
            <div>
              <p class="font-bold text-gray-800 dark:text-gray-100">{{ asignacion.ciclo }}</p>
              <p class="text-sm text-cetpro dark:text-cetpro-light font-semibold">{{ asignacion.programa }}</p>
            </div>
            <span class="text-xs font-semibold px-2 py-1 bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 rounded-full">{{ asignacion.turno }}</span>
          </div>

          <div class="flex items-center justify-center gap-4 my-6">
            <div class="relative w-20 h-20">
              <svg class="w-full h-full" viewBox="0 0 36 36">
                <path class="text-gray-200 dark:text-slate-700" stroke-width="4" fill="none" stroke="currentColor" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                <path class="text-cetpro" stroke-width="4" fill="none" stroke-linecap="round" stroke="currentColor" :style="getProgressStyle(asignacion.porcentajeAsistencia).value" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
              </svg>
              <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-xl font-bold text-gray-700 dark:text-gray-200">{{ asignacion.porcentajeAsistencia }}%</span>
              </div>
            </div>
            <div class="text-sm">
                <p class="font-semibold text-gray-700 dark:text-gray-200">Asistencia General</p>
                <p class="text-gray-500 dark:text-gray-400">Promedio del ciclo</p>
            </div>
          </div>
        </div>
        
        <RouterLink 
          :to="{ name: 'biometrico.detalle', params: { idAsignacion: asignacion.id } }" 
          class="block w-full text-center p-3 bg-gray-50 dark:bg-slate-700/50 text-sm font-bold text-cetpro dark:text-cetpro-light hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
        >
          Ver Registros
        </RouterLink>
      </div>
    </div>
  </div>
</template>