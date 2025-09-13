<script setup>
import { onMounted, ref, inject } from 'vue';
import { Bars3Icon } from '@heroicons/vue/24/outline';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});

const { isSubSidebarOpen, toggleSubSidebar } = inject('subSidebarState');
const nombreGrupo = ref('');

onMounted(() => {
  nombreGrupo.value = `Grupo ${props.id}`;
});
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center space-x-4">
      <transition
          enter-active-class="transition-opacity duration-300"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition-opacity duration-300"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
      >
          <button
            v-if="!isSubSidebarOpen"
            @click="toggleSubSidebar"
            class="inline-flex items-center px-3 py-1.5 bg-light-color hover:bg-cetpro-dark dark:bg-blue-600 dark:hover:bg-blue-700 text-white  text-sm font-medium transition-colors shadow-sm"
          >
            <Bars3Icon class="h-5 w-5 mr-2" />
            <span>Abrir SubMenú</span>
          </button>
      </transition>
      
      <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 truncate">
        {{ nombreGrupo }}
      </h1>
    </div>
    
    <router-view />
  </div>
</template>