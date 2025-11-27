<script setup>
import { computed } from 'vue';

import SearchBar from '../components/head_table/headSearch.vue';

import Table from '../components/table/Table.vue';
import THead from '../components/table/THead.vue';
import TBody from '../components/table/TBody.vue';
import Tr from '../components/table/Tr.vue';
import Th from '../components/table/Th.vue';
import Td from '../components/table/Td.vue';

import AuthorizationFallback from '../components/page/AuthorizationFallback.vue';

import usePermissionStore from '../store/usePermissionStore';
import useTableData from '../composables/tabla/useTableData';

const permissionStore = usePermissionStore();

if (!permissionStore.permissions.length)
    await permissionStore.loadPermissions();

// ---- FILTRO y PAGINACIÓN ----
const permisos = computed(() => permissionStore.permissions);

const {
    pagina,
    itemsPorPagina,
    paginados: permisosPaginados,
    totalPaginas,
    ordenados: permisosOrdenados,
    filtrar: filtrarPermisos
} = useTableData(permisos, {
    defaultOrderBy: "id",        // ← ORDEN POR ID
    searchFields: ["name", "id"]
});
</script>

<template>
    <AuthorizationFallback :permissions="['todo-acceso-permisos', 'ver-permisos']">
        <div class="w-full space-y-2 py-4 px-6">

      

            <!-- Filtro de búsqueda -->
            <div class="flex-between flex-row-reverse my-5">
                <SearchBar
                    :totalResultados="permisosOrdenados.length"
                    :campoOrden="'id'"
                    @search="filtrarPermisos"
                />
                <div class="font-inter text-md w-full">Lista de docentes</div>
            </div>

            <!-- Tabla de permisos -->
            <Table
                :paginacion="true"
                :current-page="pagina"
                :total-pages="totalPaginas"
                @changePage="pagina = $event"
            >
                <THead>
                    <Th>Id</Th>
                    <Th>Permiso</Th>
                    <Th>Descripción</Th>
                </THead>

                <TBody>
                    <Tr v-for="permission in permisosPaginados" :key="permission.id">
                        <Td>{{ permission?.id }}</Td>
                        <Td>{{ permission?.name }}</Td>
                        <Td class="text-slate-500">Permiso del sistema</Td>
                    </Tr>
                </TBody>
            </Table>

        </div>
    </AuthorizationFallback>
</template>
