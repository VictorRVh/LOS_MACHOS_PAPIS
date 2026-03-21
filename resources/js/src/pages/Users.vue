<script setup>
import { ref, computed } from "vue";
import { storeToRefs } from "pinia";

import SearchBar from "../components/head_table/headSearch.vue";
import Table from "../components/table/Table.vue";
import THead from "../components/table/THead.vue";
import TBody from "../components/table/TBody.vue";
import Tr from "../components/table/Tr.vue";
import Th from "../components/table/Th.vue";
import Td from "../components/table/Td.vue";
import MenuTable from "../components/table/MenuTable.vue";

import CreateButton from "../components/ui/CreateButton.vue";
import TableBadge from "../components/ui/TableBadge.vue";
import AuthorizationFallback from "../components/page/AuthorizationFallback.vue";
import UserSlider from "../components/page/UserSlider.vue";
import userInfoModal from "../components/page/Docente/infoUsuarioSlider.vue";
import RestaurarPasswordSlider from "../components/page/RestaurarContraseña.vue";

import useUserStore from "../store/useUserStore";
import useSlider from "../composables/useSlider";
import useModalToast from "../composables/useModalToast";
import useHttpRequest from "../composables/useHttpRequest";
import useTableData from "../composables/tabla/useTableData";

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

const usuarios = computed(() => userStore.users);
const totalUsuarios = computed(() => usuarios.value.length);
const usuariosActivos = computed(() =>
  usuarios.value.filter((user) => Number(user.status) === 1).length
);
const usuariosInactivos = computed(() =>
  usuarios.value.filter((user) => Number(user.status) !== 1).length
);

const { userData } = storeToRefs(userStore);
const showUserModal = ref(false);
const showRestorePassword = ref(false);
const selectedUser = ref(null);

const emitCloseModal = () => (showUserModal.value = false);

const verUsuario = async (user) => {
  await userStore.loadUserData(user.id);

  if (userStore.userData) {
    showUserModal.value = true;
  } else {
    showToast(`"${userStore.userData?.name}" No encontramos datos del docente`, "warning");
  }
};

const Restarurar = (user) => {
  selectedUser.value = user;
  showRestorePassword.value = true;
};

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
  <AuthorizationFallback :permissions="['todo-acceso-usuarios', 'ver-usuarios']">
    <div class="w-full space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <section
        class="border border-slate-200 bg-white px-3 py-2 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900"
      >
        <div class="flex flex-col gap-1.5">
          <div class="flex flex-col gap-1 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
              <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
                Gestion institucional
              </p>
              <h2 class="mt-0.5 text-[1.2rem] font-semibold tracking-tight text-cetpro dark:text-cetpro-light">
                Usuarios
              </h2>
            </div>

            <div class="shrink-0">
              <CreateButton @click="showSlider(true)" />
            </div>
          </div>

          <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Total usuarios
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalUsuarios }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Base general</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Activos
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ usuariosActivos }}</p>
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
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ usuariosInactivos }}</p>
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
                <p class="text-[1.05rem] font-semibold leading-none text-slate-900 dark:text-slate-100">
                  {{ usuariosOrdenados.length }}
                </p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Filtro actual</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section
        class="border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900"
      >
        <div class="mb-2.5 flex flex-col gap-2.5 lg:flex-row lg:items-end lg:justify-between">
          <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
              Registro operativo
            </p>
            <div class="mt-1 text-[15px] font-medium text-slate-800 dark:text-slate-100">Lista de usuarios</div>
            <p class="mt-1 max-w-xl text-[13px] text-slate-500 dark:text-slate-400">
              Consulta, ordenamiento y mantenimiento del personal registrado.
            </p>
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
            <Th>Rol</Th>
            <Th>Fecha de creacion</Th>
            <Th>Estado</Th>
            <Th class="text-center">Accion</Th>
          </THead>

          <TBody>
            <Tr v-for="(user, index) in usuariosPaginados" :key="index">
              <Td>
                <span class="text-gray-800 dark:text-gray-300">
                  {{ (pagina - 1) * itemsPorPagina + index + 1 }}
                </span>
              </Td>
              <Td>{{ user.name }}</Td>
              <Td>{{ user.apellido_paterno }} {{ user.apellido_materno }}</Td>
              <Td>{{ user.dni }}</Td>
              <Td>{{ user.email }}</Td>
              <Td>
                <TableBadge :label="user?.roles[0]?.name" variant="neutral" />
              </Td>
              <Td>{{ user.created_at.slice(0, 10) }}</Td>
              <Td>
                <TableBadge
                  :label="user.status === 1 ? 'Activo' : 'Inactivo'"
                  :variant="user.status === 1 ? 'success' : 'danger'"
                  :dot="true"
                />
              </Td>
              <Td class="text-center text-gray-600 dark:text-gray-200">
                <MenuTable
                  :actions="{ view: true, edit: true, delete: true, download: false, custom1: true }"
                  entity-label="usuario"
                  :labels="{ custom1: 'Restaurar Contraseña' }"
                  @view="verUsuario(user)"
                  @edit="showSlider(true, user)"
                  @delete="onDelete(user)"
                  @custom1="Restarurar(user)"
                />
              </Td>
            </Tr>
          </TBody>
        </Table>
      </section>
    </div>

    <UserSlider :show="slider" :user="sliderData" @hide="hideSlider" />
    <userInfoModal :show="showUserModal" :data="userData" @close="emitCloseModal" />
    <RestaurarPasswordSlider :show="showRestorePassword" :user="selectedUser" @hide="showRestorePassword = false" />
  </AuthorizationFallback>
</template>
