<script setup>
import { ref, onMounted, computed } from 'vue';
import { RouterLink } from 'vue-router';
import {
  CheckCircleIcon,
  UserPlusIcon,
  AcademicCapIcon,
  ArchiveBoxXMarkIcon,
  KeyIcon,
} from '@heroicons/vue/24/outline';
import Button from '@/components/ui/Button.vue';

const allNotifications = ref([]);

const unreadNotifications = computed(() => allNotifications.value.filter(n => !n.isRead));
const readNotifications = computed(() => allNotifications.value.filter(n => n.isRead));

const markAllAsRead = () => {
  allNotifications.value.forEach(n => n.isRead = true);
};

const clearRead = () => {
  allNotifications.value = allNotifications.value.filter(n => !n.isRead);
};

onMounted(() => {
  allNotifications.value = [
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
      icon: KeyIcon,
      text: 'Se han actualizado los permisos para el rol de "Secretaria".',
      time: 'hace 8 horas',
      isRead: false,
      route: { name: 'roles' }
    },
    {
      id: 4,
      icon: CheckCircleIcon,
      text: 'Tu solicitud de matrícula para el grupo 1243 ha sido procesada.',
      time: 'ayer',
      isRead: true,
      route: { name: 'matricula' }
    },
    {
      id: 5,
      icon: UserPlusIcon,
      text: 'Tienes 3 nuevas solicitudes de matrícula pendientes de revisión.',
      time: 'hace 2 días',
      isRead: true,
      route: { name: 'matricula' }
    },
    {
      id: 6,
      icon: ArchiveBoxXMarkIcon,
      text: 'El convenio con "Empresa XYZ" ha sido marcado como inactivo.',
      time: 'hace 1 semana',
      isRead: true,
      route: { name: 'convenio' }
    },
     {
      id: 7,
      icon: CheckCircleIcon,
      text: 'El periodo "2024-II" ha finalizado.',
      time: 'hace 2 semanas',
      isRead: true,
      route: { name: 'periodo' }
    },
  ];
});
</script>

<template>
  <div class="p-6 space-y-4">
    <div class="flex items-baseline justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Todas las Notificaciones
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Revisa el historial completo de la actividad de tu cuenta.
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md">
      <div class="p-5 flex flex-wrap items-center justify-between gap-4">
          <h2 class="text-lg font-semibold text-gray-700 dark:text-white">Historial de Actividad</h2>
          <div class="flex items-center gap-2">
              <Button @click="markAllAsRead" title="Marcar todo como leído" class="!text-sm !py-2"/>
              <Button @click="clearRead" title="Eliminar leídas" class="!text-sm !py-2 !bg-red-600 hover:!bg-red-700 dark:!bg-red-700 dark:hover:!bg-red-800"/>
          </div>
      </div>
      
      <hr class="border-t-2 border-cetpro dark:border-cetpro-light" />

      <div class="p-5 space-y-5">
        <div>
          <h3 class="text-md font-semibold text-gray-600 dark:text-gray-300 mb-3">Nuevas</h3>
          <div v-if="unreadNotifications.length > 0" class="space-y-3">
            <RouterLink v-for="notification in unreadNotifications" :key="notification.id" :to="notification.route" class="block p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
              <div class="flex items-center gap-4">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white dark:bg-slate-700 shadow-sm">
                  <component :is="notification.icon" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="flex-grow">
                  <p class="text-sm text-gray-800 dark:text-gray-100">{{ notification.text }}</p>
                </div>
                <p class="text-xs shrink-0 font-bold text-blue-600 dark:text-blue-400">
                  {{ notification.time }}
                </p>
              </div>
            </RouterLink>
          </div>
          <p v-else class="text-sm text-gray-500 dark:text-gray-400 p-4 bg-gray-50 dark:bg-slate-700/50 rounded-lg text-center">
            ¡Estás al día! No tienes notificaciones nuevas.
          </p>
        </div>

        <div>
          <h3 class="text-md font-semibold text-gray-600 dark:text-gray-300 mb-3">Anteriores</h3>
          <div v-if="readNotifications.length > 0" class="space-y-3">
            <RouterLink v-for="notification in readNotifications" :key="notification.id" :to="notification.route" class="block p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
              <div class="flex items-center gap-4">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-700">
                  <component :is="notification.icon" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                </div>
                <div class="flex-grow">
                  <p class="text-sm text-gray-600 dark:text-gray-300">{{ notification.text }}</p>
                </div>
                <p class="text-xs shrink-0 text-gray-400 dark:text-gray-500">
                  {{ notification.time }}
                </p>
              </div>
            </RouterLink>
          </div>
           <p v-else class="text-sm text-gray-500 dark:text-gray-400 p-4 bg-gray-50 dark:bg-slate-700/50 rounded-lg text-center">
            No hay notificaciones leídas en tu historial.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>