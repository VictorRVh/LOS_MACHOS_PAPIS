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
import UserSlider from "../../components/page/Administrativo/AdministrativoSlider.vue";

import useAdministraticoStore from "../../store/Administrativo/useAdministrativoStore";

import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import useTableData from "../../composables/tabla/useTableData";


const administrativo = useAdministraticoStore();


if (!administrativo.users?.length) await administrativo.loadUsers();

///console.log("Administrativo: pe: ",administrativo.users);

const { slider, sliderData, showSlider, hideSlider } = useSlider("user-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteUser, deleting } = useHttpRequest("/personal_administrativo");

const showModal = ref(false);

const onDelete = (user) => {
  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteUser(user?.id);
    if (isDeleted) {
      showToast(`"${user?.name}" eliminado correctamente...`);
     administrativo.loadUsers();
    }
  });
};

/// FILTAR USUARIOS

const usuarios = computed(() => administrativo.users);
const {
  query,
  orderBy,
  orderDirection,
  pagina,
  itemsPorPagina,
  paginados: usuariosPaginados,
  totalPaginas,
  ordenados: usuariosOrdenados,
  filtrar: filtrarUsuarios,
} = useTableData(usuarios, {
  defaultOrderBy: "apellido_paterno",
  searchFields: ["name", "apellido_paterno", "dni"],
});
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-usuarios', 'ver-usuarios']">
    <div class="w-full space-y-2 py-2 px-3">
      <div class="m-2">
        <div class="flex-between">
          <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">
            Administrativos
          </h2>
        </div>

        <div class="flex-between flex-row-reverse my-5">
          <SearchBar
            :totalResultados="usuariosOrdenados.length"
            :campoOrden="'apellido_paterno'"
            @search="filtrarUsuarios"
          />

          <div class="font-inter text-md w-full">Lista de usuarios</div>
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
          <Th>Rol</Th>
          <Th>Local</Th>
          <Th>Turno</Th>
          <Th class="text-center">Acción</Th>
        </THead>

        <TBody>
          <Tr v-for="(user, index) in usuariosPaginados" :key="index">
            <Td
              ><span class="text-gray-800 dark:text-gray-300">{{
                (pagina - 1) * itemsPorPagina + index + 1
              }}</span></Td
            >
            <Td>{{ user.name }}</Td>
            <Td
              >{{ user.apellido_paterno }}
              {{ user.apellido_materno }}</Td
            >
            <Td>{{ user.dni }}</Td>

            <Td>
              <span class="bg-gray-800 dark:bg-gray-600 text-white px-2 py-1 rounded-full">
                {{ user?.roles[0] }}
              </span>
            </Td>
            <Td>
              {{ user?.administrativo?.turno }}
            </Td>
            <Td>
              {{ user?.administrativo?.local }}
            </Td>

            <Td class="flex items-center justify-center gap-1">
              <EditButton @click="showSlider(true, user)" />
            </Td>
          </Tr>
        </TBody>
      </Table>
    </div>

    <UserSlider :show="slider" :admin="sliderData" @hide="hideSlider" />
  </AuthorizationFallback>
</template>
