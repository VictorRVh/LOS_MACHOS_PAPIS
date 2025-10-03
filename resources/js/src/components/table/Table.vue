<script setup>
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/solid';

defineProps({
  currentPage: { type: Number, default: 1 },
  totalPages: { type: Number, default: 1 },
  paginacion: {
    type: Boolean,
    default: false
  }
});

defineEmits(['changePage']);
</script>

<template>
  <div class="w-full">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <slot></slot>
        </table>
      </div>
    </div>

    <div
      v-if="paginacion && totalPages > 1"
      class="flex items-center justify-between mt-4 px-2"
    >
      <span class="text-sm text-gray-700 dark:text-gray-300">
        Página {{ currentPage }} de {{ totalPages }}
      </span>

      <div class="inline-flex items-center -space-x-px">
        <button
          @click="$emit('changePage', currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-3 py-2 leading-tight text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <span class="sr-only">Anterior</span>
          <ChevronLeftIcon class="h-4 w-4" />
        </button>
        
        <button
          @click="$emit('changePage', currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-3 py-2 leading-tight text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <span class="sr-only">Siguiente</span>
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>
    </div>
  </div>
</template>