<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from 'vue-router';

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

import useProgramaStore from '../../store/Programa/useProgramaStore'
import useGrupoStore from "../../store/Grupo/useGrupoStore";
import BaseSelectGrupo from "../../components/ui/BaseSelectGrupo.vue";

import usePeriodoStore from "../../store/Periodo/usePeriodoStore";
import useCicloStore from "../../store/Ciclo/useCicloStore";
import BaseSelectCiclo from "../../components/ui/BaseSelectCiclo.vue";

// Stores
const router = useRouter();
const grupoStore = useGrupoStore();
const programaStore = useProgramaStore();
const pe = usePeriodoStore();

const cicloStore = useCicloStore();

// if (!grupoStore.grupos?.length) await grupoStore.loadGrupos();
// if (!programaStore.programa?.length) await programaStore.loadPrograma();
// if (!peridoStore.periodos?.length) await peridoStore.loadPeriodos();

const { slider, sliderData, showSlider, hideSlider } = useSlider("grupo-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteGrupo, deleting } = useHttpRequest("/grupo");

const showModal = ref(false);

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
const verGrupo = (grupo) => {
  router.push({
    name: 'grupo.documentos',
    params: { id: grupo.id_grupo },
  });
};
const grupos = ref([]);;
const selectedCiclo = ref(null)
const selectedAnio = ref(null)
const selectedPeriodo = ref(null)

// onMounted(async () => {
//   await grupoStore.loadGrupos();
//   grupos.value = grupoStore.grupos
// });

const onCicloChange = async () => {
  selectedAnio.value = null
  selectedPeriodo.value = null

  if (selectedCiclo.value) {
    await grupoStore.loadAnios(selectedCiclo.value);
  } else {
    grupoStore.anios = [];
  }
};


const onAnioChange = async () => {
  selectedPeriodo.value = null

  if (selectedAnio.value) {
    await grupoStore.loadPeriodoAnio(selectedAnio.value);
  } else {
    grupoStore.periodoAnio = [];
  }
};


const filtrarPorSeleccion = async () => {
  if (!selectedCiclo.value || !selectedAnio.value || !selectedPeriodo.value) {
    showToast('Seleccionar todos los filtros.')
    return;
  }

  await grupoStore.loadGruposFiltrados({
    id_ciclo: selectedCiclo.value,
    anio: selectedAnio.value,
    id_periodo: selectedPeriodo.value,
  });

  grupos.value = grupoStore.gruposFiltrados;
};



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
        <div class="w-full border-cetpro-light dark:bg-gray-800 shadow-md border dark:border-gray-700 p-4 my-5">
          <div class="grid md:grid-cols-4 gap-4 items-center">
            <!-- Programa Académico -->
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Ciclo</label>

              <BaseSelectGrupo v-model="selectedCiclo" :options="cicloStore.ciclo" label="nombre_ciclo"
                placeholder="Seleccione un ciclo" @change="onCicloChange" />

            </div>

            <!-- Año -->
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Año</label>
              <BaseSelectGrupo v-model="selectedAnio" :options="grupoStore.anios" label="label"
                placeholder="Seleccione un año" @change="onAnioChange" :disabled="!selectedCiclo"
                :loading="grupoStore.aniosByCicloLoading" />
            </div>

            <!-- Periodo -->
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Periodo</label>
              <BaseSelectGrupo v-model="selectedPeriodo" :options="grupoStore.periodoAnio" label="nombre_periodo"
                placeholder="Seleccione un periodo" :disabled="!selectedAnio"
                :loading="grupoStore.periodoByAnioLoading" />
            </div>

            <!-- Botón Filtrar -->
            <div class="flex items-end pt-5">
              <button @click="filtrarPorSeleccion"
                class="bg-cetpro hover:bg-primary-dark text-white py-2 px-4 rounded-md w-full">
                Filtrar
              </button>
            </div>
          </div>
        </div>

        <div class="flex-between flex-row-reverse mb-4">
          <SearchBar :totalResultados="gruposOrdenados.length" :campoOrden="'nombre'" @search="filtrarGrupos" />
          <div class="font-inter text-md w-full">Lista de grupos</div>
        </div>
      </div>

      <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">
        <THead>
          <Th>N°</Th>
          <Th>Módulo</Th>
          <Th>Sección</Th>
          <Th>Turno</Th>
          <Th>Convenio</Th>
          <Th>Nro Est.</Th>
          <Th>Docente</Th>
          <Th class="text-center">Acción</Th>
        </THead>

        <TBody>
          <template v-for="(especialidad, espIndex) in grupos" :key="especialidad.especialidad.id">
            <!-- Fila de la especialidad -->
            <Tr class="bg-gray-100 dark:bg-gray-800 font-bold">
              <Td colspan="8" class="uppercase">{{ especialidad.especialidad.nombre }}</Td>
            </Tr>

            <!-- Filas de módulos -->
            <Tr v-for="(modulo, modIndex) in especialidad.modulos" :key="modulo.id_grupo">
              <Td>{{ (pagina - 1) * itemsPorPagina + modIndex + 1 }}</Td>
              <Td><strong>{{ modulo.modulo.numero }}:</strong> {{ modulo.modulo.descripcion }} </Td>
              <Td>{{ modulo.seccion }}</Td>
              <Td>{{ modulo.turno }}</Td>
              <Td>{{ modulo.convenio.nombre }}</Td>
              <Td>{{ modulo.cantidad }}</Td>
              <Td>
                <span v-if="modulo.docente?.nombre">{{ modulo.docente.nombre }}</span>
                <span v-else class="text-red-500 font-semibold">Docente no asignado</span>
              </Td>

              <Td class="text-center">
                <MenuTable :actions="{ view: true, edit: true, delete: true }" entity-label="grupo"
                  @view="verGrupo(modulo)" @edit="showSlider(true, modulo)"
                  @delete="onDelete({ id: modulo.id_grupo })" />
              </Td>
            </Tr>
          </template>
        </TBody>

      </Table>
    </div>

    <GrupoSlider :show="slider" :grupo="sliderData" @hide="hideSlider" />
  </AuthorizationFallback>
  <ChangePasswordModal v-if="showModal" @success="onPasswordChanged" />
</template>