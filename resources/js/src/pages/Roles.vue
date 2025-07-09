<script setup>
import Table from "../components/table/Table.vue";
import THead from "../components/table/THead.vue";
import TBody from "../components/table/TBody.vue";
import Tr from "../components/table/Tr.vue";
import Th from "../components/table/Th.vue";
import Td from "../components/table/Td.vue";
import CreateButton from "../components/ui/CreateButton.vue";
import EditButton from "../components/ui/EditButton.vue";
import DeleteButton from "../components/ui/DeleteButton.vue";
import AuthorizationFallback from "../components/page/AuthorizationFallback.vue";
import RoleSlider from "../components/page/RoleSlider.vue";

import useUserStore from "../store/useUserStore";
import useRoleStore from "../store/useRoleStore";
import usePermissionStore from "../store/usePermissionStore";
import useSlider from "../composables/useSlider";
import useModalToast from "../composables/useModalToast";
import useHttpRequest from "../composables/useHttpRequest";
import { ref } from "vue";
import ModalRoles from "../layouts/components/ModalRoles.vue";

const userStore = useUserStore();
const roleStore = useRoleStore();
const permissionStore = usePermissionStore();

if (!permissionStore.permissions.length) await permissionStore.loadPermissions();
if (!roleStore.roles?.length) await roleStore.loadRoles();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteRole, deleting } = useHttpRequest("/roles");

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
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Roles</h2>
      <!-- <CreateButton @click="showSlider(true)" /> -->
    </div>
    <div class="flex  px-6">
      <div class="w-1/2 bg-white dark:bg-gray-900 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Agregar Rol
        </h3>
        <hr class="border-t-2  border-cetpro dark:border-cetpro-light mb-4" />
        <RoleSlider :show="slider" :role="sliderData" @hide="hideSlider" />
      </div>
      

      <div class="w-full">
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
              <Td class="w-48 whitespace-nowrap">
                <div
                  :class="[
                    'flex gap-2 w-full',
                    role.permissions.length > 1
                      ? 'justify-between items-center'
                      : 'justify-center',
                  ]"
                >
                  <ul class="text-sm text-gray-700  w-30 list-none">
                    <li
                      v-for="(permission, index) in role.permissions.slice(0, 1)"
                      :key="permission.id"
                    >
                      {{ permission.name }}
                    </li>
                  </ul>
                  <button
                    v-if="role.permissions.length > 1"
                    @click="showPermissionsModal(role)"
                    class="bg-blue-100 text-blue-700 px-2 py-0.5 text-xs rounded hover:bg-blue-200 transition"
                  >
                    Ver más ({{ role.permissions.length }})
                  </button>
                </div>
              </Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, role)" />
                  <DeleteButton @click="onDelete(role)" />
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>

      <!-- Modal para ver permisos -->
      <ModalRoles v-if="showModal" @close="showModal = false" class="font-inter">
        <template #title>
          Permisos del Rol: <span class="uppercase">{{ selectedRole?.name }}</span>
        </template>

        <template #body>
          <ul class="ml-4 space-y-1 ">
            <li class="" v-for="permission in selectedRole?.permissions" :key="permission.id">
              {{ permission.name }}
            </li>
          </ul>
        </template>

        <template #footer>
          <button
            @click="showModal = false"
            class="px-3 py-1 text-sm bg-cetpro text-white rounded hover:bg-cetpro-dark"
          >
            Cerrar
          </button>
        </template>
      </ModalRoles>
    </div>
  </AuthorizationFallback>
</template>
