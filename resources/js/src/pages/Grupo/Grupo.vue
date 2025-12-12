<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";

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

const router = useRouter();
const breadcrumb = useBreadcrumbStore();
const grupoStore = useGrupoStore();
const cicloStore = useCicloStore();

const { slider, sliderData, showSlider, hideSlider } = useSlider("grupo-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteGrupo, deleting } = useHttpRequest("/grupo");

const grupos = ref([]);
const isLoading = ref(false);

const selectedCiclo = ref(null);
const selectedAnio = ref(null);
const selectedPeriodo = ref(null);

const openEspecialidades = ref(new Set());

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

// ---- Filtrar por selección
const filtrarPorSeleccion = async () => {
  if (!selectedCiclo.value || !selectedAnio.value || !selectedPeriodo.value) {
   // showToast("Debes seleccionar todos los filtros para buscar.", "warning");
    return;
  }

  isLoading.value = true;

  await grupoStore.loadGruposFiltrados({
    id_ciclo: selectedCiclo.value,
    anio: selectedAnio.value,
    id_periodo: selectedPeriodo.value,
  });

  grupos.value = grupoStore.gruposFiltrados || [];

  // abrir todas las especialidades luego del filtro
  openEspecialidades.value = new Set(gruposPlanos.value.map(g => g.especialidad));

  pagina.value = 1;
  isLoading.value = false;
};

const verGrupo = (grupo) => {
  console.log("loque llego: ", grupo)
  router.push({ name: "grupo.detalle", params: { id: grupo.id } });
};

const onDelete = (grupo) => {
  if (deleting.value) return;

  showConfirmModal(
    {
      title: "Confirmar Eliminación",
      text: "¿Estás seguro de eliminar este grupo? Esta acción no se puede deshacer.",
    },
    async (confirmed) => {
      if (!confirmed) return;

      const isDeleted = await deleteGrupo(grupo.id);

      if (isDeleted) {
        showToast("Grupo eliminado correctamente.");
        await filtrarPorSeleccion();
      }
    }
  );
};

const toggleEspecialidad = (nombre) => {
  const temp = new Set(openEspecialidades.value);
  temp.has(nombre) ? temp.delete(nombre) : temp.add(nombre);
  openEspecialidades.value = temp;
};

// ---- Mapeo plano de grupos
const gruposPlanos = computed(() => {
  return grupos.value.map((g) => ({
    id: g.id,
    especialidad: g.especialidad,
    modulo: g.modulo,
    seccion: g.seccion,
    turno: g.turno,
    convenio_nombre: g.convenio_nombre ?? "Sin convenio",
    docente: g.docente ?? "No asignado",
    cantidad_estudiantes: g.cantidad_estudiantes ?? 0,
    status: g.status,
    id_programa: g.id_programa,
    id_especialidad: g.id_especialidad,
    id_modulo: g.id_modulo,
    id_periodo: g.id_periodo,
    id_convenio: g.id_convenio,
    id_docente: g.id_docente,
    ciclo_id: g.ciclo_id,
    fecha_inicio: g.fecha_inicio,
    fecha_fin: g.fecha_fin,
    fecha_entrega_acta: g.fecha_entrega_acta,
  }));
});

// ---- Configuración de tabla
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

// ---- Agrupación por especialidad
const gruposAgrupados = computed(() => {
  const agrupados = {};

  gruposPaginados.value.forEach((grupo) => {
    if (!agrupados[grupo.especialidad]) agrupados[grupo.especialidad] = [];
    agrupados[grupo.especialidad].push(grupo);
  });

  return Object.entries(agrupados);
});



</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-grupos', 'ver-grupos']">
    <div class="p-4 md:p-6 space-y-2"> <!-- Header -->
      <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Grupos de Estudio</h1>
        <CreateButton @click="showSlider(true)" text="Agregar Nuevo" />
      </header> <!-- FILTROS -->
      <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-end"> <!-- Ciclo -->
          <div class="md:col-span-2"> <label
              class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Ciclo</label>
            <BaseSelectGrupo v-model="selectedCiclo" :options="cicloStore.ciclo" label="nombre_ciclo"
              placeholder="Seleccione un ciclo" @change="onCicloChange" />
          </div> <!-- Año -->
          <div class="md:col-span-2"> <label
              class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Año</label>
            <BaseSelectGrupo v-model="selectedAnio" :options="grupoStore?.anios" label="label"
              placeholder="Seleccione un año" @change="onAnioChange" :disabled="!selectedCiclo"
              :loading="grupoStore.aniosByCicloLoading" />
          </div> <!-- Periodo -->
          <div class="md:col-span-2"> <label
              class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Periodo</label>
            <BaseSelectGrupo v-model="selectedPeriodo" :options="grupoStore.periodoAnio" label="nombre_periodo"
              placeholder="Seleccione un periodo" :disabled="!selectedAnio"
              :loading="grupoStore.periodoByAnioLoading" />
          </div> <!-- BOTÓN Filtrar -->
          <div class="md:col-span-1 flex items-end"> <button @click="filtrarPorSeleccion"
              class="w-full bg-cetpro hover:bg-cetpro-dark text-white font-semibold py-2 px-4 rounded-md transition-colors duration-300 h-10 flex items-center justify-center">
              Filtrar </button> </div>
        </div>
      </div> <!-- TABLA -->
      <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-auto border border-gray-200 dark:border-gray-700">
        <!-- ENCABEZADO Tabla + Buscador -->
        <div class="flex justify-between items-center p-2 pb-0">
          <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Lista de Grupos</h3>
          <SearchBar v-if="!isLoading && gruposPlanos.length > 0" :totalResultados="gruposOrdenados.length"
            :campoOrden="'modulo'" @search="filtrarGrupos" />
        </div> <!-- Loading -->
        <div v-if="isLoading" class="p-4 space-y-2">
          <div v-for="i in 5" :key="i" class="h-12 bg-gray-200 dark:bg-gray-700 rounded-md animate-pulse"></div>
        </div> <!-- TABLA con diseño mejorado -->
        <Table v-else-if="gruposPaginados.length > 0" :paginacion="true" :current-page="pagina"
          :total-pages="totalPaginas" @changePage="pagina = $event" class="w-full border-collapse mt-2">
          <THead>
            <Th class="border-b border-gray-300 dark:border-gray-600 w-[40px] text-center">N°</Th>
            <Th class="border-b border-gray-300 dark:border-gray-600 min-w-[250px]">Módulo</Th>
            <Th class="border-b border-gray-300 dark:border-gray-600 w-[80px] text-center">Sección</Th>
            <Th class="border-b border-gray-300 dark:border-gray-600 w-[80px] text-center">Turno</Th>
            <Th class="border-b border-gray-300 dark:border-gray-600 min-w-[180px]">Convenio</Th>
            <Th class="border-b border-gray-300 dark:border-gray-600 w-[80px] text-center">N° Estudiantes</Th>
            <Th class="border-b border-gray-300 dark:border-gray-600 w-[180px] text-center">Docente</Th>
            <Th class="border-b border-gray-300 dark:border-gray-600 w-[60px] text-center">Acciones</Th>
          </THead>
          <TBody> <template v-for="([nombreEspecialidad, modulos]) in gruposAgrupados" :key="nombreEspecialidad">
              <!-- Fila de ESPECIALIDAD -->
              <tr @click="toggleEspecialidad(nombreEspecialidad)"
                class="bg-cetpro dark:bg-cetpro-dark hover:bg-cetpro-dark/80 cursor-pointer">
                <td colspan="8"
                  class="px-4 py-2 font-bold uppercase tracking-wider text-sm border-b border-gray-300 dark:border-gray-700">
                  <div class="flex justify-between items-center text-cetpro-text"> <span>{{ nombreEspecialidad }}</span>
                    <ChevronDownIcon
                      :class="['h-6 w-6 transition-transform duration-300', { 'rotate-180': openEspecialidades.has(nombreEspecialidad) }]" />
                  </div>
                </td>
              </tr> <!-- FILAS DE GRUPOS (MODULOS) -->
              <tr v-for="(grupo, index) in modulos" :key="grupo.id" v-show="openEspecialidades.has(nombreEspecialidad)"
                class="border-b border-gray-300 dark:border-gray-700">
                <td class="text-center py-3">{{ index + 1 }}</td>
                <td class="py-3">{{ grupo.modulo }}</td>
                <td class="text-center py-3">{{ grupo.seccion }}</td>
                <td class="text-center py-3">{{ grupo.turno }}</td>
                <td class="py-3">{{ grupo.convenio_nombre }}</td>
                <td class="text-center text-green-700 font-semibold py-3"> {{ grupo.cantidad_estudiantes }} </td>
                <td class="py-3">{{ grupo.docente }}</td>
                <td class="text-center py-3">
                  <MenuTable :actions="{ view: true, edit: true, delete: true }" @view="verGrupo(grupo)"
                    @edit="showSlider(true, grupo)" @delete="onDelete(grupo)" entity-label="grupo" />
                </td>
              </tr>
            </template>
          </TBody>
        </Table> <!-- Sin resultados -->
        <div v-else class="text-center py-12"> <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
          </svg>
          <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">No se encontraron grupos</h3>
          <p class="mt-1 text-sm text-gray-500">Intenta con otros filtros o crea un nuevo grupo.</p>
        </div>
      </div>
    </div> <!-- Slider CRUD -->
    <GrupoSlider :show="slider" :grupo="sliderData" @hide="hideSlider" @updated="filtrarPorSeleccion()"  />
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
