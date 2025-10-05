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
import { ref, computed } from "vue";
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

const groupedPermissions = computed(() => {
  if (!selectedRole.value?.permissions) return {};
  const groups = {};
  selectedRole.value.permissions.forEach(permission => {
    const resource = permission.name.split('-').pop();
    if (!groups[resource]) {
      groups[resource] = [];
    }
    groups[resource].push(permission);
  });
  return Object.keys(groups).sort().reduce(
    (obj, key) => { 
      obj[key] = groups[key]; 
      return obj;
    }, 
    {}
  );
});
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Roles</h2>
    </div>
    <div class="flex flex-col lg:flex-row px-6 gap-6">
      <div class="w-full lg:w-1/3 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Agregar Rol
        </h3>
        <hr class="border-t-2  border-cetpro dark:border-cetpro-light mb-4" />
        <RoleSlider :show="slider" :role="sliderData" @hide="hideSlider" />
      </div>
      
      <div class="w-full lg:w-2/3">
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
                  <ul class="text-sm text-gray-700 dark:text-gray-300 w-30 list-none">
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
                    class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2 py-0.5 text-xs rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition"
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

      <ModalRoles v-if="showModal" @close="showModal = false" size="5xl">
        <template #title>
          Permisos del Rol: <span class="uppercase font-bold text-cetpro dark:text-cetpro-light">{{ selectedRole?.name }}</span>
        </template>

        <template #body>
          <div class="max-h-[65vh] overflow-y-auto p-1 pr-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
              <div v-for="(permissions, resourceName) in groupedPermissions" :key="resourceName" class="break-inside-avoid">
                <h4 class="font-semibold text-gray-800 dark:text-gray-200 capitalize border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                  {{ resourceName.replace(/_/g, ' ') }}
                </h4>
                <ul class="space-y-1.5">
                  <li v-for="permission in permissions" :key="permission.id" class="flex items-start text-sm text-gray-600 dark:text-gray-400">
                    <span class="text-cetpro dark:text-cetpro-light mr-2 mt-1">&#9679;</span>
                    <span class="flex-1">{{ permission.name }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </template>

        <template #footer>
          <div class="flex justify-end border-t border-gray-200 dark:border-gray-700 pt-4">
            <button
              @click="showModal = false"
              class="px-4 py-2 text-sm bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-700 dark:hover:bg-gray-600"
            >
              Cerrar
            </button>
          </div>
        </template>
      </ModalRoles>
    </div>
  </AuthorizationFallback>
</template>