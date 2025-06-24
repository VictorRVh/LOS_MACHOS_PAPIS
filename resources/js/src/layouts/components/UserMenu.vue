<!-- src/components/UserMenu.vue -->
<script setup>
import { ref, inject } from 'vue';

const props = defineProps({
  nombre: String,
  apellido: String
});

const isOpen = ref(false);

const { isDarkMode, updateDarkMode, windowWidth } = inject('theme');

const emit = defineEmits(['logout']);

const toggleMenu = () => {
  isOpen.value = !isOpen.value;
};

const logout = () => {
  emit('logout');
  isOpen.value = false;
};
</script>

<template>
  <div class="relative inline-block text-left">
    <div class="flex items-center gap-2 cursor-pointer" @click="toggleMenu">
      <span class="font-semibold">{{ nombre }} {{ apellido }}</span>
      <svg
        class="w-4 h-4"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        viewBox="0 0 24 24"
      >
        <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </div>

    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg z-50"
    >
      <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">
        {{ nombre }} {{ apellido }}
      </div>
      <div class="border-t dark:border-gray-600"></div>

      <button
        class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm"
        @click="updateDarkMode(!isDarkMode)"
      >
        {{ isDarkMode ? 'Modo Claro' : 'Modo Oscuro' }}
      </button>

      <button
        class="w-full text-left px-4 py-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-800 text-sm"
        @click="logout"
      >
        Cerrar sesión
      </button>
    </div>
  </div>
</template>

<style scoped>
</style>
