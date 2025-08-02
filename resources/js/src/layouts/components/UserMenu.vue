<script setup>
import { onMounted, onUnmounted } from 'vue';
import { RouterLink } from 'vue-router'; // Importar RouterLink
import {
  ArrowLeftOnRectangleIcon,
  Cog6ToothIcon,
  SunIcon,
  MoonIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  nombre: String,
  apellido: String,
  email: String,
  isDarkMode: Boolean
});

const emit = defineEmits(['logout', 'toggle-theme', 'close-menu']);

const getInitials = (name, lastName) => {
  if (!name) return '?';
  const firstInitial = name[0];
  const lastInitial = lastName ? lastName[0] : '';
  return `${firstInitial}${lastInitial}`.toUpperCase();
};

const handleKeydown = (e) => {
  if (e.key === 'Escape') {
    emit('close-menu');
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <div
    class="absolute right-0 mt-2 w-72 origin-top-right rounded-xl bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
    role="menu"
    aria-orientation="vertical"
    aria-labelledby="user-menu-button"
    tabindex="-1"
    v-on-click-outside="() => emit('close-menu')"
  >
    <div class="py-1">
      <div class="flex items-center gap-4 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-cetpro text-white text-xl font-bold">
          {{ getInitials(nombre, apellido) }}
        </div>
        <div>
          <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
            {{ nombre }} {{ apellido }}
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
            {{ email }}
          </p>
        </div>
      </div>
      
      <div class="p-2">
        <button
          @click="$emit('toggle-theme')"
          class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
          role="menuitem"
          tabindex="-1"
        >
          <SunIcon v-if="isDarkMode" class="h-5 w-5" />
          <MoonIcon v-else class="h-5 w-5" />
          <span>Cambiar a tema {{ isDarkMode ? 'claro' : 'oscuro' }}</span>
        </button>

        <!-- CAMBIADO A ROUTERLINK -->
        <RouterLink
          :to="{ name: 'cuenta.editar' }"
          @click="$emit('close-menu')"
          class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
          role="menuitem"
          tabindex="-1"
        >
          <Cog6ToothIcon class="h-5 w-5" />
          <span>Configuración de cuenta</span>
        </RouterLink>
      </div>
      
      <hr class="border-gray-200 dark:border-gray-700" />
      
      <div class="p-2">
        <button
          @click="$emit('logout')"
          class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-red-600 dark:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10"
          role="menuitem"
          tabindex="-1"
        >
          <ArrowLeftOnRectangleIcon class="h-5 w-5" />
          <span>Cerrar sesión</span>
        </button>
      </div>
    </div>
  </div>
</template>