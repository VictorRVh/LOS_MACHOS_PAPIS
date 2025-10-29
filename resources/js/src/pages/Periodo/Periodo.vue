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

import periodoSlider from "../../components/page/Periodo/PeriodoSlider.vue";

import usePeriodosStore from "../../store/Periodo/usePeriodoStore";

const periodosStore = usePeriodosStore();

if (!periodosStore.periodos.length) await periodosStore.loadPeriodos();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deletePeriodo, deleting } = useHttpRequest("/periodo");

const onDelete = (periodo) => {

  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deletePeriodo(periodo?.id);
    if (isDeleted) {
      showToast(`Periodo "${periodo?.nombre_periodo}" eliminado exitosamente...`);
      periodosStore.loadPeriodos();

    }
  });
};

</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-periodos', 'ver-periodos']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Periodos</h2>
    </div>
    <div class="flex flex-col lg:flex-row px-6 gap-6">
      <div class="w-full lg:w-1/3 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Agregar Periodo
        </h3>
        <hr class="border-t-2  border-cetpro dark:border-cetpro-light mb-4" />
        <periodoSlider :show="slider" :periodo="sliderData" @hide="hideSlider" />
      </div>
      <div class="w-full lg:w-2/3">
        <Table>
          <THead>
            <Th>Id</Th>
            <Th>Periodo</Th>
            <Th>Estado</Th>
            <Th>Acciones</Th>
          </THead>

          <TBody>
            <Tr v-for="(periodo, index) in periodosStore.periodos" :key="periodo.id">
              <Td>{{ index + 1 }}</Td>
              <Td>{{ periodo?.nombre_periodo }}</Td>
              <Td>
                <span :class="periodo.status === 1
                  ? 'text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-900'
                  : 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900'
                  " class="px-2 py-1 text-xs rounded-md font-semibold inline-flex items-center gap-1">
                  <span v-if="periodo.status === 1"> Activo ✓ </span>
                  <span v-else="periodo.status === 0"> Inactivo X </span>
                </span>
              </Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, periodo)" />
                  <DeleteButton @click="onDelete(periodo)" />
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>

    </div>
  </AuthorizationFallback>
</template>