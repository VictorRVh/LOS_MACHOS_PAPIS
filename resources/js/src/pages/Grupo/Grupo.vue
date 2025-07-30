<script setup>
import { ref, computed } from "vue";

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
import ChangePasswordModal from "../../components/page/ChangePasswordModal.vue";
import GrupoSlider from "../../components/page/Grupo/GrupoSlider.vue";

import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import useTableData from "../../composables/tabla/useTableData";

import useGrupoStore from "../../store/Grupo/useGrupoStore";
import BaseSelectGrupo from "../../components/ui/BaseSelectGrupo.vue";

// Grupo store
const grupoStore = useGrupoStore();
if (!grupoStore.grupos?.length) await grupoStore.loadGrupos();

const { slider, sliderData, showSlider, hideSlider } = useSlider("grupo-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteGrupo, deleting } = useHttpRequest("/grupo");

const showModal = ref(false);
//VICTOR CABRO........................................
const onDelete = (grupo) => {
  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteGrupo(grupo?.id);
    if (isDeleted) {
      showToast(`Grupo "${grupo?.nombre}" eliminado correctamente...`);
      grupoStore.loadGrupos();
    }
  });
};

// Computed de grupos
const grupos = computed(() => grupoStore.grupos);

// Filtros
const programaAcademico = ref(null);
const anio = ref(null);
const periodo = ref(null);

const programas = ref([
  { label: "Auxiliar Técnico", value: "auxiliar" },
  { label: "Operador Industrial", value: "operador" },
]);

const periodos = ref([
  { label: "2025-I", value: "2025-1" },
  { label: "2025-II", value: "2025-2" },
]);

const anios = ref(
  Array.from({ length: 6 }, (_, i) => {
    const year = new Date().getFullYear() - i;
    return { label: `${year}`, value: year };
  })
);

const filtrarPorSeleccion = () => {
  console.log("Filtrar por:", {
    programa: programaAcademico.value,
    anio: anio.value,
    periodo: periodo.value,
  });
  // Aquí podrías aplicar lógica de filtrado real si lo deseas.
};

// Tabla
const {
  query,
  orderBy,
  orderDirection,
  pagina,
  itemsPorPagina,
  paginados: gruposPaginados,
  totalPaginas,
  ordenados: gruposOrdenados,
  filtrar: filtrarGrupos
} = useTableData(grupos, {
  defaultOrderBy: "nombre",
  searchFields: ["nombre", "modulo.nombre_modulo", "docente.name"]
});

</script>


<template>
  <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
    <div class="w-full space-y-2 py-2 px-3">
      <div class="m-2">
        <div class="flex-between">
          <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">Grupos</h2>
          <CreateButton @click="showSlider(true)" />
        </div>

        <!-- Filtros Superiores -->
        <div class="w-full border-cetpro-light dark:bg-gray-800 shadow-md border border-gray-200 dark:border-gray-700 p-4 my-5">
          <div class="grid md:grid-cols-4 gap-4 items-center">
            
            <!-- Programa Académico -->
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Programa Académico</label>
              <BaseSelectGrupo
                v-model="programaAcademico"
                :options="programas"
                label="Programa Académico"
                placeholder="Seleccione un programa"
                @change="filtrarPorSeleccion"
                :loading="false"
              />
            </div>

            <!-- Año -->
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Año</label>
              <BaseSelectGrupo
                v-model="anio"
                :options="anios"
                label="Año"
                placeholder="Seleccione un año"
                @change="filtrarPorSeleccion"
                :loading="false"
              />
            </div>

            <!-- Periodo -->
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Periodo</label>
              <BaseSelectGrupo
                v-model="periodo"
                :options="periodos"
                label="Periodo"
                placeholder="Seleccione un periodo"
                @change="filtrarPorSeleccion"
                :loading="false"
              />
            </div>

            <!-- Botón Filtrar -->
            <div class="flex items-end pt-5">
              <button
                @click="filtrarPorSeleccion"
                class="bg-primary hover:bg-primary-dark text-white py-2 px-4 rounded-md w-full"
              >
                Filtrar
              </button>
            </div>
          </div>
        </div>


        <div class="flex-between flex-row-reverse mb-4">
          <SearchBar
            :totalResultados="gruposOrdenados.length"
            :campoOrden="'nombre'"
            @search="filtrarGrupos"
          />
          <div class="font-inter text-md w-full">Lista de grupos</div>
        </div>
      </div>

      <Table
        :paginacion="true"
        :current-page="pagina"
        :total-pages="totalPaginas"
        @changePage="pagina = $event"
      >
        <THead>
          <Th>N°</Th>
          <Th>Nombre</Th>
          <Th>Módulo</Th>
          <Th>Docente</Th>
          <Th>Periodo</Th>
          <Th>Fecha de Creación</Th>
          <Th>Estado</Th>
          <Th class="text-center">Acción</Th>
        </THead>

        <TBody>
          <Tr v-for="(grupo, index) in gruposPaginados" :key="grupo.id">
            <Td>{{ (pagina - 1) * itemsPorPagina + index + 1 }}</Td>
            <Td>{{ grupo.nombre }}</Td>
            <Td>{{ grupo.modulo?.nombre_modulo ?? '---' }}</Td>
            <Td>{{ grupo.docente?.name ?? '---' }}</Td>
            <Td>{{ grupo.periodo?.nombre ?? '---' }}</Td>
            <Td>{{ grupo.created_at?.slice(0, 10) ?? '---' }}</Td>
            <Td>
              <span
                :class="grupo.status === 1
                  ? 'text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-900'
                  : 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900'"
                class="px-2 py-1 text-xs rounded-md font-semibold inline-flex items-center gap-1"
              >
                <span v-if="grupo.status === 1">Activo ✓</span>
                <span v-else>Inactivo X</span>
              </span>
            </Td>
            <Td class="text-center text-gray-600 dark:text-gray-200">
              <MenuTable
                :actions="{ view: true, edit: true, delete: true, download: false }"
                entity-label="grupo"
                @view="showSlider(true, grupo)"
                @edit="showSlider(true, grupo)"
                @delete="onDelete(grupo)"
              />
            </Td>
          </Tr>
        </TBody>
      </Table>
    </div>

    <GrupoSlider :show="slider" :grupo="sliderData" @hide="hideSlider" />
  </AuthorizationFallback>
  <ChangePasswordModal v-if="showModal" @success="onPasswordChanged" />
</template>
