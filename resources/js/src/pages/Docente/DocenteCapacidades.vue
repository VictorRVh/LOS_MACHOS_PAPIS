<script setup>
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import CapacidaddesSlider from "../../components/page/Docente/CapacidadesSlider.vue";

import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import useCapacidadesStore from "../../store/CapacidadTerminal/UseCapacidadesStore";
import useSesionesStore from "../../store/Sesion/useSesionStore";

import { ref, onMounted } from "vue";

const props = defineProps({
  id: { type: String, required: true },
  idModulo: { type: String, required: true },
});

const sesionStore = useSesionesStore();
const capacidadStore = useCapacidadesStore();

// Set reactivo para controlar unidades abiertas
const openCapacidades = ref(new Set());

// Función para abrir/cerrar unidades
const toggleCapacidad = (id) => {
  if (openCapacidades.value.has(id)) {
    openCapacidades.value.delete(id);
  } else {
    openCapacidades.value.add(id);
  }
  // Reasignamos para que Vue detecte el cambio
  openCapacidades.value = new Set(openCapacidades.value);
};

// Slider y modales
const { slider, sliderData, showSlider, hideSlider } = useSlider("capacidad-terminal-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteCapacidad, deleting } = useHttpRequest("/capacidad_terminal");

// Función para formatear fechas
const formatFecha = (fecha) =>
  new Date(fecha).toLocaleDateString('es-PE', { day: '2-digit', month: 'long', year: 'numeric' });

// Función para eliminar capacidades
const onDelete = async (capacidad) => {
  if (!canEditCapacidades?.value) {
    showToast('No se puede eliminar esta unidad, la sesión está fuera de rango o finalizada.', 'warning');
    return;
  }

  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteCapacidad(capacidad?.id);
    if (isDeleted) {
      showToast(`Capacidad "${capacidad?.nombre_capacidad}" eliminada exitosamente.`);
      await capacidadStore.loadCapacidadTerminal(props.id);
    } else {
      showToast("No se pudo Eliminar la capacidad. Intenta nuevamente.", "error");
    }
  });
};

// Cargar datos al montar
onMounted(async () => {
  await sesionStore.loadSesion({ id_grupo: props.id, tipo_entrega: 1 });
  if (!capacidadStore.capacidades?.length) {
    await capacidadStore.loadCapacidades(props.id);
  }
});
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-capacidad-terminal-docente', 'ver-capacidad-terminal-docente']">


    <div class="flex flex-col lg:flex-row px-6 gap-6">

      <!-- FORMULARIO -->
      <div class="w-full lg:w-1/3 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">

        <CapacidaddesSlider :show="slider" :idGrupo="id" :idModulo="idModulo" :indexCapacidades="indicesArray"
          :capacidad="sliderData" @hide="hideSlider" />
      </div>

      <!-- TABLA -->
      <div class="w-full lg:w-2/3">
        <Table class="w-full">
          <THead class="hidden">
            <Th></Th>
          </THead>

          <TBody>
            <template v-for="unidad in capacidadStore.capacidades" :key="unidad.id">

              <!-- HEADER UNIDAD -->
              <tr @click="toggleCapacidad(unidad.id)" class="bg-cetpro dark:bg-cetpro-dark cursor-pointer
               hover:bg-cetpro-dark transition-colors border-b">
                <td colspan="8" class="px-4 py-3">
                  <div class="flex items-center justify-between font-bold uppercase text-sm">
                    <span>Unidad {{ unidad.unidad }}</span>

                    <ChevronDownIcon class="h-5 w-5 transition-transform"
                      :class="{ 'rotate-180': openCapacidades.has(unidad.id) }" />
                  </div>
                </td>
              </tr>

              <!-- CONTENIDO UNIDAD -->
              <tr v-if="openCapacidades.has(unidad.id)">
                <td colspan="8" class="bg-white dark:bg-gray-800 px-6 py-4 space-y-4">

                  <!-- CAPACIDAD -->
                  <div class="border-l-4 border-cetpro pl-4">
                    <p class="font-semibold text-sm">
                      {{ unidad.nombre }}
                    </p>

                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                      {{ unidad.descripcion }}
                    </p>
                  </div>

                </td>
              </tr>

            </template>
          </TBody>
        </Table>

      </div>
    </div>
  </AuthorizationFallback>
</template>