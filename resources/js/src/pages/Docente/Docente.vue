<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";

import SearchBar from "../../components/head_table/headSearch.vue";

import { storeToRefs } from "pinia";

import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import MenuTable from "../../components/table/MenuTable.vue";

import CreateButton from "../../components/ui/CreateButton.vue";
import EditButton from "../../components/ui/EditButton.vue";
import DeleteButton from "../../components/ui/DeleteButton.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import UserSlider from "../../components/page/Docente/DocenteSlider.vue";

import useDocenteStore from "../../store/Docente/useDocenteStore";

import useSlider from "../../composables/useSlider";

import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import useTableData from "../../composables/tabla/useTableData";
import ChangePasswordModal from "../../components/page/ChangePasswordModal.vue";

const docenteStore = useDocenteStore();

if (!docenteStore.docentes?.length) await docenteStore.loadDocentes();

const { slider, sliderData, showSlider, hideSlider } = useSlider("user-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteDocente, deleting } = useHttpRequest("/docente");

const showModal = ref(false);

const { requiereCambioPassword } = storeToRefs(docenteStore);


const onDelete = (docente) => {
  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteDocente(docente?.id);
    if (isDeleted) {
      showToast(`"${docente?.name}" eliminado correctamente...`);
      docenteStore.loadDocentes();
    }
  });
};

/// FILTAR USUARIOS
// const usuarios = ref(docenteStore.docentes)
const usuarios = computed(() => docenteStore.docentes);



const {
  query,
  orderBy,
  orderDirection,
  pagina,
  itemsPorPagina,
  paginados: usuariosPaginados,
  totalPaginas,
  ordenados: usuariosOrdenados,
  filtrar: filtrarUsuarios
} = useTableData(usuarios, {
  defaultOrderBy: "apellido_paterno",
  searchFields: ["name", "apellido_paterno", "dni"]
});

</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-usuarios', 'ver-usuarios']">
    <div class="w-full space-y-2 py-2 px-3">
      <div class="m-2">
        <div class="flex-between">
          <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">Docentes</h2>
          <CreateButton @click="showSlider(true)" />
        </div>

        <div class="flex-between flex-row-reverse my-5">
          <SearchBar
            :totalResultados="usuariosOrdenados.length"
            :campoOrden="'apellido_paterno'"
            @search="filtrarUsuarios"
          />

          <div class="font-inter text-md w-full">Lista de docentes</div>
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
          <Th>Nombres</Th>
          <Th>Apellidos</Th>
          <Th>Dni</Th>
          <Th>Correo</Th>
  
          <Th>Fecha de Creación</Th>
          <Th>Estado</Th>
          <Th class="text-center">Acción</Th>
        </THead>

        <TBody>
          <Tr v-for="(docente, index) in usuariosPaginados" :key="index">
            <Td
              ><span class="text-gray-800 dark:text-gray-300">{{
                (pagina - 1) * itemsPorPagina + index + 1
              }}</span></Td
            >
            <Td>{{ docente.name }}</Td>
            <Td>{{ docente.apellido_paterno }} {{ docente.apellido_materno }}</Td>
            <Td>{{ docente.dni }}</Td>
            <Td>{{ docente.email }}</Td>
        
            <Td>{{ docente.created_at.slice(0, 10) }}</Td>
            <Td>
              <span
                :class="
                  docente.status === 1
                    ? 'text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-900'
                    : 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900'
                "
                class="px-2 py-1 text-xs rounded-md font-semibold inline-flex items-center gap-1"
              >
                <span v-if="docente.status === 1"> Activo ✓ </span>
                <span v-else="docente.status === 0"> Inactivo X </span>
              </span>
            </Td>
            <Td class="text-center text-gray-600 dark:text-gray-200">
              <MenuTable
                :actions="{ view: true, edit: true, delete: true, download: false }"
                entity-label="usuario"
                @view="verGrupo(user)"
                @edit="showSlider(true, user)"
                @delete="onDelete(user)"
              />
            </Td>
          </Tr>
        </TBody>
      </Table>
    </div>

    <UserSlider :show="slider" :user="sliderData" @hide="hideSlider" />
  </AuthorizationFallback>
  <ChangePasswordModal v-if="showModal" @success="onPasswordChanged" />
</template>
