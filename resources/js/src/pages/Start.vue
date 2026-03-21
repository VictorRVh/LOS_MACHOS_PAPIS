<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';
import { UsersIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import useActividadesStore from '../store/ActividadesRecientes/UseActividadesRecientesStore';
import PermissionBlock from '../components/page/AuthorizationStart.vue'
import useGrupoStore from '../store/Grupo/useGrupoStore';
import useUserStore from '../store/useUserStore.js'
import useHttpRequest from '../composables/useHttpRequest';
// IMPORTAR GENERADOR CENSO
import { generateCenso9B } from '../pdf/Censo9B.js';
import useNotificacionesStore from '../store/Notificaciones/UseNotificacionesStore.js';


const { indexWithParams: getCensoData } = useHttpRequest('/censo9b-data');
const { index: getCensoAnios } = useHttpRequest('/censo9b-anios');

/* -------------------- TABS -------------------- */
const activeGroupsTab = ref('recientes');
const activeActivityTab = ref(null);

/* -------------------- FECHAS -------------------- */
const dateFrom = ref(new Date().toISOString().slice(0, 10));
const dateTo = ref(new Date().toISOString().slice(0, 10));
const censoAniosDisponibles = ref([]);
const censoAnioSeleccionado = ref(null);

/* -------------------- ACTIVIDADES -------------------- */
const actividadesStore = useActividadesStore();
const grupoStore = useGrupoStore();
const userStore = useUserStore();
const notificacionesStore = useNotificacionesStore();

const authUser = computed(() => userStore.user);

const allActivities = ref([]);

const canVerActividades = computed(() =>
  userStore.canVerActividadesRecientes  // computed reactivo directo
)

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
  isFiltering.value = true; // activar bandera
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
  if (!canVerActividades.value) return
  if (interval) return

  interval = setInterval(async () => {
    if (!canVerActividades.value) {
      stopAutoUpdate()
      return
    }

    if (document.visibilityState === 'visible') {
      await actividadesStore.loadActividadesRecientes()
      allActivities.value = actividadesStore.actividadesRecientes
    }
  }, 10000)
}

const stopAutoUpdate = () => {
  clearInterval(interval);
  interval = null;
};

const resetInactivityTimer = () => {
  clearTimeout(inactivityTimer)

  inactivityTimer = setTimeout(() => {
    stopAutoUpdate()
  }, INACTIVITY_LIMIT)

  if (
    !interval &&
    document.visibilityState === 'visible' &&
    !isFiltering.value &&
    canVerActividades.value
  ) {
    startAutoUpdate()
  }
}

watch(
  canVerActividades,
  async (allowed) => {
    if (!allowed) {
      stopAutoUpdate()
      return
    }

    await notificacionesStore.loadNotificaciones()
    await actividadesStore.loadActividadesRecientes()
    allActivities.value = actividadesStore.actividadesRecientes

    await grupoStore.loadGruposRecientes()
    gruposRecientes.value = grupoStore.gruposRecientes

    await grupoStore.loadGruposCulminados()
    gruposCulminados.value = grupoStore.gruposCulminados

    await loadCensoAnios()

     if (rolesTabs.value.length > 0) {
      activeActivityTab.value = rolesTabs.value[0]
    }

    startAutoUpdate()
    resetInactivityTimer()
  },
  { immediate: true }
)

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
const handleCensoClick = async () => {
  const selectedYear =
    Number(censoAnioSeleccionado.value) ||
    Number((dateFrom.value || '').slice(0, 4)) ||
    new Date().getFullYear();
  const data = await getCensoData({
    anio: selectedYear
  });

  generateCenso9B(data);
};

const loadCensoAnios = async () => {
  const response = await getCensoAnios();
  const anios = Array.isArray(response?.anios_disponibles) ? response.anios_disponibles : [];
  censoAniosDisponibles.value = anios;

  if (anios.length > 0) {
    censoAnioSeleccionado.value = Number(anios[0]);
    return;
  }

  censoAnioSeleccionado.value = Number(new Date().getFullYear());
};

/* -------------------- LIFECYCLE -------------------- */
onMounted(async () => {

  // if (can('ver-actividades-recientes')) {

  //   await notificacionesStore.loadNotificaciones();

  //   await actividadesStore.loadActividadesRecientes();
  //   allActivities.value = actividadesStore.actividadesRecientes;

  //   await grupoStore.loadGruposRecientes();
  //   gruposRecientes.value = grupoStore.gruposRecientes;

  //   await grupoStore.loadGruposCulminados();
  //   gruposCulminados.value = grupoStore.gruposCulminados;

  //   await loadCensoAnios();
  // }

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

    <div class="space-y-3 bg-slate-100 p-2.5 font-sans sm:p-3 lg:p-3">

      <section class="border border-slate-200 bg-white p-3 shadow-sm">
        <div class="flex flex-col gap-2.5">
          <div class="space-y-1.5">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">Accesos rápidos</p>
              <h1 class="mt-1 text-[1.3rem] font-semibold tracking-tight text-slate-900">Panel principal</h1>
            </div>

            <div class="flex flex-wrap gap-2">
          <button
            @click="$router.push({ name: 'matricula.index' })"
            class="inline-flex items-center gap-2.5 border border-slate-200 bg-slate-50 px-3.5 py-2 text-[13px] font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
            <img src="/img/matricula.png" class="h-5 w-5" alt="Matrículas">
            <span>MATRICULAS</span>
          </button>
          <button
            @click="$router.push({ name: 'grupo' })"
            class="inline-flex items-center gap-2.5 border border-slate-200 bg-slate-50 px-3.5 py-2 text-[13px] font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
            <img src="/img/grupo.png" class="h-5 w-5" alt="Grupos">
            <span>GRUPOS</span>
          </button>
          <button
            @click="$router.push({ name: 'programa' })"
            class="inline-flex items-center gap-2.5 border border-slate-200 bg-slate-50 px-3.5 py-2 text-[13px] font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
            <img src="/img/ciclo.png" class="h-5 w-5" alt="Nuevo Ciclo">
            <span>NUEVO CICLO</span>
          </button>

          <!-- BOTÓN BUSCAR ESTUDIANTE -->
          <button @click="$router.push({ name: 'buscar.estudiante' })"
            class="inline-flex items-center gap-2.5 border border-slate-200 bg-slate-50 px-3.5 py-2 text-[13px] font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
            <img src="/img/busqueda-estudiante.png" class="h-5 w-5" alt="Buscar Estudiante">
            <span>BUSCAR ESTUDIANTE</span>
          </button>

          <!-- NUEVO BOTÓN CENSO -->
          <div
            class="inline-flex items-center gap-2 border border-slate-200 bg-white px-3 py-1.5 text-sm">
            <span class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">Año
              censo</span>
            <select v-model="censoAnioSeleccionado"
              class="border border-slate-300 bg-white px-2 py-1 text-[13px] text-slate-700 focus:border-cetpro focus:ring-cetpro">
              <option v-for="anio in censoAniosDisponibles" :key="anio" :value="Number(anio)">
                {{ anio }}
              </option>
            </select>
          </div>

          <button @click="handleCensoClick"
            class="inline-flex items-center gap-2.5 border border-slate-200 bg-slate-50 px-3.5 py-2 text-[13px] font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
            <!-- Icono simulado (Documento) -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor" class="h-5 w-5 text-purple-600">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
            <span>CENSO</span>
          </button>
            </div>
        </div>

        </div>
      </section>

      <section class="border border-slate-200 bg-white p-3 shadow-sm">
        <div class="mb-2.5 border-b border-slate-200 pb-2.5">
          <div class="flex flex-col gap-2.5 xl:flex-row xl:items-end xl:justify-between">
            <div class="min-w-0">
              <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">Actividad reciente</p>
              <h2 class="mt-1 text-[1.2rem] font-semibold tracking-tight text-slate-900">Seguimiento por rol</h2>
            </div>

            <div class="flex w-full flex-wrap items-end gap-2 xl:w-auto xl:justify-end">
                <button @click="setDateRange(0)"
                  class="border border-slate-200 bg-white px-3 py-1.5 text-[13px] font-medium text-slate-700 transition hover:bg-slate-100">Hoy</button>
                <button @click="setDateRange(7)"
                  class="border border-slate-200 bg-white px-3 py-1.5 text-[13px] font-medium text-slate-700 transition hover:bg-slate-100">Últimos
                  7 días</button>
                <div class="relative min-w-[160px] flex-1 xl:flex-none">
                  <label for="dateFrom"
                    class="absolute -top-2 left-2 bg-white px-1 text-[11px] font-medium text-slate-500">Desde</label>
                  <input id="dateFrom" type="date" v-model="dateFrom"
                    class="w-full border border-slate-300 bg-white px-3 py-1.5 text-[13px] text-slate-700 focus:border-cetpro focus:ring-cetpro">
                </div>
                <div class="relative min-w-[160px] flex-1 xl:flex-none">
                  <label for="dateTo"
                    class="absolute -top-2 left-2 bg-white px-1 text-[11px] font-medium text-slate-500">Hasta</label>
                  <input id="dateTo" type="date" v-model="dateTo"
                    class="w-full border border-slate-300 bg-white px-3 py-1.5 text-[13px] text-slate-700 focus:border-cetpro focus:ring-cetpro">
                </div>
                <button @click="applyDateFilter"
                  class="border border-cetpro bg-cetpro px-4 py-1.5 text-[13px] font-semibold text-white transition hover:bg-cetpro-dark">Buscar</button>
            </div>
          </div>
        </div>

        <div class="mb-2.5 flex flex-wrap gap-2 border-b border-slate-200 pb-2.5">
          <div v-for="rol in rolesTabs" :key="rol" @click="activeActivityTab = rol" class="cursor-pointer border px-3 py-1.5 text-[13px] font-semibold transition"
            :class="activeActivityTab === rol ? 'border-cetpro/25 bg-cetpro/[0.08] text-cetpro' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900'">
            {{ rol }}
          </div>
        </div>

        <!-- Contenedor con scroll -->
        <div class="max-h-52 overflow-y-auto pr-1">
          <div v-if="currentActivities.length === 0" class="border border-dashed border-slate-200 bg-slate-50 py-8 text-center text-slate-500">
            No hay actividad reciente para este rol en el rango de fechas seleccionado.
          </div>
          <div v-else v-for="(actividad, index) in currentActivities" :key="index" class="flex gap-3 px-1 py-1">
            <div class="flex flex-col items-center">
              <div class="rounded-full bg-slate-100 p-2">
                <img src="/img/transmision.png" class="h-5 w-5" alt="Actividad">
              </div>
              <div v-if="index < currentActivities.length - 1" class="w-px flex-grow bg-slate-200">
              </div>
            </div>
            <div class="flex flex-grow items-start justify-between border-b border-slate-100 pb-4">
              <div>
                <p class="text-[13px] font-semibold text-slate-700">
                  {{ actividad.actor }} - <span class="font-bold" :class="actividad.accionColor || 'text-green-500'">{{
                    actividad.accion }}</span>
                </p>
                <p class="text-[13px] text-slate-500">{{ actividad.detalle }}</p>
              </div>
              <p class="ml-4 shrink-0 text-xs text-slate-400">{{ actividad.tiempo }}</p>
            </div>
          </div>
        </div>
      </section>

      <section class="border border-slate-200 bg-white p-3 shadow-sm">
        <div class="mb-2.5 flex flex-col gap-3 border-b border-slate-200 pb-2.5 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">Grupos</p>
            <h2 class="mt-1 text-[1.2rem] font-semibold tracking-tight text-slate-900">Estado de grupos</h2>
          </div>

          <div class="flex flex-wrap gap-2">
          <button @click="activeGroupsTab = 'recientes'"
            :class="['inline-flex items-center gap-2 border px-3 py-1.5 text-[13px] font-semibold transition-colors', activeGroupsTab === 'recientes' ? 'border-cetpro/25 bg-cetpro/[0.08] text-cetpro' : 'border-slate-200 text-slate-500 hover:border-slate-300 hover:text-slate-700']">
            <UsersIcon class="h-5 w-5" />
            <span>Grupos Recientes</span>
          </button>
          <button @click="activeGroupsTab = 'culminados'"
            :class="['inline-flex items-center gap-2 border px-3 py-1.5 text-[13px] font-semibold transition-colors', activeGroupsTab === 'culminados' ? 'border-cetpro/25 bg-cetpro/[0.08] text-cetpro' : 'border-slate-200 text-slate-500 hover:border-slate-300 hover:text-slate-700']">
            <UsersIcon class="h-5 w-5" />
            <span>Grupos Culminados</span>
          </button>
          </div>
        </div>
        <div class="overflow-x-auto border border-slate-200">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th scope="col"
                  class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">
                  Especialidad</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">
                  Nro de Módulo</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">
                  Modulo</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">
                  Seccion</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">
                  Turno</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">
                  Nro Matriculados</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">
                  Opciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-for="(grupo, index) in currentGrupos" :key="index"
                class="transition hover:bg-slate-50">

                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{
                  grupo.nombre_especialidad }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ grupo.numero_modulo
                }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ grupo.nombre_modulo
                }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ grupo.seccion }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ grupo.turno }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                  :class="grupo.nro_matriculados > 0 ? 'text-green-600' : 'text-slate-900'">{{
                    grupo.nro_matriculados }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <router-link v-if="grupo.nominaRoute" :to="grupo.nominaRoute"
                    class="inline-flex items-center justify-center gap-1 border border-cetpro bg-cetpro px-3 py-2 text-white transition hover:bg-cetpro-dark">
                    <span>Nomina</span>
                    <ArrowRightIcon class="h-4 w-4" />
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </PermissionBlock>
</template>
