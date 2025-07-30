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
import { useRouter } from "vue-router";

const router = useRouter();

const programaStore = useProgramaStore();
const cicloStore = useCicloStore();

if (!programaStore.programa.length) await programaStore.loadPrograma();
if (!cicloStore.ciclo.length) await cicloStore.loadCiclo();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deletePrograma, deleting } = useHttpRequest("/programa_estudio");

const onDelete = (programa) => {

  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;
   
    //console.log("elelinad porgrma: ",programa?.id)
    const isDeleted = await deletePrograma(programa?.id);
    console.log("elelinad porgrma: ",isDeleted)
    if (isDeleted) {
      showToast(`Programa "${programa?.año}" eliminado exitosamente...`);
      programaStore.loadPrograma();

    }
  });
};

const SeeMore = (id) => {

  console.log('id del programa', id);

  router.push({
    name: "especialidadPrograma",
    params: { idPrograma: id},
  });
};


</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Programa de estudio</h2>
      <!-- <CreateButton @click="showSlider(true)" /> -->
    </div>
    <div class="flex  px-6">
      <div class="w-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Agregar Programa
        </h3>
        <hr class="border-t-2  border-cetpro dark:border-cetpro-light mb-4" />
        <ProgramaSlider :show="slider" :programa="sliderData" :ciclo="cicloStore.ciclo" @hide="hideSlider" />
      </div>


      <div class="w-full">
        <Table>
          <THead>
            <Th>Id</Th>
            <Th>Programa</Th>
            <Th>Numero R.D.</Th>
            <Th>Acciones</Th>
          </THead>

          <TBody>
            <Tr v-for="(programa, index) in programaStore.programa" :key="programa.id">
              <Td>{{ index + 1 }}</Td>
              <Td>{{ programa?.año }}</Td>
              <Td>{{ programa?.numero_rd }}</Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, programa)" />
                  <DeleteButton @click="onDelete(programa)" />
                  <div class="flex items-center justify-center space-x-2">
                    <div @click="SeeMore(programa.id)"
                      class="text-blue-500 hover:text-blue-700 font-semibold cursor-pointer border-b-2 border-transparent hover:border-blue-500">
                      Asignar Especialidades
                    </div>

                  </div>
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>

    </div>
  </AuthorizationFallback>
</template>
