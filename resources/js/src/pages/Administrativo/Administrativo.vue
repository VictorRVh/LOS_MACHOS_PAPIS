<script setup>
import { ref, computed } from "vue";

import SearchBar from "../../components/head_table/headSearch.vue";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import TableBadge from "../../components/ui/TableBadge.vue";
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

const { slider, sliderData, showSlider, hideSlider } = useSlider("user-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteUser, deleting } = useHttpRequest("/personal_administrativo");

const onDelete = (user) => {
  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteUser(user?.id);
    if (isDeleted) {
      showToast(`Los datos administrativos de "${user?.name}" fueron eliminados correctamente.`);
      administrativo.loadUsers();
    }
  });
};

const usuarios = computed(() => administrativo.users);
const totalAdministrativos = computed(() => usuarios.value.length);
const localesRegistrados = computed(() => {
  const locales = new Set(
    usuarios.value.map((user) => user?.administrativo?.local).filter(Boolean)
  );
  return locales.size;
});
const turnosRegistrados = computed(() => {
  const turnos = new Set(
    usuarios.value.map((user) => user?.administrativo?.turno).filter(Boolean)
  );
  return turnos.size;
});

const {
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

orderDirection.value = "asc";
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-administrativos', 'ver-administrativos']">
    <div class="w-full space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <section
        class="border border-slate-200 bg-white px-3 py-2 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900"
      >
        <div class="flex flex-col gap-1.5">
          <div class="flex flex-col gap-1">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
              Gestión institucional
            </p>
            <h2 class="text-[1.2rem] font-semibold tracking-tight text-cetpro dark:text-cetpro-light">
              Administrativos
            </h2>
          </div>

          <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Total administrativos
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">
                  {{ totalAdministrativos }}
                </p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Registrados</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Locales
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ localesRegistrados }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Activos</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Turnos
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ turnosRegistrados }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Disponibles</span>
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
            <div class="mt-1 text-[15px] font-medium text-slate-900 dark:text-slate-100">Lista de administrativos</div>
          </div>

          <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center sm:justify-end lg:w-auto">
            <div class="w-full lg:w-auto">
              <SearchBar
                :totalResultados="usuariosOrdenados.length"
                :campoOrden="'apellido_paterno'"
                @search="filtrarUsuarios"
              />
            </div>
            <CreateButton @click="showSlider(true)" />
          </div>
        </div>

        <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">
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
              <Td><span class="text-gray-800 dark:text-gray-300">{{ (pagina - 1) * itemsPorPagina + index + 1 }}</span></Td>
              <Td>{{ user.name }}</Td>
              <Td>{{ user.apellido_paterno }} {{ user.apellido_materno }}</Td>
              <Td>{{ user.dni }}</Td>
              <Td>
                <TableBadge :label="user?.roles[0]" variant="neutral" />
              </Td>
              <Td>{{ user?.administrativo?.local }}</Td>
              <Td>{{ user?.administrativo?.turno }}</Td>
              <Td class="flex items-center justify-center gap-1">
                <EditButton @click="showSlider(true, user)" />
                <DeleteButton @click="onDelete(user)" />
              </Td>
            </Tr>
          </TBody>
        </Table>
      </section>
    </div>

    <UserSlider :show="slider" :admin="sliderData" @hide="hideSlider" />
  </AuthorizationFallback>
</template>
