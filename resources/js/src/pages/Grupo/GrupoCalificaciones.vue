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

import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";

import useStudentsStore from "../../store/Estudiante/UseEstudianteGrupoStore";
import useCapacidadTerminalStore from "../../store/Estudiante/UseEstudianteCapacidadGrupoStore";
import useTableData from "../../composables/tabla/useTableData";

const props = defineProps({
  id: { type: String, required: true },
});

// --- Stores ---
const userStore = useStudentsStore();
const capacidadStore = useCapacidadTerminalStore();

// --- Carga inicial ---
if (!userStore.estudiantes?.length) {
  await userStore.loadEstudiantes(props.id);
}
if (!capacidadStore?.capacidadTerminal?.capacidades?.length) {
  await capacidadStore.loadCapacidadTerminal(props.id);
}

// --- Reactividad segura ---
const { estudiantes } = storeToRefs(userStore);
const { capacidadTerminal } = storeToRefs(capacidadStore);

// --- Cantidad de capacidades ---
const lengthUnit = computed(
  () =>
    capacidadTerminal.value?.cantidad_capacidades ??
    capacidadTerminal.value?.capacidades?.length ??
    0
);

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

// --
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
    ? "text-red-600 dark:text-red-500  font-bold"
    : "text-green-600 dark:text-green-300 font-bold";

const getEstadoClass = (estado) =>
  estado === "APROBADO"
    ? "text-green-600 dark:text-green-400"
    : "text-red-600 dark:text-red-400";

// --- 
const getResumenNotas = (est) => {
  // Notas de capacidades
  const notas = (est.capacidades || []).map((c) => {
    const value = c?.nota_capacidad;
    return value !== null && value !== "" && !isNaN(value) ? Number(value) : 0;
  });

  // ✅ AGREGAR EXPERIENCIA (EFSRT / PRÁCTICAS)
  const notaExperiencia =
    est.nota_experiencia !== null &&
    est.nota_experiencia !== "" &&
    !isNaN(est.nota_experiencia)
      ? Number(est.nota_experiencia)
      : 0;

  notas.push(notaExperiencia);

  // Promedio
  const total = notas.reduce((sum, n) => sum + n, 0);
  const promedio = notas.length ? total / notas.length : 0;

  const estado = promedio >= 11 ? "APROBADO" : "DESAPROBADO";

  return {
    // ✅ IMPORTANTE: ahora sí existe
    nota_experiencia: notaExperiencia.toString().padStart(2, "0"),

    total: total.toString().padStart(2, "0"),

    promedio:
      Number.isInteger(promedio)
        ? promedio.toString().padStart(2, "0")
        : promedio.toFixed(1),

    estado,

    promedioClass:
      promedio < 11
        ? "text-red-600 dark:text-red-500 font-bold"
        : "text-green-600 dark:text-green-400 font-bold",

    estadoClass:
      estado === "APROBADO"
        ? "text-green-600 dark:text-green-400"
        : "text-red-600 dark:text-red-400",
  };
};
</script>

<template>
  <AuthorizationFallback :permissions="[
    'todo-acceso-unidad-didáctica-notas-docente',
    'ver-unidad-didáctica-notas-docente',
  ]">
    <div class="space-y-4">
      <!-- Cabecera -->
      <div class="flex justify-between items-center">
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-300">
          Lista de estudiantes con notas
        </h3>


        
        <div class="flex justify-end">
          <SearchBar :totalResultados="estudiantesOrdenados.length" @search="filtrarEstudiantes" />
        </div>
      </div>

      <!-- Tabla -->
      <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">
        <THead>
          <Th>#</Th>
          <Th>Apellidos y Nombres</Th>
          <Th v-for="i in lengthUnit" :key="i" class="text-center">UD{{ i }}</Th>
          <Th class="text-center" title="Experiencia formativa en situaciones reales de trabajo">EFSRT</Th>
          <Th class="text-center">PUNTAJE</Th>
          <Th class="text-center">PROMEDIO</Th>
          <Th class="text-center">A-D-R</Th>
        </THead>

        <TBody>
          <Tr v-for="(est, index) in estudiantesPaginados" :key="est.id_estudiante ?? index">
            <!-- N° -->
            <Td>{{ (pagina - 1) * itemsPorPagina + index + 1 }}</Td>

            <!-- Nombre -->
            <Td class="whitespace-nowrap">
              {{ est.apellidos_nombres }}
            </Td>

            <!-- SI ESTÁ RETIRADO -->
            <template v-if="est.matriculado === 2">
              <Td :colspan="lengthUnit + 3" class="text-center text-red-600 font-semibold">
                RETIRADO POR INASISTENCIA
              </Td>
            </template>

            <!-- SI NO ESTÁ RETIRADO -->
            <template v-else>
              <!-- Notas por CT -->
              <Td v-for="(cap, i) in est.capacidades" :key="i" class="text-center"
                :class="getNotaClass(cap.nota_capacidad)">
                {{ cap.nota_capacidad ?? "--" }}
              </Td>
              <Td class="text-center font-semibold">{{ getResumenNotas(est).nota_experiencia }}</Td>
              <!-- Resumen -->
              <template v-if="getResumenNotas(est)">
                <Td class="text-center font-semibold">
                  {{ getResumenNotas(est).total }}
                </Td>

                <Td class="text-center font-semibold" :class="getResumenNotas(est).promedioClass">
                  {{ getResumenNotas(est).promedio }}
                </Td>

                <Td class="text-center" :class="getResumenNotas(est).estadoClass">
                  {{ getResumenNotas(est).estado }}
                </Td>
              </template>
            </template>
          </Tr>
        </TBody>
      </Table>
    </div>
  </AuthorizationFallback>
</template>

<style scoped>
.nota-vacia {
  @apply text-gray-400 italic;
}
</style>
