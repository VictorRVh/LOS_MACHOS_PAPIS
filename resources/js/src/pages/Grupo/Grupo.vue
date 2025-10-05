<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from 'vue-router';
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';

import { ChevronDownIcon } from '@heroicons/vue/24/solid';

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

const router = useRouter();
const breadcrumb = useBreadcrumbStore();
const grupoStore = useGrupoStore();
const cicloStore = useCicloStore();
const { slider, sliderData, showSlider, hideSlider } = useSlider("grupo-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteGrupo, deleting } = useHttpRequest("/grupo");

const grupos = ref([]);

const selectedCiclo = ref(null);
const selectedAnio = ref(null);
const selectedPeriodo = ref(null);
const openEspecialidades = ref(new Set());

//if (!grupoStore.grupos.length) await grupoStore.loadGrupos();
if (!cicloStore.ciclo.length) await cicloStore.loadCiclo();

const onCicloChange = async () => {
  selectedAnio.value = null;
  selectedPeriodo.value = null;
  if (selectedCiclo.value) {
    await grupoStore.loadAnios(selectedCiclo.value);
  } else {
    grupoStore.anios = [];
  }
};

const onAnioChange = async () => {
  selectedPeriodo.value = null;
  if (selectedAnio.value) {
    await grupoStore.loadPeriodoAnio(selectedAnio.value);
  } else {
    grupoStore.periodoAnio = [];
  }
};

const filtrarPorSeleccion = async () => {
  if (!selectedCiclo.value || !selectedAnio.value || !selectedPeriodo.value) {
    showToast('Debes seleccionar todos los filtros para buscar.', 'warning');
    return;
  }

  await grupoStore.loadGruposFiltrados({
    id_ciclo: selectedCiclo.value,
    anio: selectedAnio.value,
    id_periodo: selectedPeriodo.value,
  });
  grupos.value = grupoStore.gruposFiltrados;
  openEspecialidades.value = new Set(grupos.value.map(g => g.especialidad.id));

};

const verGrupo = (grupo) => {
  router.push({
    name: 'grupo.detalle',
    params: { id: grupo.id_grupo },
  });
 // breadcrumb.setTextItem("los peludos",grupo.id_grupo);

};

const onDelete = (grupo) => {
  if (deleting.value) return;
  showConfirmModal(
    { title: 'Confirmar Eliminación', text: `¿Estás seguro de eliminar este grupo? Esta acción no se puede deshacer.` },
    async (confirmed) => {
      if (!confirmed) return;
      const isDeleted = await deleteGrupo(grupo.id_grupo);
      if (isDeleted) {
        showToast(`Grupo eliminado correctamente.`);
        await fetchInitialData();
      }
    }
  );
};

const toggleEspecialidad = (id) => {
  const newSet = new Set(openEspecialidades.value);
  if (newSet.has(id)) {
    newSet.delete(id);
  } else {
    newSet.add(id);
  }
  openEspecialidades.value = newSet;
};
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-grupos', 'ver-grupos']">
    <div class="p-4 md:p-6 space-y-6">
      <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Grupos de Estudio</h1>
        <CreateButton @click="showSlider(true)" text="Agregar Nuevo" />
      </header>

      <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-white-200 dark:border-gray-700">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Ciclo</label>
            <BaseSelectGrupo v-model="selectedCiclo" :options="cicloStore.ciclo" label="nombre_ciclo"
              placeholder="Seleccione un ciclo" @change="onCicloChange" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Año</label>
            <BaseSelectGrupo v-model="selectedAnio" :options="grupoStore.anios" label="label"
              placeholder="Seleccione un año" @change="onAnioChange" :disabled="!selectedCiclo"
              :loading="grupoStore.aniosByCicloLoading" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Periodo</label>
            <BaseSelectGrupo v-model="selectedPeriodo" :options="grupoStore.periodoAnio" label="nombre_periodo"
              placeholder="Seleccione un periodo" :disabled="!selectedAnio"
              :loading="grupoStore.periodoByAnioLoading" />
          </div>
          <button @click="filtrarPorSeleccion"
            class="w-full bg-cetpro hover:bg-cetpro-dark text-white font-semibold py-2 px-4 rounded-md transition-colors duration-300 h-10 flex items-center justify-center">
            Filtrar
          </button>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex-between mb-4">
          <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Lista de grupos</h3>
          <SearchBar v-if="!isLoading && grupos.length > 0" :totalResultados="grupos.length" />
        </div>

        <div v-if="isLoading" class="space-y-2">
          <div v-for="i in 5" :key="i" class="h-12 bg-gray-200 dark:bg-gray-700 rounded-md animate-pulse"></div>
        </div>
        <div v-else>
          <Table v-if="grupos.length > 0">
            <THead class="hidden">
              <Th>N°</Th>
              <Th>Módulo</Th>
              <Th>Sección</Th>
              <Th>Turno</Th>
              <Th>Convenio</Th>
              <Th>Nro Est.</Th>
              <Th>Docente</Th>
              <Th>Acciones</Th>
            </THead>
            <TBody>
              <template v-for="(especialidad) in grupos" :key="especialidad.especialidad.id">
                <tr @click="toggleEspecialidad(especialidad.especialidad.id)"
                  class="bg-cetpro dark:bg-cetpro-dark hover:bg-cetpro-dark dark:hover:bg-cetpro cursor-pointer transition-colors duration-200 border-b border-white dark:border-cetpro">
                  <td colspan="8" class="px-4 py-3 font-bold uppercase tracking-wider text-sm">
                    <div class="flex items-center justify-between text-cetpro-text">
                      <span>{{ especialidad.especialidad.nombre }}</span>
                      <ChevronDownIcon
                        :class="['h-6 w-6 text-cetpro-text transition-transform duration-300', { 'rotate-180': openEspecialidades.has(especialidad.especialidad.id) }]" />
                    </div>
                  </td>
                </tr>
                <tr v-if="openEspecialidades.has(especialidad.especialidad.id)" class="bg-white dark:bg-gray-800">
                  <td colspan="8" class="p-0">
                    <TransitionGroup name="list" tag="table" class="w-full">
                <Tr v-for="(modulo, modIndex) in especialidad.modulos" :key="modulo.id_grupo" class="border-t-0">
                  <Td class="text-center w-12">{{ modIndex + 1 }}</Td>
                  <Td>
                    <span class="font-semibold text-gray-500 dark:text-gray-400">Nombre: </span>
                    <span>{{ modulo.modulo.numero }}: {{ modulo.modulo.descripcion }}</span>
                  </Td>
                  <Td class="w-32">
                    <span class="font-semibold text-gray-500 dark:text-gray-400">Sección: </span>
                    <span>{{ modulo.seccion }}</span>
                  </Td>
                  <Td class="w-28">
                    <span class="font-semibold text-gray-500 dark:text-gray-400">Turno: </span>
                    <span>{{ modulo.turno }}</span>
                  </Td>
                  <Td class="w-48">
                    <span class="font-semibold text-gray-500 dark:text-gray-400">Convenio: </span>
                    <span>{{ modulo.convenio.nombre }}</span>
                  </Td>
                  <Td class="w-40">
                    <span class="font-semibold text-gray-500 dark:text-gray-400">Nro. Estudiantes: </span>
                    <span>{{ modulo.cantidad }}</span>
                  </Td>
                  <Td class="min-w-[250px]">
                    <span class="font-semibold text-gray-500 dark:text-gray-400">Docente: </span>
                    <span v-if="modulo.docente?.nombre">{{ modulo.docente.nombre }} {{ modulo.docente.apellido_paterno
                      }}</span>
                    <span v-else class="text-red-500 font-semibold italic text-xs">No asignado</span>
                  </Td>
                  <Td class="text-center w-28">
                    <MenuTable :actions="{ view: true, edit: true, delete: true }" entity-label="grupo"
                      @view="verGrupo(modulo)" @edit="showSlider(true, modulo)" @delete="onDelete(modulo)" />
                  </Td>
                </Tr>
                </TransitionGroup>
                </td>
                </tr>
              </template>
            </TBody>
          </Table>
          <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              aria-hidden="true">
              <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">No se encontraron grupos</h3>
            <p class="mt-1 text-sm text-gray-500">Intenta con otros filtros o crea un nuevo grupo para empezar.</p>
          </div>
        </div>
      </div>
    </div>
    <GrupoSlider :show="slider" :grupo="sliderData" @hide="hideSlider" />
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