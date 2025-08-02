<script setup>
import { ref, onMounted, computed } from 'vue';
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';
import {
  UsersIcon,
  ArrowRightIcon,
  BuildingOffice2Icon,
  UserGroupIcon,
  AcademicCapIcon,
} from '@heroicons/vue/24/outline';

const breadcrumbStore = useBreadcrumbStore();

const activeGroupsTab = ref('recientes');
const activeActivityTab = ref('direccion');
const dateFrom = ref(new Date().toISOString().slice(0, 10));
const dateTo = ref(new Date().toISOString().slice(0, 10));

const allActivities = ref([]);
const gruposRecientes = ref([]);
const gruposCulminados = ref([]);

const currentActivities = computed(() => {
  return allActivities.value.filter(act => act.role === activeActivityTab.value);
});

const currentGrupos = computed(() => {
  return activeGroupsTab.value === 'recientes' ? gruposRecientes.value : gruposCulminados.value;
});

const setDateRange = (days) => {
  const today = new Date();
  const from = new Date();
  from.setDate(today.getDate() - days);
  dateFrom.value = from.toISOString().slice(0, 10);
  dateTo.value = today.toISOString().slice(0, 10);
  applyDateFilter();
};

const applyDateFilter = () => {
  console.log(`Buscando actividades desde ${dateFrom.value} hasta ${dateTo.value}`);
};

onMounted(() => {
  breadcrumbStore.setBase([
    { text: 'Programa de Estudio', to: { name: 'programa.estudio' } },
    { text: 'Grupos' }
  ]);

  allActivities.value = [
    { role: 'direccion', actor: 'DIRECTORA | Mónica Calderón', accion: 'Agregado', accionColor: 'text-blue-500', detalle: 'Nuevo Convenio con "Empresa XYZ"', tiempo: 'hace 10 minutos' },
    { role: 'direccion', actor: 'DIRECTORA | Mónica Calderón', accion: 'Aprobado', detalle: 'Programa de estudios "Gastronomía 2026"', tiempo: 'hace 2 horas' },
    { role: 'coordinacion', actor: 'COORDINADOR | Jose Tapa Coaquira', accion: 'Agregado', detalle: 'Matricula - Victor Raul Valdez Huancaum - cod: 25000011', tiempo: 'hace 2 minutos' },
    { role: 'coordinacion', actor: 'COORDINADOR | Jose Tapa Coaquira', accion: 'Eliminado', accionColor: 'text-red-500', detalle: 'Asignación de docente al grupo 1243', tiempo: 'hace 1 hora' },
    { role: 'docente', actor: 'DOCENTE | Harol Flores', accion: 'Calificado', accionColor: 'text-purple-500', detalle: 'Módulo "Técnicas de Corte" - Grupo 1203', tiempo: 'hace 5 horas' },
    { role: 'docente', actor: 'DOCENTE | Juan Perez', accion: 'Registrado', detalle: 'Asistencia para la sesión de hoy', tiempo: 'hace 6 horas' },
  ];

  gruposRecientes.value = [
      { matriculados: 0, nro: 1243, modulo: 8, descripcion: 'MAQUILLAJE', seccion: 'UNICA', turno: 'TARDE', nominaRoute: '#' },
      { matriculados: 4, nro: 1243, modulo: 7, descripcion: 'PELUQUERIA', seccion: 'UNICA', turno: 'TARDE', nominaRoute: '#' },
  ];
});

</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8 space-y-6 font-sans bg-gray-50 dark:bg-slate-900">

    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-md flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-2 flex-wrap">
        <button class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-slate-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
          <img src="/img/matricula.png" class="h-6 w-6" alt="Matrículas">
          <span>MATRICULAS</span>
        </button>
        <button class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-slate-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
          <img src="/img/grupo.png" class="h-6 w-6" alt="Grupos">
          <span>GRUPOS</span>
        </button>
        <button class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-cetpro dark:text-cetpro-light bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
          <img src="/img/ciclo.png" class="h-6 w-6" alt="Nuevo Ciclo">
          <span>NUEVO CICLO</span>
        </button>
        <!-- BOTÓN DE BUSCAR ESTUDIANTE RESTAURADO -->
        <button class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-slate-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
          <img src="/img/busqueda-estudiante.png" class="h-6 w-6" alt="Buscar Estudiante">
          <span>BUSCAR ESTUDIANTE</span>
        </button>
      </div>

      <div class="flex flex-wrap items-center justify-end gap-2">
          <span class="text-sm font-semibold text-gray-600 dark:text-gray-300 mr-2">Filtrar Actividad:</span>
          <button @click="setDateRange(0)" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-slate-700 rounded-md hover:bg-gray-200 dark:hover:bg-slate-600">Hoy</button>
          <button @click="setDateRange(7)" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-slate-700 rounded-md hover:bg-gray-200 dark:hover:bg-slate-600">Últimos 7 días</button>
          <div class="relative">
              <label for="dateFrom" class="absolute -top-2 left-2 text-xs text-gray-500 bg-white dark:bg-slate-800 px-1">Desde</label>
              <input id="dateFrom" type="date" v-model="dateFrom" class="pl-2 pr-2 py-1.5 border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-md text-sm focus:ring-cetpro focus:border-cetpro">
          </div>
          <div class="relative">
              <label for="dateTo" class="absolute -top-2 left-2 text-xs text-gray-500 bg-white dark:bg-slate-800 px-1">Hasta</label>
              <input id="dateTo" type="date" v-model="dateTo" class="pl-2 pr-2 py-1.5 border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-md text-sm focus:ring-cetpro focus:border-cetpro">
          </div>
          <button @click="applyDateFilter" class="px-4 py-2 bg-cetpro text-white font-semibold rounded-lg text-sm hover:bg-opacity-90 transition-colors">Buscar</button>
      </div>
    </div>
    
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-6">
      <div class="flex border-b border-gray-200 dark:border-slate-700 mb-4">
        <button @click="activeActivityTab = 'direccion'" :class="['flex items-center gap-2 px-4 py-2 text-sm font-semibold transition-colors', activeActivityTab === 'direccion' ? 'border-b-2 border-cetpro text-cetpro' : 'text-gray-500 hover:text-gray-700']">
          <BuildingOffice2Icon class="h-5 w-5" /> <span>Dirección</span>
        </button>
        <button @click="activeActivityTab = 'coordinacion'" :class="['flex items-center gap-2 px-4 py-2 text-sm font-semibold transition-colors', activeActivityTab === 'coordinacion' ? 'border-b-2 border-cetpro text-cetpro' : 'text-gray-500 hover:text-gray-700']">
          <UserGroupIcon class="h-5 w-5" /> <span>Coordinación</span>
        </button>
        <button @click="activeActivityTab = 'docente'" :class="['flex items-center gap-2 px-4 py-2 text-sm font-semibold transition-colors', activeActivityTab === 'docente' ? 'border-b-2 border-cetpro text-cetpro' : 'text-gray-500 hover:text-gray-700']">
          <AcademicCapIcon class="h-5 w-5" /> <span>Docentes</span>
        </button>
      </div>
      <div class="space-y-4">
        <div v-if="currentActivities.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
          No hay actividad reciente para este rol en el rango de fechas seleccionado.
        </div>
        <div v-else v-for="(actividad, index) in currentActivities" :key="index" class="flex gap-4">
          <div class="flex flex-col items-center">
            <!-- ICONO DE ACTIVIDAD RECIENTE CORREGIDO -->
            <div class="bg-gray-100 dark:bg-slate-700 rounded-full p-2">
              <img src="/img/transmision.png" class="h-5 w-5" alt="Actividad">
            </div>
            <div v-if="index < currentActivities.length - 1" class="w-px flex-grow bg-gray-300 dark:bg-slate-600"></div>
          </div>
          <div class="flex-grow pb-4 flex justify-between items-start">
            <div>
              <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ actividad.actor }} - <span class="font-bold" :class="actividad.accionColor || 'text-green-500'">{{ actividad.accion }}</span>
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ actividad.detalle }}</p>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 shrink-0 ml-4">{{ actividad.tiempo }}</p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-6">
      <div class="flex border-b border-gray-200 dark:border-slate-700 mb-4">
        <button @click="activeGroupsTab = 'recientes'" :class="['flex items-center gap-2 px-4 py-2 text-sm font-semibold transition-colors', activeGroupsTab === 'recientes' ? 'border-b-2 border-cetpro text-cetpro' : 'text-gray-500 hover:text-gray-700']">
          <UsersIcon class="h-5 w-5" />
          <span>Grupos Recientes</span>
        </button>
        <button @click="activeGroupsTab = 'culminados'" :class="['flex items-center gap-2 px-4 py-2 text-sm font-semibold transition-colors', activeGroupsTab === 'culminados' ? 'border-b-2 border-cetpro text-cetpro' : 'text-gray-500 hover:text-gray-700']">
          <UsersIcon class="h-5 w-5" />
          <span>Grupos Culminados</span>
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
          <thead class="bg-gray-50 dark:bg-slate-700/50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nro Matriculados</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nro de Grupo</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nro de Modulo</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descripcion</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Seccion</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Turno</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Opciones</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
            <tr v-for="(grupo, index) in currentGrupos" :key="index" class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="grupo.matriculados > 0 ? 'text-green-600' : 'text-gray-900 dark:text-gray-200'">{{ grupo.matriculados }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ grupo.nro }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ grupo.modulo }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ grupo.descripcion }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ grupo.seccion }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ grupo.turno }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <router-link :to="grupo.nominaRoute" class="flex items-center justify-center gap-1 text-white bg-cetpro hover:bg-opacity-90 rounded-md px-3 py-1.5">
                  <span>Nomina</span>
                  <ArrowRightIcon class="h-4 w-4" />
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>