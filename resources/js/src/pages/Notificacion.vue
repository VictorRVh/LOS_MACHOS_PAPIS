<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { ClockIcon, ArrowRightIcon } from '@heroicons/vue/24/outline'
import useNotificacionesStore from '../store/Notificaciones/UseNotificacionesStore'

/* Props */
const props = defineProps({
  show: { type: Boolean, default: false }
})

/* Emits */
const emit = defineEmits(['close'])

/* Store */
const notificacionesStore = useNotificacionesStore()

/* Acceso reactivo a las notificaciones */
const notificaciones = computed(() => notificacionesStore.notificaciones)

/* Contenedor para detectar clic afuera */
const notificacionesContainer = ref(null)

/* Cargar cuando el popup se abre */
watch(() => props.show, async (open) => {
  if (open) {
    await notificacionesStore.loadNotificaciones()
  }
})

/* Click fuera */
const handleClickOutside = (event) => {
  if (
    notificacionesContainer.value &&
    !notificacionesContainer.value.contains(event.target)
  ) {
    emit('close')
  }
}

/* Escape para cerrar */
const handleKeydown = (e) => {
  if (e.key === 'Escape') emit('close')
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})

/* Limpiar notificaciones (opcional: puedes llamar al backend si deseas) */
const markAllAsRead = async () => {
  await notificacionesStore.loadNotificacionesMarcarTodo();
  await notificacionesStore.loadNotificaciones();
};

</script>

<template>
  <Transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95"
    enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75"
    leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
    <div v-if="show" ref="notificacionesContainer"
      class="absolute right-0 top-full mt-2 w-80 md:w-96 origin-top-right rounded-xl bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 z-50">
      <!-- HEADER -->
      <header class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
          Actividad Reciente
        </h3>

        <button v-if="notificaciones.length > 0" @click="markAllAsRead"
          class="text-xs font-semibold text-cetpro hover:underline">
          Limpiar
        </button>
      </header>

      <!-- LISTA -->
      <ul v-if="notificaciones.length > 0"
        class="max-h-[400px] overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
        <li v-for="n in notificaciones" :key="n.id">

          <RouterLink :to="{ name: 'notifi.index', query: { highlight: n.id } }" @click="$emit('close')"
            class="flex items-start gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40">
              <ClockIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            </div>

            <div class="flex-grow">
              <p class="font-bold text-sm text-gray-800 dark:text-gray-100">
                {{ n.titulo }}
              </p>
              <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ n.descripcion }}
              </p>
            </div>
          </RouterLink>
        </li>
      </ul>

      <!-- SIN ACTIVIDAD -->
      <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400">
        No hay actividad reciente
      </div>

      <!-- FOOTER -->
      <footer class="p-2 border-t border-gray-200 dark:border-gray-700">
        <RouterLink :to="{ name: 'notifi.index' }" @click="$emit('close')"
          class="w-full flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-cetpro hover:bg-gray-100 dark:hover:bg-gray-700">
          <span>Ver toda la actividad</span>
          <ArrowRightIcon class="h-4 w-4" />
        </RouterLink>
      </footer>
    </div>
  </Transition>
</template>
