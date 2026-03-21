<script setup>
import { TrashIcon } from "@heroicons/vue/24/outline";
</script>

<template>
  <button
    type="button"
    class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-sm border border-red-200 bg-red-50 px-2.5 py-1.5 text-xsm font-medium text-red-700 transition-colors duration-150 hover:bg-red-100 active:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-200 focus-visible:ring-offset-1 focus-visible:ring-offset-white disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-60 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300 dark:hover:bg-red-950/45 dark:active:bg-red-950/55 dark:focus-visible:ring-offset-slate-900"
  >
    <TrashIcon class="h-4 w-4 shrink-0" />
    <span class="leading-none">Eliminar</span>
  </button>
</template>
