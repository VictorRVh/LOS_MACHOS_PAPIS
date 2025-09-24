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

import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import ModalRoles from "../../layouts/components/ModalRoles.vue";
import useHttpRequest from "../../composables/useHttpRequest";
import { ref } from "vue";

import comisionSlider from "../../components/page/Comision/ComisionSlider.vue";
import useComisionesStore from "../../store/Comision/useComisionesStore";

const comisionesStore = useComisionesStore();

if (!comisionesStore.comisiones.length) await comisionesStore.loadComisiones();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deletecomision, deleting } = useHttpRequest("/comisiones");

const onDelete = (comision) => {
  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deletecomision(comision?.id);
    if (isDeleted) {
      showToast(`Comisión "${comision?.titulo}" eliminada exitosamente...`);
      comisionesStore.loadComisiones();
      comisionesStore.loadComisionesUserFilter();
    }
  });
};

const showModal = ref(false);
const selectedRole = ref(null);

function showPermissionsModal(comision) {

  selectedRole.value = comision;

  // console.log('comision selec', selectedRole.value)

  showModal.value = true;
}
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Comisión</h2>
    </div>

    <div class="flex px-6 gap-6">
      <!-- Formulario lateral -->
      <div class="w-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Agregar comisión
        </h3>
        <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
        <comisionSlider :show="slider" :comision="sliderData" @hide="hideSlider" />
      </div>

      <!-- Tabla de comisiones -->
      <div class="w-full">
        <Table>
          <THead>
            <Th>#</Th>
            <Th>Comisión</Th>
            <Th>Usuarios</Th>
            <Th>Acciones</Th>
          </THead>

          <TBody>
            <Tr v-for="(comision, index) in comisionesStore.comisiones" :key="comision.id">
              <Td>{{ index + 1 }}</Td>
              <Td>{{ comision?.titulo }}</Td>

              <!-- Mostrar usuarios -->
              <Td class="w-48 whitespace-nowrap">
                <div :class="[
                  'flex gap-2 w-full',
                  comision.length > 1
                    ? 'justify-between items-center'
                    : 'justify-center',
                ]">
                  <ul class="text-sm text-gray-700  w-30 list-none">
                    <li v-for="(usuario, index) in comision.usuarios.slice(0, 1)" :key="usuario.id">
                      {{ usuario.nameCompleto }}
                    </li>
                  </ul>
                  <button v-if="comision.usuarios.length > 1" @click="showPermissionsModal(comision)"
                    class="bg-blue-100 text-blue-700 px-2 py-0.5 text-xs rounded hover:bg-blue-200 transition">
                    Ver más ({{ comision.usuarios.length }})
                  </button>
                </div>
              </Td>



              <Td>
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, comision)" />
                  <DeleteButton @click="onDelete(comision)" />
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>

      <ModalRoles v-if="showModal" @close="showModal = false" class="font-inter">
        <template #title>
          Usuarios de la comision: <span class="uppercase">{{ selectedRole?.name }}</span>
        </template>

        <template #body>
          <ul class="ml-4 space-y-1 ">
            <li class="" v-for="usuario in selectedRole?.usuarios" :key="usuario.id">
              {{ usuario.nameCompleto }}
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

    </div>
  </AuthorizationFallback>
</template>
