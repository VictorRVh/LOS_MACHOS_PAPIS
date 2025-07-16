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
import EspecialidadSlider from "../../components/page/Especialidad/EspecialidadSlider.vue";
import useEspecialidadStore from "../../store/Especialidad/useEspecialidad";
import useCicloStore from "../../store/Ciclo/useCicloStore";

const especialidadStore = useEspecialidadStore();
const cicloStore = useCicloStore();

if (!especialidadStore.especialidad.length) await especialidadStore.loadEspecialidad();
if (!cicloStore.ciclo.length) await cicloStore.loadCiclo();

const { slider, sliderData, showSlider, hideSlider } =  useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy:deleteEspecialidad, deleting } = useHttpRequest("/especialidad_madre");

console.log('ciclo', cicloStore.ciclo)

const onDelete = (especialidad) => {

  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteEspecialidad  (especialidad?.id);
    if (isDeleted) {
      showToast(`Especialidad "${especialidad?.nombre_especialidad}" eliminado exitosamente...`);
      especialidadStore.loadPeriodos();

    }
  });
};

</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Especialidad</h2>
      <!-- <CreateButton @click="showSlider(true)" /> -->
    </div>
    <div class="flex  px-6">
      <div class="w-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Agregar Especialidad
        </h3>
        <hr class="border-t-2  border-cetpro dark:border-cetpro-light mb-4" />
        <EspecialidadSlider :show="slider" :especialidad="sliderData" :ciclo="cicloStore.ciclo" @hide="hideSlider" />
      </div>
      

      <div class="w-full">
        <Table>
          <THead>
            <Th>Id</Th>
            <Th>Especialidad</Th>
            <Th>Ciclo Academico</Th>
            <Th>Acciones</Th>
          </THead>

          <TBody>
            <Tr v-for="(especialidad,index) in especialidadStore.especialidad" :key="especialidad.id">
              <Td>{{ index +1 }}</Td>
              <Td>{{ especialidad?.nombre_especialidad }}</Td> 
               <Td>{{ especialidad?.ciclo_academico.nombre_ciclo }}</Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, especialidad)" />
                  <DeleteButton @click="onDelete(especialidad)" />
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>

    </div>
  </AuthorizationFallback>
</template>
