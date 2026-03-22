<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import SearchBar from "../../components/head_table/headSearch.vue";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Th from "../../components/table/Th.vue";
import MenuTable from "../../components/table/MenuTable.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import StatsOverviewSection from "../../components/page/StatsOverviewSection.vue";
import BaseSelectGrupo from "../../components/ui/BaseSelectGrupo.vue";
import useModalToast from "../../composables/useModalToast";
import useTableData from "../../composables/tabla/useTableData";
import useEspecialidadStore from "../../store/Especialidad/useEspecialidadStore";
import usePeriodosStore from "../../store/Periodo/usePeriodoStatusStore";
import axios from "axios";

const especialidadStore = useEspecialidadStore();
const periodoStore = usePeriodosStore();
const router = useRouter();
const { showToast } = useModalToast();

const anios = ref([]);
const periodos = ref([]);
const anioSeleccionado = ref(null);
const periodoSeleccionado = ref(null);
const especialidades = ref([]);
const isLoading = ref(false);

onMounted(async () => {
  await periodoStore.loadPeriodosAnios();
  anios.value = periodoStore.periodosAnios;
});

const onAnioChange = async () => {
  periodoSeleccionado.value = null;
  periodos.value = [];

  if (anioSeleccionado.value) {
    await periodoStore.loadPeriodosAniosFiltrado(anioSeleccionado.value.anio);
    periodos.value = periodoStore.periodosAniosFiltrado;
  } else {
    periodoStore.periodosAniosFiltrado = [];
  }
};

const filtrarPorSeleccion = async () => {
  if (!anioSeleccionado.value || !periodoSeleccionado.value) {
    showToast("Debes seleccionar todos los filtros para buscar.", "warning");
    return;
  }

  isLoading.value = true;
  await especialidadStore.loadEspecialidadesPorPeriodo(periodoSeleccionado.value);
  especialidades.value = especialidadStore.especialidadesFiltradas || [];
  isLoading.value = false;
};

const verEgresados = (especialidad) => {
  router.push({
    name: "egresadosLista",
    params: {
      id: especialidad.id_especialidad,
      periodoId: periodoSeleccionado.value,
    },
  });
};

const generarReporte = async () => {
  try {
    const response = await axios.get("/reporte-censo", {
      responseType: "blob",
    });

    const contentType = response.headers["content-type"];
    if (contentType.includes("application/json")) {
      const text = await response.data.text();
      const error = JSON.parse(text);
      showToast(error.error || "Error al generar el reporte", "error");
      return;
    }

    let filename = "censo_educativo.xlsx";
    const disposition = response.headers["content-disposition"];

    if (disposition && disposition.includes("filename=")) {
      filename = disposition.split("filename=")[1].replace(/"/g, "");
    }

    const blob = new Blob([response.data], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });

    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;

    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error("Error:", error);
    showToast("Ocurrió un error al generar el reporte.", "error");
  }
};

const {
  ordenados: especialidadesOrdenadas,
  filtrar: filtrarEspecialidades,
} = useTableData(
  computed(() =>
    especialidades.value.map((especialidad) => ({
      id_especialidad: especialidad.id_especialidad,
      nombre_especialidad: especialidad.nombre_especialidad,
    }))
  ),
  {
    defaultOrderBy: "nombre_especialidad",
    searchFields: ["nombre_especialidad"],
  }
);

const totalEspecialidades = computed(() => especialidades.value.length);
const totalAnios = computed(() => anios.value.length);
const totalPeriodos = computed(() => periodos.value.length);
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-grupos', 'ver-grupos']">
    <div class="space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <StatsOverviewSection eyebrow="Gestion institucional" title="Egresados">
          <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Especialidades</p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalEspecialidades }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Filtradas</span>
              </div>
            </div>
            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Años disponibles</p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalAnios }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Histórico</span>
              </div>
            </div>
            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Periodos</p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalPeriodos }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Del año</span>
              </div>
            </div>
            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Vista actual</p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ especialidadesOrdenadas.length }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Resultado</span>
              </div>
            </div>
          </div>
      </StatsOverviewSection>

      <section class="border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-7 md:items-end">
          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-300">Año</label>
            <BaseSelectGrupo v-model="anioSeleccionado" :options="anios" label="anio" placeholder="Seleccione un año" @change="onAnioChange" />
          </div>
          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-300">Periodo</label>
            <BaseSelectGrupo v-model="periodoSeleccionado" :options="periodos" label="nombre_periodo" placeholder="Seleccione un periodo" :disabled="!anioSeleccionado" :loading="periodoStore.periodosAniosFiltradoLoading" />
          </div>
          <div class="md:col-span-1 flex items-end">
            <button @click="filtrarPorSeleccion" class="flex h-10 w-full items-center justify-center bg-cetpro px-4 py-2 font-semibold text-white transition-colors duration-300 hover:bg-cetpro-dark">
              Filtrar
            </button>
          </div>
        </div>
      </section>

      <section class="border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900">
        <div class="mb-3 flex justify-between gap-3">
          <div>
            <h3 class="mt-1 text-[15px] font-medium text-slate-900 dark:text-slate-100">Lista de especialidades</h3>
          </div>
          <SearchBar v-if="!isLoading && especialidades.length > 0" :totalResultados="especialidades.length" :campoOrden="'nombre_especialidad'" @search="filtrarEspecialidades" />
        </div>

        <div class="overflow-auto border border-gray-200 dark:border-gray-700">
          <div v-if="isLoading" class="space-y-2 p-4">
            <div v-for="i in 5" :key="i" class="h-12 animate-pulse bg-gray-200 dark:bg-gray-700"></div>
          </div>

          <Table v-else-if="especialidades.length > 0" :paginacion="false" class="mt-2 w-full border-collapse">
            <THead>
              <Th class="w-[40px] border-b border-gray-300 text-center dark:border-gray-600">N°</Th>
              <Th class="min-w-[250px] border-b border-gray-300 dark:border-gray-600">Especialidad</Th>
              <Th class="w-[100px] border-b border-gray-300 text-center dark:border-gray-600">Acciones</Th>
            </THead>
            <TBody :filas="especialidades.length">
              <tr v-for="(esp, index) in especialidades" :key="esp.id" class="border-b border-gray-300 dark:border-gray-700">
                <td class="py-3 text-center">{{ index + 1 }}</td>
                <td class="py-3 font-semibold">{{ esp.nombre_especialidad }}</td>
                <td class="py-3 text-center">
                  <MenuTable :actions="{ view: true }" @view="verEgresados(esp)" entity-label="egresados" />
                </td>
              </tr>
            </TBody>
          </Table>

          <div v-else class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">No se encontraron especialidades</h3>
            <p class="mt-1 text-sm text-gray-500">Intenta con otro periodo.</p>
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
