<script setup>
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";

import useCapacidadTerminalStore from "../../store/CapacidadTerminal/UseCapacidadTerminalStore";
import useCapacidadTerminalCalificacionesStore from "../../store/Estudiante/UseEstudianteCapacidadGrupoStore";
import { ref, computed } from "vue";
import CapacidadTerminalPlazo from "../../components/page/CapacidadesTerminales/CapacidadTerminalPlazo.vue";
import useHttpRequest from "../../composables/useHttpRequest";
import useModalToast from "../../composables/useModalToast";

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

const { update, deleting } = useHttpRequest("/capacidad_terminal_reactivar");
const { showToast } = useModalToast();

const capacidadStore = useCapacidadTerminalStore();
const calificacionCapacidad = useCapacidadTerminalCalificacionesStore();

const showModal = ref(false);
const capacidadSeleccionada = ref(null);
const accionModal = ref("");

// 🧠 Cargar capacidades
if (!capacidadStore.capacidadTerminal?.length) {
  await capacidadStore.loadCapacidadTerminal(props.id);
  await calificacionCapacidad.loadCapacidadTerminal(props.id);
}

const indexCapacidades = ref([]);
const cantidad = Number(capacidadStore.capacidadTerminal.nro_capacidades || 0);

for (let i = 1; i <= cantidad; i++) {
  const id = i.toString().padStart(2, "0");
  indexCapacidades.value.push({
    id,
    name: `Módulo ${id}`,
  });
}

// 🔢 Calcular módulos asignados
const indicesArray = computed(() => {
  const asignadas =
    capacidadStore.capacidadTerminal?.capacidades.map((ep) => ep?.numero_capacidad) || [];
  return indexCapacidades.value.filter(
    (indice) => !asignadas.includes(indice.id)
  );
});

const abrirModal = (capacidad, accion) => {
  capacidadSeleccionada.value = capacidad;
  accionModal.value = accion;
  showModal.value = true;
};

const reactivarNota = async (capacidad) => {
  try {
    const response = await update(capacidad.id, null)

    showToast("Nota reactivada para edición.", "success");

    await capacidadStore.loadCapacidadTerminal(props.id)

  } catch (e) {
    console.error(e);
    showToast("Error al reactivar la nota.", "error");
  }
};

</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-capacidad-terminal-docente', 'ver-capacidad-terminal-docente']">
    <div class="flex flex-col lg:flex-row px-6 gap-6">
      <!-- 🧾 INFORMACIÓN -->
      <div class="w-full lg:w-1/3 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
          Unidades Didácticas (Vista de Administración)
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Solo lectura — no editable.
        </p>
        <ul class="mt-3 text-sm text-gray-700 dark:text-gray-300 list-disc list-inside">
          <li>Total de unidades: {{ capacidadStore.capacidadTerminal?.capacidades?.length || 0 }}</li>
          <li>Módulos definidos: {{ cantidad }}</li>
        </ul>
      </div>

      <!-- 📋 TABLA -->
      <div class="w-full lg:w-2/3">
        <Table>
          <THead>
            <Th>#</Th>
            <Th>Nombre Capacidad</Th>
            <Th>Fecha Inicio</Th>
            <Th>Fecha Fin</Th>
            <Th>¿Aplazar?</Th>
          </THead>

          <TBody>
            <Tr v-if="!capacidadStore.capacidadTerminal?.capacidades?.length">
              <Td colspan="5" class="text-center text-gray-500">
                No hay capacidades registradas.
              </Td>
            </Tr>

            <Tr v-for="(capacidad, index) in capacidadStore.capacidadTerminal.capacidades" :key="capacidad.id">
              <Td>{{ index + 1 }}</Td>
              <Td>{{ capacidad?.nombre_capacidad }}</Td>
              <Td>{{ capacidad?.fecha_inicio }}</Td>
              <Td>
                <template v-if="capacidad.fecha_aplazada">
                  <span class="line-through text-gray-400">
                    {{ capacidad.fecha_fin }}
                  </span>
                  <br />
                  <span class="text-blue-600 font-semibold">
                    {{ capacidad.fecha_aplazada }}
                  </span>
                </template>

                <template v-else>
                  {{ capacidad.fecha_fin }}
                </template>
              </Td>
              <Td>
                <!-- Solo reactiva nota -->
              <td>
                <!-- Reactivar Nota -->
                <button v-if="capacidad.accion_disponible === 'reactivar'" @click="reactivarNota(capacidad)" class="px-3 py-1.5 rounded-md text-sm font-medium 
           bg-blue-600 text-white hover:bg-blue-700 
           dark:bg-blue-500 dark:hover:bg-blue-600 
           transition-all duration-150 shadow-sm 
           flex items-center gap-1">
                  Reactivar
                </button>

                <!-- Aplazar Fecha -->
                <button v-else-if="capacidad.accion_disponible === 'aplazar'" @click="abrirModal(capacidad, 'aplazar')"
                  class="px-3 py-1.5 rounded-md text-sm font-medium 
           bg-red-500 text-white hover:bg-red-600 
           dark:bg-red-400 dark:hover:bg-red-500
           transition-all duration-150 shadow-sm 
           flex items-center gap-1 mt-1">
                  Aplazar
                </button>

                <button v-else-if="capacidad.accion_disponible === 'rectificar'"
                  @click="abrirModal(capacidad, 'rectificar')" class="px-3 py-1.5 rounded-md text-sm font-medium 
           bg-amber-500 text-white hover:bg-amber-600 
           dark:bg-amber-400 dark:hover:bg-amber-500
           transition-all duration-150 shadow-sm 
           flex items-center gap-1 mt-1">
                  Rectificar
                </button>

              </td>

              </Td>

            </Tr>
          </TBody>
        </Table>
        <CapacidadTerminalPlazo :show="showModal" :capacidad="capacidadSeleccionada" :accion="accionModal"
          :load="props.id" @hided="showModal = false" />

      </div>
    </div>
  </AuthorizationFallback>
</template>
