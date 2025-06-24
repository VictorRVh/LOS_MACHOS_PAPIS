<script setup>
defineProps({
  nombre: String,
  apellido: String,
});

const emit = defineEmits(['logout', 'toggle-theme']);
</script>

<template>
  <div
    class="w-48 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg z-50"
  >
    <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">
      {{ nombre }} {{ apellido }}
    </div>
    <div class="border-t dark:border-gray-600"></div>

    <button
      class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm"
      @click="$emit('toggle-theme')"
    >
      Cambiar tema
    </button>

    <button
      class="w-full text-left px-4 py-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-800 text-sm"
      @click="$emit('logout')"
    >
      Cerrar sesión
    </button>
  </div>
</template>
