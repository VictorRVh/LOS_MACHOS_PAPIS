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
import useProgramaStore from "../../store/Programa/useProgramaStore";
import useCicloStore from "../../store/Ciclo/useCicloStore";
import ProgramaSlider from "../../components/page/Programa/ProgramaSlider.vue";
import useEspecialidadStore from "../../store/Especialidad/useEspecialidadStore";
import useEspecialidadProgramaStore from "../../store/EspecialidadPrograma/useEspecialidadPrograma";
import EspecialidadProgramaSlider from "../../components/page/EspecialidadPrograma/EspecialidadProgramaSlider.vue";

const programaStore = useProgramaStore();
const especialidadStore = useEspecialidadStore();
const especialidadProgramaStore = useEspecialidadProgramaStore();

if (!programaStore.programa.length) await programaStore.loadPrograma();
if (!especialidadStore.especialidad.length) await especialidadStore.loadEspecialidad();
if (!especialidadProgramaStore.especialidadPrograma.length) await especialidadProgramaStore.loadEspecialidadPrograma();

const { slider, sliderData, showSlider, hideSlider } =  useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy:deletePrograma, deleting } = useHttpRequest("/especialidad_programa");

const onDelete = (especialidadPrograma) => {

  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deletePrograma  (especialidadPrograma?.id);
    if (isDeleted) {
      showToast(`Programa "${programa?.año}" eliminado exitosamente...`);
      especialidadProgramaStore.loadEspecialidadPrograma();

    }
  });
};

</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Especialidades</h2>
      <!-- <CreateButton @click="showSlider(true)" /> -->
    </div>
    <div class="flex  px-6">
      <div class="w-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Asignar Especialidad
        </h3>
        <hr class="border-t-2  border-cetpro dark:border-cetpro-light mb-4" />
        <EspecialidadProgramaSlider :show="slider" :especialidadPrograma="sliderData" :especialidad="especialidadStore.especialidad" :programa="programaStore.programa" @hide="hideSlider" />
      </div>
      

      <div class="w-full">
        <Table>
          <THead>
            <Th>Id</Th>
            <Th>Especialidad</Th>
            <Th>Programa</Th>
            <Th>Acciones</Th>
          </THead>

          <TBody>
            <Tr v-for="(especialidadPrograma,index) in especialidadProgramaStore.especialidadPrograma" :key="especialidadPrograma.id">
              <Td>{{ index +1 }}</Td>
              <Td>{{ especialidadPrograma?.id_especialidad }}</Td> 
               <Td>{{ especialidadPrograma?.id_programa }}</Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, programa)" />
                  <DeleteButton @click="onDelete(programa)" />
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>

    </div>
  </AuthorizationFallback>
</template>
