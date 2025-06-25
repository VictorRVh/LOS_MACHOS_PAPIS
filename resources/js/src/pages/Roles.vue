<script setup>
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
import RoleSlider from '../components/page/RoleSlider.vue';

import useUserStore from '../store/useUserStore';
import useRoleStore from '../store/useRoleStore';
import usePermissionStore from '../store/usePermissionStore';
import useSlider from '../composables/useSlider';
import useModalToast from '../composables/useModalToast';
import useHttpRequest from '../composables/useHttpRequest';
import { ref } from 'vue';
import ModalRoles from '../layouts/components/ModalRoles.vue';

const userStore = useUserStore();
const roleStore = useRoleStore();
const permissionStore = usePermissionStore();

if (!permissionStore.permissions.length)
    await permissionStore.loadPermissions();
if (!roleStore.roles?.length) await roleStore.loadRoles();

const { slider, sliderData, showSlider, hideSlider } = useSlider('role-crud');
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteRole, deleting } = useHttpRequest('/roles');

const onDelete = (role) => {
    if (deleting.value) return;

    showConfirmModal(null, async (confirmed) => {
        if (!confirmed) return;

        const isDeleted = await deleteRole(role?.id);
        if (isDeleted) {
            showToast(`Rol "${role?.name}" eliminado exitosamente...`);
            roleStore.loadRoles();
            userStore.loadUsers();
        }
    });
};

const showModal = ref(false);
const selectedRole = ref(null);

function showPermissionsModal(role) {
    selectedRole.value = role;
    showModal.value = true;
}

</script>

<template>
    <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
        <div class="w-full space-y-8 py-10 px-6">
            <div class="flex-between">
                <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">Roles</h2>
                <CreateButton @click="showSlider(true)" />
            </div>

            <Table>
                <THead>
                    <Th>Id</Th>
                    <Th>Rol</Th>
                    <Th>Permisos</Th>
                    <Th>Acciones</Th>
                </THead>

                <TBody>
                    <Tr v-for="role in roleStore.roles" :key="role.id">
                        <Td>{{ role?.id }}</Td>

                        <Td>{{ role?.name }}</Td>

                        <Td>
                            <div class="flex flex-col items-start">
                                <ul class="list-disc ml-4">
                                    <li v-for="(permission, index) in role.permissions.slice(0, 3)"
                                        :key="permission.id">
                                        {{ permission.name }}
                                    </li>
                                </ul>

                                <button v-if="role.permissions.length > 3" @click="showPermissionsModal(role)"
                                    class="text-xs text-blue-600 underline mt-1 hover:text-blue-800">
                                    Ver más
                                </button>
                            </div>
                        </Td>

                        <Td class="align-middle">
                            <div class="flex items-center justify-center gap-2">
                                <EditButton @click="showSlider(true, role)" />
                                <DeleteButton @click="onDelete(role)" />
                            </div>
                        </Td>
                    </Tr>
                </TBody>
            </Table>
        </div>

        <ModalRoles v-if="showModal" @close="showModal = false">
            <template #title>
                Permisos del Rol: {{ selectedRole?.name }}
            </template>

            <template #body>
                <ul class="list-disc ml-4 space-y-1">
                    <li v-for="permission in selectedRole?.permissions" :key="permission.id">
                        {{ permission.name }}
                    </li>
                </ul>
            </template>

            <template #footer>
                <button @click="showModal = false"
                    class="px-3 py-1 text-sm bg-cetpro text-white rounded hover:bg-cetpro-dark">
                    Cerrar
                </button>
            </template>
        </ModalRoles>


        <RoleSlider :show="slider" :role="sliderData" @hide="hideSlider" />
    </AuthorizationFallback>
</template>