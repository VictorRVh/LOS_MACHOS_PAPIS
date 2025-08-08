<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { RouterLink } from 'vue-router';
import {
  ArrowLeftOnRectangleIcon,
  Cog6ToothIcon,
  SunIcon,
  MoonIcon,
  FingerPrintIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  nombre: String,
  apellido: String,
  email: String,
  isDarkMode: Boolean
});

const emit = defineEmits(['logout', 'toggle-theme', 'close-menu']);

const biometricStatus = ref(null);

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
  setTimeout(() => {
    biometricStatus.value = {
      ingreso: { registrada: true, hora: '08:05 AM' },
      salida: { registrada: false, hora: null }
    };
  }, 1000);
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
        >
          <SunIcon v-if="isDarkMode" class="h-5 w-5" />
          <MoonIcon v-else class="h-5 w-5" />
          <span>Cambiar a tema {{ isDarkMode ? 'claro' : 'oscuro' }}</span>
        </button>

        <RouterLink
          :to="{ name: 'cuenta.editar' }"
          @click="$emit('close-menu')"
          class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <Cog6ToothIcon class="h-5 w-5" />
          <span>Configuración de cuenta</span>
        </RouterLink>
      </div>
      
      <hr class="border-gray-200 dark:border-gray-700" />

      <div class="p-2">
          <div v-if="!biometricStatus" class="flex items-center justify-center p-3 text-sm text-gray-500">
            Cargando asistencia...
          </div>
          <div v-else class="space-y-2 px-3 py-2 text-sm">
            <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-600 dark:text-gray-300">Ingreso:</span>
                <span v-if="biometricStatus.ingreso.registrada" class="font-bold text-emerald-600 dark:text-emerald-400">{{ biometricStatus.ingreso.hora }}</span>
                <span v-else class="font-semibold text-amber-600 dark:text-amber-400">Pendiente</span>
            </div>
             <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-600 dark:text-gray-300">Salida:</span>
                <span v-if="biometricStatus.salida.registrada" class="font-bold text-emerald-600 dark:text-emerald-400">{{ biometricStatus.salida.hora }}</span>
                <span v-else class="font-semibold text-amber-600 dark:text-amber-400">Pendiente</span>
            </div>
          </div>

          <RouterLink
              :to="{ name: 'biometrico.asistencia' }"
              @click="$emit('close-menu')"
              class="w-full flex items-center justify-center gap-3 rounded-lg py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
              <FingerPrintIcon class="h-5 w-5" />
              <span>Ver mi historial completo</span>
          </RouterLink>
      </div>

      <hr class="border-gray-200 dark:border-gray-700" />
      
      <div class="p-2">
        <button
          @click="$emit('logout')"
          class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-red-600 dark:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10"
        >
          <ArrowLeftOnRectangleIcon class="h-5 w-5" />
          <span>Cerrar sesión</span>
        </button>
      </div>
    </div>
  </div>
</template>