<script setup>
import { inject } from 'vue';
import { ChevronLeftIcon } from '@heroicons/vue/24/outline';

defineProps({
  links: {
    type: Array,
    required: true,
    default: () => [],
  },
});

const { toggleSubSidebar } = inject('subSidebarState');
</script>

<template>
  <aside
    class="absolute top-0 left-24 z-30 h-full w-64
           bg-white dark:bg-slate-800
           border-r border-gray-200 dark:border-slate-700
           shadow-xl flex flex-col"
  >
    <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-slate-700 flex-shrink-0">
      <h2 class="font-semibold text-gray-800 dark:text-gray-200">Menú de Grupo</h2>
      <button
        @click="toggleSubSidebar"
        class="p-2 text-gray-500 dark:text-gray-400
               hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
      >
        <ChevronLeftIcon class="h-5 w-5" />
      </button>
    </div>

    <nav class="flex-grow p-4 space-y-1 overflow-y-auto">
      <router-link
        v-for="link in links"
        :key="link.text"
        :to="link.to"
        class="flex items-center w-full px-4 py-2.5 text-sm transition-all duration-150
               text-gray-600 dark:text-gray-300
               border-l-4 border-transparent
               hover:bg-gray-100 dark:hover:bg-slate-700"
        active-class="!border-blue-500 bg-blue-50 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 font-semibold"
      >
        {{ link.text }}
      </router-link>
    </nav>
  </aside>
</template>