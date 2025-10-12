<script setup>
import { ref } from 'vue';
import { UserPlusIcon, UserGroupIcon, ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline';

import MatriculaForm from './MatriculaForm.vue';
import ListaGrupos from './ListaGrupos.vue';
import Reservas from './Reservas.vue';

const vistaActiva = ref('registrar');

const vistas = {
  registrar: MatriculaForm,
  grupos: ListaGrupos,
  reservas: Reservas,
};

const navLinks = [
  { text: 'Matricular Estudiante', vista: 'registrar', icon: UserPlusIcon },
  { text: 'Lista por Grupos', vista: 'grupos', icon: UserGroupIcon },
  { text: 'Estudiantes con Reserva', vista: 'reservas', icon: ClipboardDocumentCheckIcon },
];

const cambiarVista = (nombreVista) => {
  vistaActiva.value = nombreVista;
};
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col">
    <header class="px-6 pt-5">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
        Módulo de Matrículas
      </h1>
      <p class="text-md text-gray-500 dark:text-gray-400">
        Gestione el proceso de matrícula y las listas de estudiantes.
      </p>
    </header>

    <nav class="mt-4 px-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex space-x-4 sm:space-x-6 overflow-x-auto custom-scrollbar-nav">
        <button
          v-for="link in navLinks"
          :key="link.vista"
          @click="cambiarVista(link.vista)"
          class="flex items-center gap-2 py-3 px-1 text-sm font-medium border-b-2 transition-colors duration-200 whitespace-nowrap"
          :class="vistaActiva === link.vista 
            ? 'text-cetpro dark:text-cetpro-light border-cetpro font-semibold' 
            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-cetpro-dark dark:hover:text-cetpro-light hover:border-cetpro/50'"
        >
          <component :is="link.icon" class="h-5 w-5" />
          <span>{{ link.text }}</span>
        </button>
      </div>
    </nav>
    
    <div class="p-6 flex-grow">
      <component :is="vistas[vistaActiva]" />
    </div>
  </div>
</template>

<style scoped>
.custom-scrollbar-nav::-webkit-scrollbar {
  height: 4px;
}
.custom-scrollbar-nav::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar-nav::-webkit-scrollbar-thumb {
  background-color: #d1d5db;
  border-radius: 20px;
}
.dark .custom-scrollbar-nav::-webkit-scrollbar-thumb {
  background-color: #4b5563;
}
</style>