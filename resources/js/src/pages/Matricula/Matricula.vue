<script setup>
import { UserPlusIcon, UserGroupIcon, ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();


const navLinks = [
  { text: 'Matricular Estudiante', to: { name: 'matricula.registrar' }, icon: UserPlusIcon },
  { text: 'Lista por Grupos', to: { name: 'matricula.grupos' }, icon: UserGroupIcon },
  { text: 'Estudiantes con Reserva', to: { name: 'matricula.reservas' }, icon: ClipboardDocumentCheckIcon },
];


const isActive = (to) => route.name === to.name;
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col">

    <header class="px-8 pt-2">
      <h1 class="text-xl font-bold">Módulo de Matrículas</h1>
    </header>

    <nav class="px-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex space-x-4 overflow-x-auto custom-scrollbar-nav">

        <button v-for="link in navLinks" :key="link.text" @click="router.push(link.to)"
          class="flex items-center gap-2 py-2 px-1 text-sm font-medium border-b-2" :class="isActive(link.to)
            ? 'text-cetpro border-cetpro font-semibold'
            : 'border-transparent text-gray-500 hover:text-cetpro hover:border-cetpro/50'">
          <component :is="link.icon" class="h-5 w-5" />
          <span>{{ link.text }}</span>
        </button>

      </div>
    </nav>

    <div class="p-4 flex-grow">
      <router-view />
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