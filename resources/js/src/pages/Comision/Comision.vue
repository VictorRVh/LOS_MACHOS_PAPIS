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
import useHttpRequest from "../../composables/useHttpRequest";
import { ref } from "vue";

import comisionSlider from "../../components/page/Comision/ComisionSlider.vue";

import useComisionesStore from "../../store/Comision/useComisionesStore";

const comisionesStore = useComisionesStore();

if (!comisionesStore.comisiones.length) await comisionesStore.loadComisiones();

const { slider, sliderData, showSlider, hideSlider } =  useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy:deletecomision, deleting } = useHttpRequest("/comisiones");

const onDelete = (comision) => {



  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deletecomision(comision?.id);
    if (isDeleted) {
      showToast(`Comisión "${comision?.nombre_comision}" eliminado exitosamente...`);
      comisionesStore.loadComisiones();

    }
  });
};

</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Comisión</h2>
      <!-- <CreateButton @click="showSlider(true)" /> -->
    </div>
    
    <div class="flex  px-6">
      <div class="w-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Agregar comision
        </h3>
        <hr class="border-t-2  border-cetpro dark:border-cetpro-light mb-4" />
        <comisionSlider :show="slider" :comision="sliderData" @hide="hideSlider" />
      </div>
      

      <div class="w-full">
        <Table>
          <THead>
            <Th>Id</Th>
            <Th>Comision</Th>
            <Th>Estado</Th>
            <Th>Acciones</Th>
          </THead>

          <TBody>
            <Tr v-for="(comision,index) in comisionesStore.comisiones" :key="comision.id">
              <Td>{{ index +1 }}</Td>
              <Td>{{ comision?.titulo }}</Td> 
               <Td>{{ comision?.status }}</Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, comision)" />
                  <DeleteButton @click="onDelete(comision)" />
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>

    </div>
  </AuthorizationFallback>
</template>
