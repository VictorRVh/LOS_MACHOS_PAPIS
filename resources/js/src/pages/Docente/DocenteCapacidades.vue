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
import CapacidadTerminalSlider from "../../components/page/Docente/CapacidadesSlider.vue";
import useCapacidadTerminalStore from "../../store/CapacidadTerminal/UseCapacidadTerminalStore";
import useCapacidadTerminalCalificacionesStore from "../../store/Estudiante/UseEstudianteCapacidadGrupoStore";
import { ref, computed, onMounted } from "vue";
import useSesionesStore from "../../store/Sesion/useSesionStore";

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  idModulo: {
    type: String,
    required: true,
  },
});

const sesionStore = useSesionesStore();
const capacidadStore = useCapacidadTerminalStore();
const calificacionCapacidad = useCapacidadTerminalCalificacionesStore();

onMounted(async () => {
  await sesionStore.loadSesion({
    id_grupo: props.id,
    tipo_entrega: 1
  });
})

if (!capacidadStore.capacidadTerminal?.length)
  await capacidadStore.loadCapacidadTerminal(props.id);

const { slider, sliderData, showSlider, hideSlider } = useSlider("capacidad-terminal-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteCapacidad, deleting } = useHttpRequest("/capacidad_terminal");

const sesion = computed(() => sesionStore?.sesion);

const hasAplazamiento = computed(() => {
  return !!sesion.value?.fecha_aplazada;
});

const formatFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-PE', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  });
};

const canEditCapacidades = computed(() => {
  const sesion = sesionStore?.sesion;
  if (!sesion) return false;

  const ahora = new Date();

  const fechaInicio = new Date(sesion.fecha_inicio);

  const fechaLimite = sesion.fecha_aplazada
    ? new Date(sesion.fecha_aplazada)
    : new Date(sesion.fecha_fin);

  if (sesion.estado === 4) return false;

  return ahora >= fechaInicio && ahora <= fechaLimite;
});

const onDelete = (capacidad) => {
  if (!canEditCapacidades.value) {
    // console.log('NO SE PUEDE ELIMINAR')
    showToast('No se puede eliminar esta unidad, la sesión está fuera de rango o finalizada.', 'warning');
    return;
  }

  if (deleting.value) return;
  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteCapacidad(capacidad?.id);
    if (isDeleted) {
      showToast(`Capacidad "${capacidad?.nombre_capacidad}" eliminada exitosamente.`);
      capacidadStore.loadCapacidadTerminal(props.id);
      calificacionCapacidad.loadCapacidadTerminal(props.id);
    }

    else {
      showToast("No se pudo Eliminar la capacidad. Intenta nuevamente.", "error");
    }
  });
};

const indexCapacidades = ref([]);

const cantidad = Number(capacidadStore.capacidadTerminal.nro_capacidades || 0);

for (let i = 1; i <= cantidad; i++) {
  const id = i.toString().padStart(2, "0");
  indexCapacidades.value.push({
    id,
    name: `Unidad Didactica ${id}`,
  });
}

const indicesArray = computed(() => {
  // Mapea los módulos ya asignados
  const asignadas = capacidadStore.capacidadTerminal?.capacidades.map(
    (ep) => ep?.numero_capacidad
  ) || [];

  // Si estoy editando
  const currentId = sliderData.value?.numero_capacidad;

  return indexCapacidades.value.filter(
    (indice) => !asignadas.includes(indice.id) || indice.id === currentId
  );

});

const estadoTexto = computed(() => {
  if (!sesionStore?.sesion) return 'Sin programación'
  switch (sesionStore?.sesion?.estado) {
    case 0: return 'Pendiente'
    case 1: return 'En curso'
    case 2: return 'En curso'
    case 3: return 'En curso'
    case 4: return 'Finalizada'
    default: return 'Desconocido'
  }
})

</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-capacidad-terminal-docente', 'ver-capacidad-terminal-docente']">

    

    <div class="flex flex-col lg:flex-row px-6 gap-6">

      <!-- FORMULARIO -->
      <div class="w-full lg:w-1/3 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">

        <CapacidadTerminalSlider :show="slider" :idGrupo="id" :indexCapacidades="indicesArray" :capacidad="sliderData"
          @hide="hideSlider" />
      </div>

      <!-- TABLA -->
      <div class="w-full lg:w-2/3">
        <Table>
          <THead>
            <Th>#</Th>
            <Th>Nombre Capacidad</Th>
            <Th>Fecha Inicio</Th>
            <Th>Fecha Fin</Th>
            <Th>Acciones</Th>
          </THead>

          <TBody>
            <Tr v-for="(capacidad, index) in capacidadStore.capacidadTerminal.capacidades" :key="capacidad.id">
              <Td>{{ index + 1 }}</Td>
              <Td>{{ capacidad?.nombre_capacidad }}</Td>
              <Td>{{ capacidad?.fecha_inicio }}</Td>
              <Td>{{ capacidad?.fecha_fin }}</Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, capacidad)" :disabled="!canEditCapacidades" />
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