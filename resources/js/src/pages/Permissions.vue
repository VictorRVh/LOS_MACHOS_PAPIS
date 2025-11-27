<script setup>
import { ref, computed } from 'vue';

import SearchBar from '../components/head_table/headSearch.vue';

import Table from '../components/table/Table.vue';
import THead from '../components/table/THead.vue';
import TBody from '../components/table/TBody.vue';
import Tr from '../components/table/Tr.vue';
import Th from '../components/table/Th.vue';
import Td from '../components/table/Td.vue';

import CreateButton from '../components/ui/CreateButton.vue';
import EditButton from '../components/ui/EditButton.vue';
import DeleteButton from '../components/ui/DeleteButton.vue';
import AuthorizationFallback from '../components/page/AuthorizationFallback.vue';
import PermissionSlider from '../components/page/PermissionSlider.vue';

import useUserStore from '../store/useUserStore';
import useRoleStore from '../store/useRoleStore';
import usePermissionStore from '../store/usePermissionStore';

import useSlider from '../composables/useSlider';
import useModalToast from '../composables/useModalToast';
import useHttpRequest from '../composables/useHttpRequest';

import useTableData from '../composables/tabla/useTableData';

const userStore = useUserStore();
const roleStore = useRoleStore();
const permissionStore = usePermissionStore();

if (!permissionStore.permissions.length)
    await permissionStore.loadPermissions();

const { slider, sliderData, showSlider, hideSlider } =
    useSlider('permission-crud');
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deletePermission, deleting } = useHttpRequest('/permissions');

const onDelete = (permission) => {
    if (deleting.value) return;

    showConfirmModal(null, async (confirmed) => {
        if (!confirmed) return;

        const isDeleted = await deletePermission(permission?.id);
        if (isDeleted) {
            showToast(`Permission "${permission?.name}" deleted successfully...`);
            permissionStore.loadPermissions();
            userStore.loadUsers();
            roleStore.loadRoles();
        }
    });
};

// ----------------------------
// FILTRO, ORDEN Y PAGINACIÓN
// ----------------------------
const permisos = computed(() => permissionStore.permissions);

const {
    query,
    orderBy,
    orderDirection,
    pagina,
    itemsPorPagina,
    paginados: permisosPaginados,
    totalPaginas,
    ordenados: permisosOrdenados,
    filtrar: filtrarPermisos
} = useTableData(permisos, {
    defaultOrderBy: "name",
    searchFields: ["name", "id"]
});
</script>


<template>
    <AuthorizationFallback :permissions="['todo-acceso-permisos', 'ver-permisos']">
        <div class="w-full space-y-8 py-10 px-6">
            <div class="flex-between">
                <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">Permisos</h2>
                <CreateButton @click="showSlider(true)" />
            </div>

            <!-- Barra de búsqueda y contador -->
            <div class="flex-between flex-row-reverse my-5">
                <SearchBar
                    :totalResultados="permisosOrdenados.length"
                    :campoOrden="'name'"
                    @search="filtrarPermisos"
                />

                <div class="font-inter text-md w-full">
                    Lista de permisos
                </div>
            </div>

            <Table
                :paginacion="true"
                :current-page="pagina"
                :total-pages="totalPaginas"
                @changePage="pagina = $event"
            >
                <THead>
                    <Th>Id</Th>
                    <Th>Permiso</Th>
                    <Th>Acciones</Th>
                </THead>

                <TBody>
                    <Tr v-for="permission in permisosPaginados" :key="permission.id">
                        <Td>{{ permission?.id }}</Td>

                        <Td>{{ permission?.name }}</Td>

                        <Td class="align-middle">
                            <div class="flex items-center justify-center gap-2">
                                <EditButton @click="showSlider(true, permission)" />
                                <DeleteButton @click="onDelete(permission)" />
                            </div>
                        </Td>
                    </Tr>
                </TBody>
            </Table>
        </div>

        <PermissionSlider
            :show="slider"
            :permission="sliderData"
            @hide="hideSlider"
        />
    </AuthorizationFallback>
</template>
