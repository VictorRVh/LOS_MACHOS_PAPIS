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
import StatsOverviewSection from "../components/page/StatsOverviewSection.vue";

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

const totalRoles = computed(() => roleStore.roles.length);
const totalPermisosAsignados = computed(() =>
  roleStore.roles.reduce((total, role) => total + role.permissions.length, 0)
);
const rolesMultipermiso = computed(() =>
  roleStore.roles.filter((role) => role.permissions.length > 1).length
);
const promedioPermisos = computed(() => {
  if (!roleStore.roles.length) return 0;
  return Math.round(totalPermisosAsignados.value / roleStore.roles.length);
});
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
    <div class="space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800 sm:px-3">
      <StatsOverviewSection eyebrow="Gestion institucional" title="Roles">
          <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Total roles
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalRoles }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Registrados</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Permisos asignados
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">
                  {{ totalPermisosAsignados }}
                </p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Acumulados</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Roles con detalle
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">
                  {{ rolesMultipermiso }}
                </p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Ver más</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Promedio permisos
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">
                  {{ promedioPermisos }}
                </p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Por rol</span>
              </div>
            </div>
          </div>
      </StatsOverviewSection>
    </div>

    <div class="flex flex-col gap-4 px-3 pb-24 sm:px-3 lg:flex-row lg:pb-0">
      <section class="w-full border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:w-1/3">
        <div class="bg-white dark:bg-gray-800">
          <RoleSlider :show="slider" :role="sliderData" @hide="hideSlider" />
        </div>
      </section>

      <section class="w-full border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:w-2/3">
        <div class="max-h-[70vh] overflow-y-auto">
          <Table class="min-w-full">
            <THead>
              <Th>Id</Th>
              <Th>Rol</Th>
              <Th>Permisos</Th>
              <Th>Acciones</Th>
            </THead>

            <TBody>
              <Tr v-for="(role, index) in roleStore.roles" :key="role.id">
                <Td>{{ index + 1 }}</Td>
                <Td>{{ role?.name }}</Td>
                <Td class="w-48 whitespace-nowrap">
                  <div :class="[
                    'flex gap-2 w-full',
                    role.permissions.length > 1
                      ? 'justify-between items-center'
                      : 'justify-center',
                  ]">
                    <ul class="text-sm text-gray-700 dark:text-gray-300 w-30 list-none">
                      <li v-for="(permission, index) in role.permissions.slice(0, 1)" :key="permission.id">
                        {{ permission.name }}
                      </li>
                    </ul>
                    <button v-if="role.permissions.length > 1" @click="showPermissionsModal(role)"
                      class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2 py-0.5 text-xs rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition">
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
      </section>

      <ModalRoles v-if="showModal" @close="showModal = false" size="5xl">
        <template #title>
          Permisos del Rol: <span class="uppercase font-bold text-cetpro dark:text-cetpro-light">{{ selectedRole?.name
            }}</span>
        </template>

        <template #body>
          <div class="max-h-[65vh] overflow-y-auto p-1 pr-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
              <div v-for="(permissions, resourceName) in groupedPermissions" :key="resourceName"
                class="break-inside-avoid">
                <h4
                  class="font-semibold text-gray-800 dark:text-gray-200 capitalize border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                  {{ resourceName.replace(/_/g, ' ') }}
                </h4>
                <ul class="space-y-1.5">
                  <li v-for="permission in permissions" :key="permission.id"
                    class="flex items-start text-sm text-gray-600 dark:text-gray-400">
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
            <button @click="showModal = false"
              class="px-4 py-2 text-sm bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-700 dark:hover:bg-gray-600">
              Cerrar
            </button>
          </div>
        </template>
      </ModalRoles>
    </div>
  </AuthorizationFallback>
</template>
