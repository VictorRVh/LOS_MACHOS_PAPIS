<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";

import SearchBar from "../components/head_table/headSearch.vue";
import { storeToRefs } from "pinia";
import Table from "../components/table/Table.vue";
import THead from "../components/table/THead.vue";
import TBody from "../components/table/TBody.vue";
import Tr from "../components/table/Tr.vue";
import Th from "../components/table/Th.vue";
import Td from "../components/table/Td.vue";
import MenuTable from "../components/table/MenuTable.vue";

import CreateButton from "../components/ui/CreateButton.vue";
import EditButton from "../components/ui/EditButton.vue";
import DeleteButton from "../components/ui/DeleteButton.vue";
import AuthorizationFallback from "../components/page/AuthorizationFallback.vue";
import UserSlider from "../components/page/UserSlider.vue";

import useUserStore from "../store/useUserStore";

import useSlider from "../composables/useSlider";
import useModalToast from "../composables/useModalToast";
import useHttpRequest from "../composables/useHttpRequest";
import useTableData from "../composables/tabla/useTableData";

import userInfoModal from "../components/page/Docente/infoUsuarioSlider.vue"
import RestaurarPasswordSlider from "../components/page/RestaurarContraseña.vue";


const userStore = useUserStore();

if (!userStore.users?.length) await userStore.loadUsers();

const { slider, sliderData, showSlider, hideSlider } = useSlider("user-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteUser, deleting } = useHttpRequest("/users");



const onDelete = (user) => {
  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteUser(user?.id);
    if (isDeleted) {
      showToast(`"${user?.name}" eliminado correctamente...`);
      userStore.loadUsers();
    }
  });
};

/// FILTAR USUARIOS
// const usuarios = ref(userStore.users)
const usuarios = computed(() => userStore.users);

const { userData } = storeToRefs(userStore);
const showUserModal = ref(false);

const showRestorePassword = ref(false);
const selectedUser = ref(null);



const emitCloseModal = () => (showUserModal.value = false);

const verUsuario = async (user) => {
  await userStore.loadUserData(user.id);

  if (userStore.userData) {
    showUserModal.value = true; // abrir modal automáticamente
  } else {
    showToast(`"${userStore.userData?.name}" No encontramos datos del docente`, "warning");
  }
};


const Restarurar = (user) => {
  selectedUser.value = user;   // guardamos el ID del usuario seleccionado
  showRestorePassword.value = true; // abrimos el slider/modal
};


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
          <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">Usuarios</h2>
          <CreateButton @click="showSlider(true)" />
        </div>

        <div class="flex-between flex-row-reverse my-5">
          <SearchBar :totalResultados="usuariosOrdenados.length" :campoOrden="'apellido_paterno'"
            @search="filtrarUsuarios" />

          <div class="font-inter text-md w-full">Lista de usuarios</div>
        </div>
      </div>
      <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">
        <THead>
          <Th>N°</Th>
          <Th>Nombres</Th>
          <Th>Apellidos</Th>
          <Th>Dni</Th>
          <Th>Correo</Th>
          <Th>Rol</Th>
          <Th>Fecha de Creación</Th>
          <Th>Estado</Th>
          <Th class="text-center">Acción</Th>
        </THead>

        <TBody>
          <Tr v-for="(user, index) in usuariosPaginados" :key="index">
            <Td><span class="text-gray-800 dark:text-gray-300">{{
              (pagina - 1) * itemsPorPagina + index + 1
                }}</span></Td>
            <Td>{{ user.name }}</Td>
            <Td>{{ user.apellido_paterno }} {{ user.apellido_materno }}</Td>
            <Td>{{ user.dni }}</Td>
            <Td>{{ user.email }}</Td>
            <Td>
              <span class="bg-gray-800 dark:bg-gray-600 text-white px-2 py-1 rounded-full">
                {{ user?.roles[0]?.name }}
              </span>
            </Td>
            <Td>{{ user.created_at.slice(0, 10) }}</Td>
            <Td>
              <span :class="user.status === 1
                ? 'text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-900'
                : 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900'
                " class="px-2 py-1 text-xs rounded-md font-semibold inline-flex items-center gap-1">
                <span v-if="user.status === 1"> Activo ✓ </span>
                <span v-else="user.status === 0"> Inactivo X </span>
              </span>
            </Td>
            <Td class="text-center text-gray-600 dark:text-gray-200">
              <MenuTable :actions="{ view: true, edit: true, delete: true, download: false, custom1: true }"
                entity-label="usuario" :labels="{ custom1: 'Restaurar Contraseña' }" @view="verUsuario(user)"
                @edit="showSlider(true, user)" @delete="onDelete(user)" @custom1="Restarurar(user)" />
            </Td>
          </Tr>
        </TBody>
      </Table>
    </div>

    <UserSlider :show="slider" :user="sliderData" @hide="hideSlider" />
    <userInfoModal :show="showUserModal" :data="userData" @close="emitCloseModal" />
    <RestaurarPasswordSlider :show="showRestorePassword" :user="selectedUser" @hide="showRestorePassword = false" />

  </AuthorizationFallback>

</template>
