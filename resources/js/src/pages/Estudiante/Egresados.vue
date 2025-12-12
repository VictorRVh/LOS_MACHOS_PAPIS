<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";

import { useBreadcrumbStore } from "@/store/useBreadcrumbStore";
import SearchBar from "../../components/head_table/headSearch.vue";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import MenuTable from "../../components/table/MenuTable.vue";
import CreateButton from "../../components/ui/CreateButton.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";

import GrupoSlider from "../../components/page/Grupo/GrupoSlider.vue";
import BaseSelectGrupo from "../../components/ui/BaseSelectGrupo.vue";

import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import useGrupoStore from "../../store/Grupo/useGrupoStore";
import useCicloStore from "../../store/Ciclo/useCicloStore";
import useTableData from "../../composables/tabla/useTableData";
import useEspecialidadStore from "../../store/Especialidad/useEspecialidadStore";
import useEstudianteStore from "../../store/Estudiante/UseEstudianteStore";
import usePeriodosStore from "../../store/Periodo/usePeriodoStatusStore";

const especialidadStore = useEspecialidadStore();
const estudianteStore = useEstudianteStore();
const periodoStore = usePeriodosStore();

const router = useRouter();

const { showConfirmModal, showToast } = useModalToast();

const anios = ref([]);
const periodos = ref([]);
const anioSeleccionado = ref(null);
const periodoSeleccionado = ref(null);
const especialidades = ref([]);

const especialidadSeleccionada = ref('');
const egresados = ref([]);

const pagina = ref(1);

const openEspecialidades = ref(new Set());
const isLoading = ref(false);

onMounted(async () => {

  await periodoStore.loadPeriodosAnios();
  anios.value = periodoStore.periodosAnios;

});

const onAnioChange = async () => {
  // 1. Limpiar valor seleccionado
  periodoSeleccionado.value = null;

  // 2. Limpiar lista de periodos
  periodos.value = [];

  if (anioSeleccionado.value) {
    await periodoStore.loadPeriodosAniosFiltrado(anioSeleccionado.value.anio);

    // 3. Asignar nuevos periodos
    periodos.value = periodoStore.periodosAniosFiltrado;
  } else {
    periodoStore.periodosAniosFiltrado = [];
  }
};

// ---- Filtrar por selección
const filtrarPorSeleccion = async () => {
  if (!anioSeleccionado.value || !periodoSeleccionado.value) {
    showToast("Debes seleccionar todos los filtros para buscar.", "warning");
    return;
  }

  isLoading.value = true;

  await especialidadStore.loadEspecialidadesPorPeriodo(periodoSeleccionado.value);

  especialidades.value = especialidadStore.especialidadesFiltradas || [];

  console.log('dejidje', periodoSeleccionado.value)

  // abrir todas las especialidades luego del filtro
  openEspecialidades.value = new Set(especialidadesPlanas.value.map(e => e.nombre_especialidad));

  pagina.value = 1;
  isLoading.value = false;
};

// ---- Mapeo plano de especialidades
const especialidadesPlanas = computed(() => {
  return especialidades.value.map((e) => ({
    id: e.id_especialidad,
    nombre_especialidad: e.nombre_especialidad,
    // cantidad_egresados: e.cantidad_egresados ?? 0,
    // codigo: e.codigo ?? "",
    // descripcion: e.descripcion ?? "",
  }));
});

// ---- Agrupación por especialidad
const especialidadesAgrupadas = computed(() => {
  const agrupados = {};

  especialidadesPaginadas.value.forEach((esp) => {
    if (!agrupados[esp.nombre_especialidad]) agrupados[esp.nombre_especialidad] = [];
    agrupados[esp.nombre_especialidad].push(esp);
  });

  return Object.entries(agrupados);
});

const verEgresados = (esp) => {
  router.push({
    name: "egresadosLista",
    params: {
      id: esp.id_especialidad,      
      periodoId: periodoSeleccionado.value
    }
  });
};


// ---- Configuración de tabla
const {
  query,
  orderBy,
  orderDirection,
  pagina: paginaTabla,
  itemsPorPagina,
  paginados: especialidadesPaginadas,
  totalPaginas,
  ordenados: especialidadesOrdenadas,
  filtrar: filtrarEspecialidades,
} = useTableData(especialidadesPlanas, {
  defaultOrderBy: "nombre_especialidad",
  searchFields: ["nombre_especialidad"],
});


</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-grupos', 'ver-grupos']">
    <div class="p-4 md:p-6 space-y-4">
      <h1 class="text-3xl font-bold mb-4">Egresados</h1>

      <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-end"> <!-- Ciclo -->
          <div class="md:col-span-2"> <label
              class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Año</label>
            <BaseSelectGrupo v-model="anioSeleccionado" :options="anios" label="anio" placeholder="Seleccione un año"
              @change="onAnioChange" />
          </div>
          <div class="md:col-span-2"> <label
              class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Periodo</label>
            <BaseSelectGrupo v-model="periodoSeleccionado" :options="periodos" label="nombre_periodo"
              placeholder="Seleccione un periodo" :disabled="!anioSeleccionado"
              :loading="periodoStore.periodosAniosFiltradoLoading" />
          </div>

          <div class="md:col-span-1 flex items-end"> <button @click="filtrarPorSeleccion"
              class="w-full bg-cetpro hover:bg-cetpro-dark text-white font-semibold py-2 px-4 rounded-md transition-colors duration-300 h-10 flex items-center justify-center">
              Filtrar </button> </div>
        </div>
      </div> <!-- TABLA -->

      <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-auto border border-gray-200 dark:border-gray-700 mt-4">

        <!-- ENCABEZADO Tabla -->
        <div class="flex justify-between items-center p-2 pb-0">
          <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
            Lista de Especialidades
          </h3>

          <!-- Buscador opcional -->
          <SearchBar v-if="!isLoading && especialidades.length > 0" :totalResultados="especialidades.length"
            :campoOrden="'nombre_especialidad'" @search="filtrarEspecialidades" />
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="p-4 space-y-2">
          <div v-for="i in 5" :key="i" class="h-12 bg-gray-200 dark:bg-gray-700 rounded-md animate-pulse"></div>
        </div>

        <!-- TABLA -->
        <Table v-else-if="especialidades.length > 0" :paginacion="false" class="w-full border-collapse mt-2">

          <THead>
            <Th class="border-b border-gray-300 dark:border-gray-600 w-[40px] text-center">N°</Th>
            <Th class="border-b border-gray-300 dark:border-gray-600 min-w-[250px]">Especialidad</Th>
            <Th class="border-b border-gray-300 dark:border-gray-600 w-[100px] text-center">Acciones</Th>
          </THead>

          <TBody>

            <!-- FILAS DE LA TABLA -->
            <tr v-for="(esp, index) in especialidades" :key="esp.id"
              class="border-b border-gray-300 dark:border-gray-700">

              <td class="text-center py-3">{{ index + 1 }}</td>

              <td class="py-3 font-semibold">
                {{ esp.nombre_especialidad }}
              </td>

              <td class="text-center py-3">
                <MenuTable :actions="{ view: true }" @view="verEgresados(esp)" entity-label="especialidad" />
              </td>

            </tr>

          </TBody>
        </Table>

        <!-- SIN RESULTADOS -->
        <div v-else class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
          </svg>
          <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
            No se encontraron especialidades
          </h3>
          <p class="mt-1 text-sm text-gray-500">Intenta con otro periodo.</p>
        </div>

      </div>

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
