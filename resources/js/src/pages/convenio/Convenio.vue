<script setup>
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import EditButton from "../../components/ui/EditButton.vue";
import DeleteButton from "../../components/ui/DeleteButton.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import RoleSlider from "../../components/page/RoleSlider.vue";

import useUserStore from "../../store/useUserStore";
import useRoleStore from "../../store/useRoleStore";
import usePermissionStore from "../../store/usePermissionStore";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import { ref } from "vue";
import ModalRoles from "../../layouts/components/ModalRoles.vue";
import ConvenioSlider from "../../components/page/ConvenioSlider.vue";
import useConveniosStore from "../../store/Convenio/useConvenioStore";

const userStore = useUserStore();
const roleStore = useRoleStore();
const permissionStore = usePermissionStore();

const conveniosStore = useConveniosStore();

if (!permissionStore.permissions.length) await permissionStore.loadPermissions();
if (!roleStore.roles?.length) await roleStore.loadRoles();
if (!conveniosStore.convenios.length) await conveniosStore.loadConvenios();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteConvenio, deleting } = useHttpRequest("/convenio");

const onDelete = (convenio) => {
  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteConvenio(convenio?.id);
    if (isDeleted) {
      showToast(`Rol "${convenio?.nombre_institucion}" eliminado exitosamente...`);
      conveniosStore.loadConvenios();
    }
  });
};

</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Convenios</h2>
      <!-- <CreateButton @click="showSlider(true)" /> -->
    </div>
    <div class="flex  px-6">
      <div class="w-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Agregar Convenio
        </h3>
        <hr class="border-t-2  border-cetpro dark:border-cetpro-light mb-4" />
        <ConvenioSlider :show="slider" :convenio="sliderData" @hide="hideSlider" />
      </div>
      

      <div class="w-full">
        <Table>
          <THead>
            <Th>Id</Th>
            <Th>Convenio</Th>
            <Th>Acciones</Th>
          </THead>

          <TBody>
            <Tr v-for="convenio in conveniosStore.convenios" :key="convenio.id">
              <Td>{{ convenio?.id }}</Td>
              <Td>{{ convenio?.nombre_institucion }}</Td> 
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, convenio)" />
                  <DeleteButton @click="onDelete(convenio)" />
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>

    </div>
  </AuthorizationFallback>
</template>
