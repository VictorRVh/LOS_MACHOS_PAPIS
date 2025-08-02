<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { RouterLink } from 'vue-router';
import {
  CheckCircleIcon,
  UserPlusIcon,
  AcademicCapIcon,
  ArrowRightIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  show: { type: Boolean, default: false }
});

const emit = defineEmits(['close']);

const notifications = ref([]);

const markAllAsRead = () => {
  notifications.value.forEach(n => n.isRead = true);
};

const handleKeydown = (e) => {
  if (e.key === 'Escape') {
    emit('close');
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
  notifications.value = [
    {
      id: 1,
      icon: UserPlusIcon,
      text: 'Se ha registrado un nuevo docente: Harol Flores.',
      time: 'hace 5 minutos',
      isRead: false,
      route: { name: 'docente' }
    },
    {
      id: 2,
      icon: AcademicCapIcon,
      text: 'El programa "Gastronomía 2026" ha sido aprobado por dirección.',
      time: 'hace 2 horas',
      isRead: false,
      route: { name: 'programa' }
    },
    {
      id: 3,
      icon: CheckCircleIcon,
      text: 'Tu solicitud de matrícula para el grupo 1243 ha sido procesada.',
      time: 'ayer',
      isRead: true,
      route: { name: 'matricula' }
    },
    {
      id: 4,
      icon: UserPlusIcon,
      text: 'Tienes 3 nuevas solicitudes de matrícula pendientes de revisión.',
      time: 'hace 2 días',
      isRead: true,
      route: { name: 'matricula' }
    },
  ];
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-100"
    enter-from-class="transform opacity-0 scale-95"
    enter-to-class="transform opacity-100 scale-100"
    leave-active-class="transition ease-in duration-75"
    leave-from-class="transform opacity-100 scale-100"
    leave-to-class="transform opacity-0 scale-95"
  >
    <div
      v-if="show"
      class="absolute right-0 top-full mt-2 w-80 md:w-96 origin-top-right rounded-xl bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
      v-on-click-outside="() => emit('close')"
    >
      <header class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Notificaciones</h3>
        <button @click="markAllAsRead" class="text-xs font-semibold text-cetpro hover:underline">
          Marcar todo como leído
        </button>
      </header>

      <ul class="max-h-[400px] overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
        <li v-for="notification in notifications" :key="notification.id">
          <RouterLink
            :to="notification.route"
            @click="$emit('close')"
            class="flex items-start gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
            :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notification.isRead }"
          >
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-700">
              <component :is="notification.icon" class="h-6 w-6 text-gray-500 dark:text-gray-300" />
            </div>
            <div class="flex-grow">
              <p class="text-sm text-gray-800 dark:text-gray-100">{{ notification.text }}</p>
              <p class="text-xs mt-1" :class="!notification.isRead ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-gray-500 dark:text-gray-400'">
                {{ notification.time }}
              </p>
            </div>
            <div v-if="!notification.isRead" class="h-2.5 w-2.5 mt-1 shrink-0 rounded-full bg-blue-500"></div>
          </RouterLink>
        </li>
      </ul>
      
       <footer class="p-2 border-t border-gray-200 dark:border-gray-700">
        <RouterLink
          :to="{ name: 'notificaciones.index' }" 
          @click="$emit('close')"
          class="w-full flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-cetpro dark:text-cetpro-light hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <span>Ver todas las notificaciones</span>
          <ArrowRightIcon class="h-4 w-4" />
        </RouterLink>
      </footer>
    </div>
  </Transition>
</template>