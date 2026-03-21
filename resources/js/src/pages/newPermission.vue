<script setup>
import { computed } from "vue";

import SearchBar from "../components/head_table/headSearch.vue";
import Table from "../components/table/Table.vue";
import THead from "../components/table/THead.vue";
import TBody from "../components/table/TBody.vue";
import Tr from "../components/table/Tr.vue";
import Th from "../components/table/Th.vue";
import Td from "../components/table/Td.vue";
import AuthorizationFallback from "../components/page/AuthorizationFallback.vue";

import usePermissionStore from "../store/usePermissionStore";
import useTableData from "../composables/tabla/useTableData";

const permissionStore = usePermissionStore();

if (!permissionStore.permissions.length) {
  await permissionStore.loadPermissions();
}

const permisos = computed(() => permissionStore.permissions);

const totalPermisos = computed(() => permisos.value.length);
const permisosUsuarios = computed(() =>
  permisos.value.filter((permission) => permission.name.includes("usuarios")).length
);
const permisosRoles = computed(() =>
  permisos.value.filter((permission) => permission.name.includes("roles")).length
);
const permisosConAccion = computed(() =>
  permisos.value.filter((permission) =>
    ["ver-", "crear-", "editar-", "eliminar-"].some((prefix) => permission.name.startsWith(prefix))
  ).length
);

const {
  pagina,
  paginados: permisosPaginados,
  totalPaginas,
  ordenados: permisosOrdenados,
  filtrar: filtrarPermisos,
} = useTableData(permisos, {
  defaultOrderBy: "id",
  searchFields: ["name", "id"],
});
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-permisos', 'ver-permisos']">
    <div class="space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <section
        class="border border-slate-200 bg-white px-3 py-2 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900"
      >
        <div class="flex flex-col gap-1.5">
          <div class="flex flex-col gap-1">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
              Gestión institucional
            </p>
            <h2 class="text-[1.2rem] font-semibold tracking-tight text-cetpro dark:text-cetpro-light">Permisos</h2>
          </div>

          <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Total permisos
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalPermisos }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Registrados</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Módulo usuarios
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ permisosUsuarios }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Usuarios</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Módulo roles
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ permisosRoles }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Roles</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Permisos de acción
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ permisosConAccion }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">CRUD base</span>
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
            <div class="mt-1 text-[15px] font-medium text-slate-900 dark:text-slate-100">Lista de permisos</div>
          </div>

          <div class="w-full lg:w-auto">
            <SearchBar :totalResultados="permisosOrdenados.length" :campoOrden="'id'" @search="filtrarPermisos" />
          </div>
        </div>

        <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">
          <THead>
            <Th>Id</Th>
            <Th>Permiso</Th>
            <Th>Descripción</Th>
          </THead>

          <TBody>
            <Tr v-for="permission in permisosPaginados" :key="permission.id">
              <Td>{{ permission?.id }}</Td>
              <Td>{{ permission?.name }}</Td>
              <Td class="text-slate-500 dark:text-slate-400">Permiso del sistema</Td>
            </Tr>
          </TBody>
        </Table>
      </section>
    </div>
  </AuthorizationFallback>
</template>
