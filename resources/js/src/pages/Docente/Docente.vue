<script setup>
import { ref, computed } from "vue";
import { storeToRefs } from "pinia";

import SearchBar from "../../components/head_table/headSearch.vue";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import MenuTable from "../../components/table/MenuTable.vue";

import CreateButton from "../../components/ui/CreateButton.vue";
import TableBadge from "../../components/ui/TableBadge.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import StatsOverviewSection from "../../components/page/StatsOverviewSection.vue";
import DocenteSlider from "../../components/page/Docente/DocenteSlider.vue";
import DocenteInfoModal from "../../components/page/Docente/infoDocenteSlider.vue";

import useDocenteStore from "../../store/Docente/useDocenteStore";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import useTableData from "../../composables/tabla/useTableData";

const docenteStore = useDocenteStore();

if (!docenteStore.docentes?.length) {
  await docenteStore.loadDocentes();
}

const { slider, sliderData, showSlider, hideSlider } = useSlider("docente-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteDocente, deleting } = useHttpRequest("/docente");

const { docenteData } = storeToRefs(docenteStore);
const showDocenteModal = ref(false);

const onDelete = (docente) => {
  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteDocente(docente?.id);
    if (isDeleted) {
      showToast(`"${docente?.name}" eliminado correctamente...`);
      docenteStore.loadDocentes();
    } else {
      showToast(`"${docente?.name}" no se pudo eliminar...`, "warning");
    }
  });
};

const verDocente = async (docente) => {
  await docenteStore.getDatosDocente(docente.id);

  if (docenteStore.docenteData) {
    showDocenteModal.value = true;
  } else {
    showToast(`"${docente?.name}" No encontramos datos del docente`, "warning");
  }
};

const emitCloseModal = () => (showDocenteModal.value = false);

const usuarios = computed(() => docenteStore.docentes);
const totalDocentes = computed(() => usuarios.value.length);
const docentesActivos = computed(() =>
  usuarios.value.filter((docente) => Number(docente.status) === 1).length
);
const docentesInactivos = computed(() =>
  usuarios.value.filter((docente) => Number(docente.status) !== 1).length
);

const {
  orderDirection,
  pagina,
  itemsPorPagina,
  paginados: usuariosPaginados,
  totalPaginas,
  ordenados: usuariosOrdenados,
  filtrar: filtrarUsuarios,
} = useTableData(usuarios, {
  defaultOrderBy: "created_at",
  searchFields: ["name", "apellido_paterno", "dni"],
});

orderDirection.value = "asc";
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-docentes', 'ver-docentes']">
    <div class="w-full space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <StatsOverviewSection eyebrow="Gestion institucional" title="Docentes">
        <template #actions>
          <div class="shrink-0">
            <CreateButton @click="showSlider(true)" />
          </div>
        </template>

        <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Total docentes
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalDocentes }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Registrados</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Activos
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ docentesActivos }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Habilitados</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Inactivos
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ docentesInactivos }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Por revisar</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Resultados visibles
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">
                  {{ usuariosOrdenados.length }}
                </p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Filtro actual</span>
              </div>
            </div>
          </div>
      </StatsOverviewSection>

      <section
        class="border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900"
      >
        <div class="mb-2.5 flex flex-col gap-2.5 lg:flex-row lg:items-end lg:justify-between">
          <div class="min-w-0">
            <div class="text-[15px] font-medium text-slate-900 dark:text-slate-100">Lista de docentes</div>
          </div>

          <div class="w-full lg:w-auto">
            <SearchBar
              :totalResultados="usuariosOrdenados.length"
              :campoOrden="'apellido_paterno'"
              @search="filtrarUsuarios"
            />
          </div>
        </div>

        <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">
          <THead>
            <Th>N°</Th>
            <Th>Nombres</Th>
            <Th>Apellidos</Th>
            <Th>Dni</Th>
            <Th>Correo</Th>
            <Th>Fecha de creacion</Th>
            <Th>Estado</Th>
            <Th class="text-center">Accion</Th>
          </THead>

          <TBody>
            <Tr v-for="(docente, index) in usuariosPaginados" :key="docente.id">
              <Td>
                <span class="text-gray-800 dark:text-gray-300">
                  {{ (pagina - 1) * itemsPorPagina + index + 1 }}
                </span>
              </Td>
              <Td>{{ docente.name }}</Td>
              <Td>{{ docente.apellido_paterno }} {{ docente.apellido_materno }}</Td>
              <Td>{{ docente.dni }}</Td>
              <Td>{{ docente.email }}</Td>
              <Td>{{ docente.created_at.slice(0, 10) }}</Td>
              <Td>
                <TableBadge
                  :label="docente.status === 1 ? 'Activo' : 'Inactivo'"
                  :variant="docente.status === 1 ? 'success' : 'danger'"
                  :dot="true"
                />
              </Td>
              <Td class="text-center text-gray-600 dark:text-gray-200">
                <MenuTable
                  :actions="{ view: true, edit: true, delete: true, download: false }"
                  entity-label="Docente"
                  @view="verDocente(docente)"
                  @edit="showSlider(true, docente)"
                  @delete="onDelete(docente)"
                />
              </Td>
            </Tr>
          </TBody>
        </Table>
      </section>
    </div>

    <DocenteSlider :show="slider" :docente="sliderData ?? null" @hide="hideSlider" />
    <DocenteInfoModal :show="showDocenteModal" :data="docenteData" @close="emitCloseModal" />
  </AuthorizationFallback>
</template>
