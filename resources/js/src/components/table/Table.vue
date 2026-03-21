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
    <div
      class="overflow-hidden rounded-sm border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-gray-800">
      <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm text-slate-700 dark:text-slate-200">
          <slot></slot>
        </table>
      </div>
    </div>

    <div
      v-if="paginacion && totalPages > 1"
      class="mt-3 flex items-center justify-between px-1"
    >
      <span class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
        Pagina {{ currentPage }} de {{ totalPages }}
      </span>

      <div class="inline-flex items-center gap-1">
        <button
          @click="$emit('changePage', currentPage - 1)"
          :disabled="currentPage === 1"
          class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-gray-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
        >
          <span class="sr-only">Anterior</span>
          <ChevronLeftIcon class="h-4 w-4" />
        </button>

        <button
          @click="$emit('changePage', currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-gray-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
        >
          <span class="sr-only">Siguiente</span>
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>
    </div>
  </div>
</template>
