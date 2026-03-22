<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { ChevronDownIcon } from "@heroicons/vue/24/outline";

import SearchBar from "../../components/head_table/headSearch.vue";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Th from "../../components/table/Th.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import StatsOverviewSection from "../../components/page/StatsOverviewSection.vue";
import BaseSelectGrupo from "../../components/ui/BaseSelectGrupo.vue";

import useGrupoStore from "../../store/Grupo/useGrupoStore";
import useIngresoStore from "../../store/Ingreso/useIngresoStore";
import useCicloStore from "../../store/Ciclo/useCicloStore";
import useTableData from "../../composables/tabla/useTableData";

const router = useRouter();
const grupoStore = useGrupoStore();
const cicloStore = useCicloStore();
const ingresos = useIngresoStore();

const grupos = ref([]);
const isLoading = ref(false);

const selectedCiclo = ref(null);
const selectedAnio = ref(null);
const selectedPeriodo = ref(null);

const openEspecialidades = ref(new Set());

const parsearIngresos = (data) => {
  const planos = [];

  data.forEach((esp) => {
    Object.values(esp.modulos || {}).forEach((mod) => {
      (mod.grupos || []).forEach((g) => {
        planos.push({
          ...g,
          especialidad: esp.especialidad,
        });
      });
    });
  });

  return planos;
};

const totalIngresosPorEspecialidad = computed(() => {
  const totales = {};

  grupos.value.forEach((g) => {
    if (!totales[g.especialidad]) totales[g.especialidad] = 0;
    totales[g.especialidad] += Number(g.ingreso_grupo || 0);
  });

  return totales;
});

onMounted(async () => {
  if (!cicloStore.ciclo.length) await cicloStore.loadCiclo();
});

const onCicloChange = async () => {
  selectedAnio.value = null;
  selectedPeriodo.value = null;

  if (selectedCiclo.value) await grupoStore.loadAnios(selectedCiclo.value);
  else grupoStore.anios = [];
};

const onAnioChange = async () => {
  selectedPeriodo.value = null;

  if (selectedAnio.value) await grupoStore.loadPeriodoAnio(selectedAnio.value);
  else grupoStore.periodoAnio = [];
};

const filtrarPorSeleccion = async () => {
  if (!selectedCiclo.value || !selectedAnio.value || !selectedPeriodo.value) return;

  isLoading.value = true;

  await ingresos.loadIngresos(selectedPeriodo.value);

  grupos.value = parsearIngresos(ingresos.ingresos || []);
  openEspecialidades.value = new Set(grupos.value.map((g) => g.especialidad));

  pagina.value = 1;
  isLoading.value = false;
};

const toggleEspecialidad = (nombre) => {
  const temp = new Set(openEspecialidades.value);
  temp.has(nombre) ? temp.delete(nombre) : temp.add(nombre);
  openEspecialidades.value = temp;
};

const gruposPlanos = computed(() => grupos.value);

const {
  query,
  orderBy,
  orderDirection,
  pagina,
  itemsPorPagina,
  paginados: gruposPaginados,
  totalPaginas,
  ordenados: gruposOrdenados,
  filtrar: filtrarGrupos,
} = useTableData(gruposPlanos, {
  defaultOrderBy: "modulo",
  searchFields: ["modulo", "seccion", "turno", "docente", "especialidad"],
});

const gruposAgrupados = computed(() => {
  const agrupados = {};

  gruposPaginados.value.forEach((grupo) => {
    if (!agrupados[grupo.especialidad]) agrupados[grupo.especialidad] = [];
    agrupados[grupo.especialidad].push(grupo);
  });

  return Object.entries(agrupados);
});

const totalGrupos = computed(() => gruposPlanos.value.length);
const totalEspecialidades = computed(() => new Set(gruposPlanos.value.map((grupo) => grupo.especialidad)).size);
const totalIngresos = computed(() =>
  gruposPlanos.value.reduce((acc, grupo) => acc + Number(grupo.ingreso_grupo || 0), 0)
);
const totalEstudiantes = computed(() =>
  gruposPlanos.value.reduce((acc, grupo) => acc + Number(grupo.cantidad_estudiantes || 0), 0)
);
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-grupos', 'ver-grupos']">
    <div class="space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <StatsOverviewSection eyebrow="Gestion institucional" title="Ingresos">
        <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
          <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
            <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Total grupos</p>
            <div class="mt-1 flex items-end justify-between gap-3">
              <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalGrupos }}</p>
              <span class="text-[10px] text-slate-500 dark:text-slate-400">Filtrados</span>
            </div>
          </div>
          <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
            <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Especialidades</p>
            <div class="mt-1 flex items-end justify-between gap-3">
              <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalEspecialidades }}</p>
              <span class="text-[10px] text-slate-500 dark:text-slate-400">Activas</span>
            </div>
          </div>
          <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
            <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Ingresos</p>
            <div class="mt-1 flex items-end justify-between gap-3">
              <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">S/. {{ totalIngresos }}</p>
              <span class="text-[10px] text-slate-500 dark:text-slate-400">Acumulados</span>
            </div>
          </div>
          <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
            <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Estudiantes</p>
            <div class="mt-1 flex items-end justify-between gap-3">
              <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalEstudiantes }}</p>
              <span class="text-[10px] text-slate-500 dark:text-slate-400">Asociados</span>
            </div>
          </div>
        </div>
      </StatsOverviewSection>

      <section class="border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_180px] md:items-end">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-300">Ciclo</label>
            <BaseSelectGrupo v-model="selectedCiclo" :options="cicloStore.ciclo" label="nombre_ciclo" placeholder="Seleccione un ciclo" @change="onCicloChange" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-300">Año</label>
            <BaseSelectGrupo v-model="selectedAnio" :options="grupoStore?.anios" label="label" placeholder="Seleccione un año" @change="onAnioChange" :disabled="!selectedCiclo" :loading="grupoStore.aniosByCicloLoading" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-300">Periodo</label>
            <BaseSelectGrupo v-model="selectedPeriodo" :options="grupoStore.periodoAnio" label="nombre_periodo" placeholder="Seleccione un periodo" :disabled="!selectedAnio" :loading="grupoStore.periodoByAnioLoading" />
          </div>
          <div class="flex items-end">
            <button @click="filtrarPorSeleccion" class="flex h-10 w-full items-center justify-center bg-cetpro px-4 py-2 font-semibold text-white transition-colors duration-300 hover:bg-cetpro-dark">
              Filtrar
            </button>
          </div>
        </div>
      </section>

      <section class="border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900">
        <div class="mb-3 flex justify-between gap-3">
          <div>
            <h3 class="mt-1 text-[15px] font-medium text-slate-900 dark:text-slate-100">Lista de grupos</h3>
          </div>
          <SearchBar v-if="!isLoading && gruposPlanos.length > 0" :totalResultados="gruposOrdenados.length" :campoOrden="'modulo'" @search="filtrarGrupos" />
        </div>

        <div class="overflow-auto border border-gray-200 dark:border-gray-700">
          <div v-if="isLoading" class="space-y-2 p-4">
            <div v-for="i in 5" :key="i" class="h-12 animate-pulse bg-gray-200 dark:bg-gray-700"></div>
          </div>

          <Table v-else-if="gruposPaginados.length > 0" :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event" class="mt-2 w-full border-collapse">
            <THead>
              <Th class="w-[40px] border-b border-gray-300 text-center dark:border-gray-600">N°</Th>
              <Th class="min-w-[250px] border-b border-gray-300 dark:border-gray-600">Modulo</Th>
              <Th class="w-[80px] border-b border-gray-300 text-center dark:border-gray-600">Sección</Th>
              <Th class="w-[80px] border-b border-gray-300 text-center dark:border-gray-600">Turno</Th>
              <Th class="min-w-[180px] border-b border-gray-300 dark:border-gray-600">Convenio</Th>
              <Th class="w-[80px] border-b border-gray-300 text-center dark:border-gray-600">N° Estudiantes</Th>
              <Th class="w-[180px] border-b border-gray-300 text-center dark:border-gray-600">Docente</Th>
              <Th class="w-[120px] border-b border-gray-300 text-center dark:border-gray-600">Ingresos</Th>
            </THead>
            <TBody>
              <template v-for="([nombreEspecialidad, modulos]) in gruposAgrupados" :key="nombreEspecialidad">
                <tr @click="toggleEspecialidad(nombreEspecialidad)" class="cursor-pointer bg-cetpro hover:bg-cetpro-dark/80 dark:bg-cetpro-dark">
                  <td colspan="8" class="px-4 py-2 text-sm font-bold uppercase tracking-wider text-cetpro-text">
                    <div class="flex items-center justify-between">
                      <span>{{ nombreEspecialidad }}</span>
                      <div class="flex items-center gap-3">
                        <div class="text-sm font-semibold text-white">
                          Total: S/. {{ totalIngresosPorEspecialidad[nombreEspecialidad] }}
                        </div>
                        <ChevronDownIcon :class="['h-6 w-6 transition-transform duration-300', { 'rotate-180': openEspecialidades.has(nombreEspecialidad) }]" />
                      </div>
                    </div>
                  </td>
                </tr>

                <tr v-for="(grupo, index) in modulos" :key="grupo.id" v-show="openEspecialidades.has(nombreEspecialidad)" class="border-b border-gray-300 dark:border-gray-700">
                  <td class="py-3 text-center">{{ index + 1 }}</td>
                  <td class="py-3">{{ grupo.modulo }}</td>
                  <td class="py-3 text-center">{{ grupo.seccion }}</td>
                  <td class="py-3 text-center">{{ grupo.turno }}</td>
                  <td class="py-3">{{ grupo.convenio_nombre }}</td>
                  <td class="py-3 text-center font-semibold text-green-700">{{ grupo.cantidad_estudiantes }}</td>
                  <td class="px-5 py-3 text-center">{{ grupo.docente || "---" }}</td>
                  <td class="py-3 text-center font-bold text-blue-600">S/. {{ grupo.ingreso_grupo }}</td>
                </tr>
              </template>
            </TBody>
          </Table>

          <div v-else class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">No se encontraron grupos</h3>
            <p class="mt-1 text-sm text-gray-500">Intenta con otros filtros o crea un nuevo grupo.</p>
          </div>
        </div>
      </section>
    </div>
  </AuthorizationFallback>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

.list-leave-active {
  position: absolute;
}
</style>
