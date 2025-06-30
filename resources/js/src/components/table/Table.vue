<template>
  <div class="m-2 font-inter h-full">
    <div
      class="relative shadow-md sm:rounded-lg md:max-h-[330px] xl:max-h-[700px] overflow-y-auto overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-transparent"
    >
      <table
        class="table-auto border-collapse border border-plomo-700 dark:border-slate-700 w-full text-[13px] text-center text-gray-200 dark:text-gray-400"
      >
        <slot></slot>
      </table>
    </div>

    <!-- PAGINACIÓN SOLO SI SE ACTIVA -->
    <div
      v-if="paginacion"
      class="flex justify-end mt-2 space-x-1 text-sm "
    >
      <button
        class="px-3 py-1 bg-gray-800 text-white dark:bg-gray-700 hover:gray-700 rounded disabled:opacity-60"
        :disabled="currentPage === 1"
        @click="$emit('changePage', currentPage - 1)"
      >
        ← Anterior
      </button>

      <span class="px-3 py-1">{{ currentPage }} / {{ totalPages }}</span>

      <button
        class="px-3 py-1 bg-gray-800 text-white dark:bg-gray-700 hover:gray-700 rounded disabled:opacity-60"
        :disabled="currentPage === totalPages"
        @click="$emit('changePage', currentPage + 1)"
      >
        Siguiente →
      </button>
    </div>
  </div>
</template>


<script setup>
defineProps({
  currentPage: Number,
  totalPages: Number,
  paginacion: {
    type: Boolean,
    default: false
  }
})
</script>
