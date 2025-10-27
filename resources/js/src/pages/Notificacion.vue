<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { RouterLink } from 'vue-router';
import { ClockIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import useHttpRequest from '../composables/useHttpRequest'; 
import { formatDistanceToNow } from 'date-fns';
import { es } from 'date-fns/locale';

const props = defineProps({
  show: { type: Boolean, default: false }
});

const emit = defineEmits(['close']);

const notificationsContainer = ref(null);
const notifications = ref([]);
const { loading, index } = useHttpRequest('/actividades-recientes');
let pollingInterval = null;

const fetchNotifications = async () => {
  try {
    const data = await index();
    if (Array.isArray(data)) {
      notifications.value = data.slice(0, 5).map(actividad => ({
          id: actividad.id,
          icon: ClockIcon,
          title: actividad.accion || 'Actividad del Sistema',
          description: actividad.descripcion,
          time: formatDistanceToNow(new Date(actividad.fecha), { addSuffix: true, locale: es }),
          isRead: false,
          route: { name: 'notificaciones.index' } 
      }));
    } else {
      notifications.value = [];
    }
  } catch (error) {
    console.error("Error al cargar actividades:", error);
    notifications.value = [];
  }
};

const markAllAsRead = () => {
  notifications.value = [];
};

const handleClickOutside = (event) => {
    const bellButton = document.querySelector('[aria-label="Notificaciones"]');
    if (
        notificationsContainer.value && 
        !notificationsContainer.value.contains(event.target) &&
        !bellButton.contains(event.target)
    ) {
        emit('close');
    }
};

const handleKeydown = (e) => {
  if (e.key === 'Escape') emit('close');
};

watch(() => props.show, (newValue) => {
    if (newValue) {
        fetchNotifications();
    }
});

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
  document.addEventListener('mousedown', handleClickOutside);
  fetchNotifications();
  pollingInterval = setInterval(fetchNotifications, 60000);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
  document.removeEventListener('mousedown', handleClickOutside);
  clearInterval(pollingInterval);
});
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-100"
    enter-from-class="transform opacity-0 scale-95"
    enter-to-class="transform opacity-100 scale-100"
    leave-active-class="transition ease-in duration-75"
    leave-from-class="transform opacity-100 scale-100"
    leave-to-class="transform opacity-0 scale-95">
    <div
      v-if="show"
      ref="notificationsContainer"
      class="absolute right-0 top-full mt-2 w-80 md:w-96 origin-top-right rounded-xl bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
      
      <header class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Actividad Reciente</h3>
        <button v-if="!loading && notifications.length > 0" @click="markAllAsRead" class="text-xs font-semibold text-cetpro hover:underline">
          Limpiar
        </button>
      </header>

      <div v-if="loading" class="p-8 text-center text-gray-500">
          Cargando...
      </div>
      
      <ul v-else-if="notifications.length > 0" class="max-h-[400px] overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
        <li v-for="notification in notifications" :key="notification.id">
          <RouterLink
            :to="notification.route"
            @click="$emit('close')"
            class="flex items-start gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40">
              <component :is="notification.icon" class="h-6 w-6 text-blue-600 dark:text-blue-400"/>
            </div>
            <div class="flex-grow">
              <p class="font-bold text-sm text-gray-800 dark:text-gray-100">{{ notification.title }}</p>
              <p class="text-sm text-gray-600 dark:text-gray-300">{{ notification.description }}</p>
              <p class="text-xs mt-1 text-gray-500 dark:text-gray-400">{{ notification.time }}</p>
            </div>
          </RouterLink>
        </li>
      </ul>
      
      <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400">
        <p>No hay actividad reciente.</p>
      </div>
      
       <footer class="p-2 border-t border-gray-200 dark:border-gray-700">
        <RouterLink
          :to="{ name: 'notificaciones.index' }" 
          @click="$emit('close')"
          class="w-full flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-cetpro dark:text-cetpro-light hover:bg-gray-100 dark:hover:bg-gray-700">
          <span>Ver toda la actividad</span>
          <ArrowRightIcon class="h-4 w-4" />
        </RouterLink>
      </footer>
    </div>
  </Transition>
</template>