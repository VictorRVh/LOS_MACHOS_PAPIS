<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';
import { UsersIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import useActividadesStore from '../store/ActividadesRecientes/UseActividadesRecientesStore';
import PermissionBlock from '../components/page/AuthorizationStart.vue'
import useGrupoStore from '../store/Grupo/useGrupoStore';

// IMPORTAR GENERADOR CENSO
import { generateCenso9B } from '../pdf/Censo9B.js';


/* -------------------- TABS -------------------- */
const activeGroupsTab = ref('recientes');
const activeActivityTab = ref(null);

/* -------------------- FECHAS -------------------- */
const dateFrom = ref(new Date().toISOString().slice(0, 10));
const dateTo = ref(new Date().toISOString().slice(0, 10));

/* -------------------- ACTIVIDADES -------------------- */
const actividadesStore = useActividadesStore();
const grupoStore = useGrupoStore();
const allActivities = ref([]);

/* -------------------- ROLES -------------------- */
const rolesTabs = computed(() => {
  const roles = allActivities.value.map(a => a.role);
  return [...new Set(roles)];
});

/* -------------------- ACTIVIDADES FILTRADAS -------------------- */
const currentActivities = computed(() => {
  if (!activeActivityTab.value) return [];
  return allActivities.value.filter(act => act.role === activeActivityTab.value);
});

/* -------------------- GRUPOS -------------------- */
const gruposRecientes = ref([]);
const gruposCulminados = ref([]);

const isFiltering = ref(false);

const currentGrupos = computed(() => {
  return activeGroupsTab.value === 'recientes'
    ? gruposRecientes.value
    : gruposCulminados.value;
});

/* -------------------- FILTRO DE FECHA -------------------- */
const setDateRange = (days) => {
  const today = new Date();
  const from = new Date();
  from.setDate(today.getDate() - days);

  dateFrom.value = from.toISOString().slice(0, 10);
  dateTo.value = today.toISOString().slice(0, 10);

  applyDateFilter();
};

const applyDateFilter = async () => {
  isFiltering.value = true; // <- 🔥 activar bandera
  stopAutoUpdate();         // detener auto-update

  await actividadesStore.loadActividadesPorFechas(dateFrom.value, dateTo.value);

  allActivities.value = actividadesStore.actividadesRecientesPorFecha;

  if (rolesTabs.value.length > 0) {
    activeActivityTab.value = rolesTabs.value[0];
  }
};

/* -------------------- AUTO-ACTUALIZACIÓN -------------------- */
let interval = null;
let inactivityTimer = null;
const INACTIVITY_LIMIT = 10 * 60 * 1000; // 10 min

const startAutoUpdate = () => {
  if (interval) return;
  interval = setInterval(async () => {
    if (document.visibilityState === 'visible') {
      await actividadesStore.loadActividadesRecientes();
      allActivities.value = actividadesStore.actividadesRecientes;
    }
  }, 10000);
};

const stopAutoUpdate = () => {
  clearInterval(interval);
  interval = null;
};

const resetInactivityTimer = () => {
  clearTimeout(inactivityTimer);
  inactivityTimer = setTimeout(() => {
    stopAutoUpdate(); // parar por inactividad
  }, INACTIVITY_LIMIT);

  if (!interval && document.visibilityState === 'visible' && !isFiltering.value) {
    startAutoUpdate();  // solo si NO se está filtrando
  }
};

/* -------------------- HANDLER VISIBILITY -------------------- */
const handleVisibilityChange = () => {
  if (document.visibilityState === 'visible') {
    if (!isFiltering.value) {
      resetInactivityTimer();
    }
  } else {
    stopAutoUpdate();
  }
};

/* -------------------- FUNCIONES CENSO -------------------- */
const handleCensoClick = () => {
    generateCenso9B();
}

/* -------------------- LIFECYCLE -------------------- */
onMounted(async () => {

  await actividadesStore.loadActividadesRecientes();
  allActivities.value = actividadesStore.actividadesRecientes;

  await grupoStore.loadGruposRecientes();
  gruposRecientes.value = grupoStore.gruposRecientes;

  await grupoStore.loadGruposCulminados();
  gruposCulminados.value = grupoStore.gruposCulminados;

  // activar primer rol automáticamente
  if (rolesTabs.value.length > 0) {
    activeActivityTab.value = rolesTabs.value[0];
  }

  // iniciar lógica
  startAutoUpdate();
  resetInactivityTimer();

  // listeners
  window.addEventListener('mousemove', resetInactivityTimer);
  window.addEventListener('keydown', resetInactivityTimer);
  document.addEventListener('visibilitychange', handleVisibilityChange);
});

onUnmounted(() => {
  stopAutoUpdate();
  clearTimeout(inactivityTimer);

  window.removeEventListener('mousemove', resetInactivityTimer);
  window.removeEventListener('keydown', resetInactivityTimer);
  document.removeEventListener('visibilitychange', handleVisibilityChange);
});
</script>



<template>
  <PermissionBlock :permissions="['ver-actividades']">

    <div class="p-4 sm:p-6 lg:p-8 space-y-2 font-sans bg-gray-50 dark:bg-slate-900">

      <div
        class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-md flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2 flex-wrap">
          <button
            class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-slate-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
            <img src="/img/matricula.png" class="h-6 w-6" alt="Matrículas">
            <span>MATRICULAS</span>
          </button>
          <button
            class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-slate-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
            <img src="/img/grupo.png" class="h-6 w-6" alt="Grupos">
            <span>GRUPOS</span>
          </button>
          <button
            class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-cetpro dark:text-cetpro-light bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
            <img src="/img/ciclo.png" class="h-6 w-6" alt="Nuevo Ciclo">
            <span>NUEVO CICLO</span>
          </button>
          
          <!-- BOTÓN BUSCAR ESTUDIANTE -->
          <button @click="$router.push({ name: 'buscar.estudiante' })"
            class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-slate-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
            <img src="/img/busqueda-estudiante.png" class="h-6 w-6" alt="Buscar Estudiante">
            <span>BUSCAR ESTUDIANTE</span>
          </button>

          <!-- NUEVO BOTÓN CENSO -->
          <button @click="handleCensoClick"
            class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-slate-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
            <!-- Icono simulado (Documento) -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-purple-600">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
            <span>CENSO</span>
          </button>

        </div>

        <div class="flex flex-wrap items-center justify-end gap-2">
          <span class="text-sm font-semibold text-gray-600 dark:text-gray-300 mr-2">Filtrar Actividad:</span>
          <button @click="setDateRange(0)"
            class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-slate-700 rounded-md hover:bg-gray-200 dark:hover:bg-slate-600">Hoy</button>
          <button @click="setDateRange(7)"
            class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-slate-700 rounded-md hover:bg-gray-200 dark:hover:bg-slate-600">Últimos
            7 días</button>
          <div class="relative">
            <label for="dateFrom"
              class="absolute -top-2 left-2 text-xs text-gray-500 bg-white dark:bg-slate-800 px-1">Desde</label>
            <input id="dateFrom" type="date" v-model="dateFrom"
              class="pl-2 pr-2 py-1.5 border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-md text-sm focus:ring-cetpro focus:border-cetpro">
          </div>
          <div class="relative">
            <label for="dateTo"
              class="absolute -top-2 left-2 text-xs text-gray-500 bg-white dark:bg-slate-800 px-1">Hasta</label>
            <input id="dateTo" type="date" v-model="dateTo"
              class="pl-2 pr-2 py-1.5 border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-md text-sm focus:ring-cetpro focus:border-cetpro">
          </div>
          <button @click="applyDateFilter"
            class="px-4 py-2 bg-cetpro text-white font-semibold rounded-lg text-sm hover:bg-opacity-90 transition-colors">Buscar</button>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-2">
        <!-- Tabs tipo submenu con pointer -->
        <div class="flex gap-2 border-b border-gray-200 dark:border-slate-700 mb-4">
          <div v-for="rol in rolesTabs" :key="rol" @click="activeActivityTab = rol" class="relative px-4 py-2 text-sm font-semibold cursor-pointer rounded-md transition
         text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
            :class="activeActivityTab === rol && 'text-cetpro'">
            {{ rol }}

            <!-- Pointer / triángulo -->
            <div v-if="activeActivityTab === rol" class="absolute left-1/2 -bottom-2 h-0 w-0 -translate-x-1/2
           border-l-8 border-r-8 border-t-8 border-l-transparent border-r-transparent
           border-t-cetpro"></div>
          </div>
        </div>

        <!-- Contenedor con scroll -->
        <div class="max-h-40 overflow-y-auto">
          <div v-if="currentActivities.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            No hay actividad reciente para este rol en el rango de fechas seleccionado.
          </div>
          <div v-else v-for="(actividad, index) in currentActivities" :key="index" class="flex gap-2">
            <div class="flex flex-col items-center">
              <div class="bg-gray-100 dark:bg-slate-700 rounded-full p-2">
                <img src="/img/transmision.png" class="h-5 w-5" alt="Actividad">
              </div>
              <div v-if="index < currentActivities.length - 1" class="w-px flex-grow bg-gray-300 dark:bg-slate-600">
              </div>
            </div>
            <div class="flex-grow pb-4 flex justify-between items-start">
              <div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                  {{ actividad.actor }} - <span class="font-bold" :class="actividad.accionColor || 'text-green-500'">{{
                    actividad.accion }}</span>
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
          <button @click="activeGroupsTab = 'recientes'"
            :class="['flex items-center gap-2 px-4 py-2 text-sm font-semibold transition-colors', activeGroupsTab === 'recientes' ? 'border-b-2 border-cetpro text-cetpro' : 'text-gray-500 hover:text-gray-700']">
            <UsersIcon class="h-5 w-5" />
            <span>Grupos Recientes</span>
          </button>
          <button @click="activeGroupsTab = 'culminados'"
            :class="['flex items-center gap-2 px-4 py-2 text-sm font-semibold transition-colors', activeGroupsTab === 'culminados' ? 'border-b-2 border-cetpro text-cetpro' : 'text-gray-500 hover:text-gray-700']">
            <UsersIcon class="h-5 w-5" />
            <span>Grupos Culminados</span>
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
            <thead class="bg-gray-50 dark:bg-slate-700/50">
              <tr>
                <th scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Especialidad</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Nro de Módulo</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Modulo</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Seccion</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Turno</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Nro Matriculados</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Opciones</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="(grupo, index) in currentGrupos" :key="index"
                class="hover:bg-gray-50 dark:hover:bg-slate-700/50">

                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{
                  grupo.nombre_especialidad }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ grupo.numero_modulo
                }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ grupo.nombre_modulo
                }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ grupo.seccion }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ grupo.turno }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                  :class="grupo.nro_matriculados > 0 ? 'text-green-600' : 'text-gray-900 dark:text-gray-200'">{{
                    grupo.nro_matriculados }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <router-link v-if="grupo.nominaRoute" :to="grupo.nominaRoute"
                    class="flex items-center justify-center gap-1 text-white bg-cetpro hover:bg-opacity-90 rounded-md px-3 py-1.5">
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
  </PermissionBlock>
</template>