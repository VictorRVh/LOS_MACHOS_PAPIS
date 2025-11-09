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

const capacidadStore = useCapacidadTerminalStore();
const calificacionCapacidad = useCapacidadTerminalCalificacionesStore();

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
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-capacidad-terminal-docente', 'ver-capacidad-terminal-docente']">
    <div class="flex flex-col lg:flex-row px-6 gap-6">
      <!-- 🧾 INFORMACIÓN -->
      <div class="w-full lg:w-1/3 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
          Capacidades Terminales (Vista de Administración)
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Solo lectura — no editable.
        </p>
        <ul class="mt-3 text-sm text-gray-700 dark:text-gray-300 list-disc list-inside">
          <li>Total de capacidades: {{ capacidadStore.capacidadTerminal?.capacidades?.length || 0 }}</li>
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
            <Th>Estado</Th>
          </THead>

          <TBody>
            <Tr
              v-if="!capacidadStore.capacidadTerminal?.capacidades?.length"
            >
              <Td colspan="5" class="text-center text-gray-500">
                No hay capacidades registradas.
              </Td>
            </Tr>

            <Tr
              v-for="(capacidad, index) in capacidadStore.capacidadTerminal.capacidades"
              :key="capacidad.id"
            >
              <Td>{{ index + 1 }}</Td>
              <Td>{{ capacidad?.nombre_capacidad }}</Td>
              <Td>{{ capacidad?.fecha_inicio }}</Td>
              <Td>{{ capacidad?.fecha_fin }}</Td>
              <Td>
                <span
                  class="px-2 py-1 rounded text-xs font-semibold"
                  :class="capacidad.status === 1
                    ? 'bg-green-200 text-green-800'
                    : 'bg-red-200 text-red-800'"
                >
                  {{ capacidad.status === 1 ? "Activo" : "Inactivo" }}
                </span>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>
    </div>
  </AuthorizationFallback>
</template>
