<script setup>
import { ref, computed, onMounted } from "vue";

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
import ChangePasswordModal from "../components/page/ChangePasswordModal.vue";

const userStore = useUserStore();


if (!userStore.users?.length) await userStore.loadUsers();


const { slider, sliderData, showSlider, hideSlider } = useSlider("user-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteUser, deleting } = useHttpRequest("/users");

const showModal = ref(false);

const { requiereCambioPassword } = storeToRefs(userStore);

onMounted(() => {
  if (requiereCambioPassword.value) {
    showModal.value = true;
  }
});

const onPasswordChanged = () => {
  showModal.value = false;
  userStore.setRequiereCambioPassword(false);
};

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

const query = ref("");
const orderDirection = ref("asc");
const orderBy = ref("apellidos"); // podrías cambiarlo por otro campo si fuese nombre, dni, etc.

function filtrarUsuarios({ query: texto, orderDirection: orden, orderBy: campo }) {
  query.value = texto.toLowerCase()
  orderDirection.value = orden
  orderBy.value = campo
}

const usuariosFiltrados = computed(() => {

  if (!query.value) return usuarios.value;

  return usuarios.value.filter(
    (user) =>
      user.name.toLowerCase().includes(query.value) ||
      user.apellido_paterno.toLowerCase().includes(query.value) ||
      user.dni.includes(query.value)
  );
});

const usuariosOrdenados = computed(() => {
  const lista = [...usuariosFiltrados.value];
  return lista.sort((a, b) => {
    const campoA =
      orderBy.value === "apellidos"
        ? `${a.apellido_paterno} ${a.apellido_materno}`.toLowerCase()
        : (a[orderBy.value] || "").toLowerCase();

    const campoB =
      orderBy.value === "apellidos"
        ? `${b.apellido_paterno} ${b.apellido_materno}`.toLowerCase()
        : (b[orderBy.value] || "").toLowerCase();

    return orderDirection.value === "asc"
      ? campoA.localeCompare(campoB)
      : campoB.localeCompare(campoA);
  });
});


//// PAGINACION DE USUAIOR
const pagina = ref(1);
const itemsPorPagina = 6;

const totalPaginas = computed(() => {
  return Math.ceil(usuariosOrdenados.value.length / itemsPorPagina);
});


const usuariosPaginados = computed(() => {
  const inicio = (pagina.value - 1) * itemsPorPagina;
  const fin = inicio + itemsPorPagina;
  return usuariosOrdenados.value.slice(inicio, fin);
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
          <SearchBar
             :totalResultados="usuariosOrdenados.length"
             :campoOrden="'apellidos'"
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
          <Th>Correo</Th>
          <Th>Rol</Th>
          <Th>Fecha de Creación</Th>
          <Th>Estado</Th>
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
            <Td>{{ user.apellido_paterno }} {{ user.apellido_materno }}</Td>
            <Td>{{ user.dni }}</Td>
            <Td>{{ user.email }}</Td>
            <Td>
              <span class="bg-gray-800 text-white px-2 py-1 rounded-full">
                {{ user.roles[0].name }}
              </span>
            </Td>
            <Td>{{ user.created_at.slice(0, 10) }}</Td>
            <Td>
              <span
                :class="
                  user.status === 1
                    ? 'text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-900'
                    : 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900'
                "
                class="px-2 py-1 text-xs rounded-md font-semibold inline-flex items-center gap-1"
              >
                <span v-if="user.status === 1"> Activo ✓ </span>
                <span v-else="user.status === 0"> Inactivo X </span>
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
