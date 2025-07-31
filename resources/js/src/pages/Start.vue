<script setup>
import { ref, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';
import useUserStore from '@/store/useUserStore';
import {
  UsersIcon,
  AcademicCapIcon,
  BriefcaseIcon,
  ClipboardDocumentListIcon,
  UserPlusIcon,
  BookOpenIcon,
  IdentificationIcon,
  UserCircleIcon,
  DocumentPlusIcon,
} from '@heroicons/vue/24/outline';

const breadcrumbStore = useBreadcrumbStore();
const userStore = useUserStore();

const stats = ref({
  estudiantes: 0,
  docentes: 0,
  programas: 0,
  convenios: 0,
});

const accionesRapidas = [
  { text: 'Registrar Nuevo Usuario', icon: UserPlusIcon, routeName: 'users.crear' },
  { text: 'Crear Nuevo Programa', icon: BookOpenIcon, routeName: 'programa.crear' },
  { text: 'Realizar una Matrícula', icon: IdentificationIcon, routeName: 'matricula' },
];

const actividadReciente = ref([]);

onMounted(() => {
  breadcrumbStore.setBase([]);

  setTimeout(() => {
    stats.value = {
      estudiantes: 153,
      docentes: 24,
      programas: 12,
      convenios: 8,
    };
    actividadReciente.value = [
      { id: 1, icon: UserCircleIcon, text: 'El usuario Mónica Calderón ha iniciado sesión.', time: 'hace 2 minutos' },
      { id: 2, icon: DocumentPlusIcon, text: 'Se ha creado el programa de estudio "2026".', time: 'hace 1 hora' },
      { id: 3, icon: UserCircleIcon, text: 'Se ha asignado la especialidad "nones" al programa "2026".', time: 'hace 3 horas' },
      { id: 4, icon: UserCircleIcon, text: 'El usuario Harol Flores ha sido creado.', time: 'ayer' },
    ];
  }, 500);
});
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8 space-y-8 font-sans">
    <header>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        Dashboard 
      </h1>
      <p class="mt-1 text-lg text-gray-600 dark:text-gray-300">
        Bienvenido de nuevo, {{ userStore.user?.name || 'Director(a)' }}.
      </p>
    </header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-5 flex items-start justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estudiantes Activos</p>
          <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.estudiantes }}</span>
        </div>
        <div class="bg-blue-100 dark:bg-blue-900/40 rounded-full p-3">
          <AcademicCapIcon class="h-7 w-7 text-blue-600 dark:text-blue-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-5 flex items-start justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Docentes</p>
          <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.docentes }}</span>
        </div>
        <div class="bg-indigo-100 dark:bg-indigo-900/40 rounded-full p-3">
          <UsersIcon class="h-7 w-7 text-indigo-600 dark:text-indigo-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-5 flex items-start justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Programas</p>
          <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.programas }}</span>
        </div>
        <div class="bg-emerald-100 dark:bg-emerald-900/40 rounded-full p-3">
          <BriefcaseIcon class="h-7 w-7 text-emerald-600 dark:text-emerald-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-5 flex items-start justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Convenios</p>
          <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.convenios }}</span>
        </div>
        <div class="bg-amber-100 dark:bg-amber-900/40 rounded-full p-3">
          <ClipboardDocumentListIcon class="h-7 w-7 text-amber-600 dark:text-amber-400" />
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-1 bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Acciones Rápidas</h2>
        <div class="space-y-3">
          <RouterLink v-for="accion in accionesRapidas" :key="accion.text" :to="{ name: accion.routeName }"
            class="flex items-center p-4 bg-gray-50 dark:bg-slate-700/50 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors duration-200 group">
            <component :is="accion.icon" class="h-6 w-6 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400" />
            <span class="ml-4 font-semibold text-gray-700 dark:text-gray-200 group-hover:text-blue-800 dark:group-hover:text-blue-300">{{ accion.text }}</span>
          </RouterLink>
        </div>
      </div>

      <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Actividad Reciente</h2>
        <ul class="divide-y divide-gray-200 dark:divide-slate-700">
          <li v-for="actividad in actividadReciente" :key="actividad.id" class="py-4 flex items-center">
            <div class="bg-gray-100 dark:bg-slate-700 rounded-full p-2">
              <component :is="actividad.icon" class="h-5 w-5 text-gray-500 dark:text-gray-300" />
            </div>
            <div class="ml-4 flex-grow">
              <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ actividad.text }}</p>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ actividad.time }}</p>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>