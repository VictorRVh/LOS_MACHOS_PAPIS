<script setup>
import { ref, computed } from "vue";
import { storeToRefs } from "pinia";

import SearchBar from "../../components/head_table/headSearch.vue";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import BaseButton from "../../components/ui/Button.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import BaseSelectGrupo from "../../components/ui/BaseSelectGrupo.vue";
import NotasEstudianteSlider from "../../components/page/CapacidadesTerminales/NotasCapacidadTerminalSlider.vue";

import useStudentsStore from "../../store/Estudiante/UseEstudianteGrupoStore";
import useCapacidadTerminalStore from "../../store/Estudiante/UseEstudianteCapacidadGrupoStore";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useTableData from "../../composables/tabla/useTableData";

const props = defineProps({
  id: { type: String, required: true },
});

// --- Stores ---
const userStore = useStudentsStore();
const capacidadStore = useCapacidadTerminalStore();
const { showToast } = useModalToast();
const { slider, showSlider, hideSlider } = useSlider("capacidad-terminal-notas");

// --- Data ---
const capacidadSeleccionada = ref(null);

// --- Carga inicial ---
if (!userStore.estudiantes?.length) await userStore.loadEstudiantes(props.id);
if (!capacidadStore?.capacidadTerminal?.capacidades?.length)
  await capacidadStore.loadCapacidadTerminal(props.id);

// --- Reactividad ---
const { estudiantes } = storeToRefs(userStore);
const { capacidadTerminal } = storeToRefs(capacidadStore);

// --- Cantidad de capacidades ---
const lengthUnit = computed(
  () =>
    capacidadTerminal.value?.cantidad_capacidades ??
    capacidadTerminal.value?.capacidades?.length ??
    0
);

const opcionesCapacidades = computed(() => capacidadTerminal.value?.capacidades ?? []);

// --- Normalizar capacidades ---
const estudiantesNormalizados = computed(() => {
  const n = Number(lengthUnit.value) || 0;
  return (estudiantes.value ?? []).map((est) => {
    const capacidades = Array.from({ length: n }, (_, i) => {
      const cap =
        (est.capacidades && est.capacidades[i]) ??
        { nota_capacidad: null, id_capacidad: `empty-${i}` };
      return { ...cap, nota_capacidad: cap?.nota_capacidad ?? null };
    });
    return { ...est, capacidades };
  });
});

// --- Tabla: búsqueda y paginación ---
const {
  pagina,
  itemsPorPagina,
  paginados: estudiantesPaginados,
  totalPaginas,
  ordenados: estudiantesOrdenados,
  filtrar: filtrarEstudiantes,
} = useTableData(estudiantesNormalizados, {
  defaultOrderBy: "apellidos_nombres",
  searchFields: ["apellidos_nombres", "dni", "apellidos", "nombres"],
});

// --- Funciones utilitarias ---
const getNotaClass = (nota) =>
  nota < 11
    ? "text-red-600 dark:text-red-500 font-bold"
    : "text-green-600 dark:text-green-300 font-bold";

const getEstadoClass = (estado) =>
  estado === "APROBADO"
    ? "text-green-600 dark:text-green-400"
    : "text-red-600 dark:text-red-400";

// --- Resumen: total, promedio, estado ---
const getResumenNotas = (est) => {
  const total = est.capacidades.reduce(
    (sum, c) => sum + Number(c.nota_capacidad ?? 0),
    0
  );
  const promedio = est.capacidades.length
    ? total / est.capacidades.length
    : 0;
  const estado = promedio >= 11 ? "APROBADO" : "DESAPROBADO";

  const promedioTexto = Number.isInteger(promedio)
    ? promedio === 0
      ? "00"
      : promedio.toString().padStart(2, "0")
    : promedio.toFixed(1).replace(/\.0$/, "").padStart(4, "0");

  return {
    total: total.toString().padStart(2, "0"),
    promedio: promedioTexto,
    estado,
    promedioClass:
      promedio < 11
        ? "text-red-600 dark:text-red-500 font-bold"
        : "text-green-600 dark:text-green-400 font-bold",
    estadoClass: getEstadoClass(estado),
  };
};

// --- Slider ---
const verNotasUnidad = () => {
  if (!capacidadSeleccionada.value) {
    showToast("Selecciona una capacidad terminal primero.", "warning");
    return;
  }
  showSlider(true, {
    capacidad: capacidadSeleccionada.value,
    idGroup: props.id,
    idType: "capacidad",
  });
};

const onHideSlider = async () => {
  hideSlider();
  capacidadSeleccionada.value = null;
  await userStore.loadEstudiantes(props.id);
};
</script>

<template>
  <AuthorizationFallback
    :permissions="[
      'todo-acceso-capacidad-terminal-notas-docente',
      'ver-capacidad-terminal-notas-docente',
    ]"
  >
    <div v-if="!slider" class="space-y-4">
      <!-- Cabecera -->
      <div class="flex justify-between items-center">
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-300">
          Registro y visualización de notas
        </h3>
      </div>

      <!-- Filtros -->
      <div class="flex justify-between items-center gap-4">
        <BaseSelectGrupo
          v-model="capacidadSeleccionada"
          :options="opcionesCapacidades"
          label="nombre_capacidad"
          placeholder="Seleccione una capacidad terminal"
          class="w-2/5"
        />
        <BaseButton :title="'Asignar Nota'" @click="verNotasUnidad" />
        <SearchBar
          class="ml-auto"
          :totalResultados="estudiantesOrdenados.length"
          @search="filtrarEstudiantes"
        />
      </div>

      <!-- Tabla -->
      <Table
        :paginacion="true"
        :current-page="pagina"
        :total-pages="totalPaginas"
        @changePage="pagina = $event"
      >
        <THead>
          <Th>#</Th>
          <Th>Apellidos y Nombres</Th>
          <Th v-for="i in lengthUnit" :key="i">CT{{ i }}</Th>
          <Th>PUNTAJE</Th>
          <Th>PROMEDIO</Th>
          <Th>A-D-R</Th>
        </THead>

        <TBody>
          <Tr v-for="(est, index) in estudiantesPaginados" :key="est.id_estudiante ?? index">
            <Td>{{ (pagina - 1) * itemsPorPagina + index + 1 }}</Td>
            <Td class="font-medium whitespace-nowrap">{{ est.apellidos_nombres }}</Td>

            <!-- Notas -->
            <Td
              v-for="(cap, i) in est.capacidades"
              :key="i"
              class="text-center"
              :class="getNotaClass(cap.nota_capacidad)"
            >
              {{ cap.nota_capacidad ?? "--" }}
            </Td>

            <!-- Totales -->
            <template v-if="getResumenNotas(est)">
              <Td class="text-center font-semibold">{{ getResumenNotas(est).total }}</Td>
              <Td class="text-center font-semibold" :class="getResumenNotas(est).promedioClass">
                {{ getResumenNotas(est).promedio }}
              </Td>
              <Td class="font-bold text-center" :class="getResumenNotas(est).estadoClass">
                {{ getResumenNotas(est).estado }}
              </Td>
            </template>
          </Tr>
        </TBody>
      </Table>
    </div>

    <!-- Slider -->
    <NotasEstudianteSlider
      v-if="slider"
      :show="slider"
      :idgroup="props.id"
      :id-capacidad-note="capacidadSeleccionada"
      :idType="'capacidad'"
      @hide="onHideSlider"
    />
  </AuthorizationFallback>
</template>

<style scoped>
.nota-vacia {
  @apply text-gray-400 italic;
}
</style>
