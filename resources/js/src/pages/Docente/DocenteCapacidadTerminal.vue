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
import CapacidadTerminalSlider from "../../components/page/CapacidadesTerminales/CapacidadTerminalSlider.vue";
import useCapacidadTerminalStore from "../../store/CapacidadTerminal/UseCapacidadTerminalStore";

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});

const capacidadStore = useCapacidadTerminalStore();

if (!capacidadStore.capacidadTerminal?.length)
  await capacidadStore.loadCapacidadTerminal(props.id);

const { slider, sliderData, showSlider, hideSlider } = useSlider("capacidad-terminal-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteCapacidad, deleting } = useHttpRequest("/capacidad_terminal");

const onDelete = (capacidad) => {
  if (deleting.value) return;
  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteCapacidad(capacidad?.id);
    if (isDeleted) {
      showToast(`Capacidad "${capacidad?.nombre_capacidad}" eliminada exitosamente.`);
      capacidadStore.loadCapacidadTerminal(props.id);
    }

    else{
      showToast("No se pudo Eliminar la capacidad. Intenta nuevamente.", "error");
    }
  });
};
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-capacidad-terminal-docente', 'ver-capacidad-terminal-docente']">

    <div class="flex flex-col lg:flex-row px-6 gap-6">

      <!-- FORMULARIO -->
      <div class="w-full lg:w-1/3 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">

        <CapacidadTerminalSlider
          :show="slider"
          :idGrupo="id"
          :capacidad="sliderData"
          @hide="hideSlider"
        />
      </div>

      <!-- TABLA -->
      <div class="w-full lg:w-2/3">
        <Table>
          <THead>
            <Th>#</Th>
            <Th>Nombre Capacidad</Th>
            <Th>Fecha Inicio</Th>
            <Th>Fecha Fin</Th>
            <Th>Estado</Th>
            <Th>Acciones</Th>
          </THead>

          <TBody>
            <Tr
              v-for="(capacidad, index) in capacidadStore.capacidadTerminal"
              :key="capacidad.id"
            >
              <Td>{{ index + 1 }}</Td>
              <Td>{{ capacidad?.nombre_capacidad }}</Td>
              <Td>{{ capacidad?.fecha_inicio }}</Td>
              <Td>{{ capacidad?.fecha_fin }}</Td>
              <Td>
                <span
                  class="px-2 py-1 rounded text-xs font-semibold"
                  :class="capacidad.status === 1 ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'"
                >
                  {{ capacidad.status === 1 ? 'Activo' : 'Inactivo' }}
                </span>
              </Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, capacidad)" />
                  <DeleteButton @click="onDelete(capacidad)" />
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>

    </div>
  </AuthorizationFallback>
</template>
