<script setup>
import { ref, computed, watch } from "vue";
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
import useEstudianteCapacidadTerminalStore from "../../store/Estudiante/UseEstudianteCapacidadGrupoStore";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useTableData from "../../composables/tabla/useTableData";
import useCapacidadTerminalStore from "../../store/CapacidadTerminal/UseCapacidadTerminalStore";

const props = defineProps({
  id: { type: String, required: true },
});

// --- Stores ---
const userStore = useStudentsStore();
const capacidadStore = useEstudianteCapacidadTerminalStore();
const capacidadTerminalStore = useCapacidadTerminalStore();
const { showToast } = useModalToast();
const { slider, showSlider, hideSlider } = useSlider("capacidad-terminal-notas");

// --- Data ---
const capacidadSeleccionada = ref(null);
const estadoCapacidad = ref(null);

// --- Carga inicial ---
if (!userStore.estudiantes?.length) await userStore.loadEstudiantes(props.id);
if (!capacidadStore?.capacidadTerminal?.capacidades?.length)
  await capacidadStore.loadCapacidadTerminal(props.id);

// --- Reactividad ---
const { estudiantes } = storeToRefs(userStore);
const { capacidadTerminal } = storeToRefs(capacidadStore);

watch(capacidadSeleccionada, async (nuevaCapacidad) => {
  console.log('capacidad', capacidadSeleccionada)
  if (nuevaCapacidad) {
    try {
      await capacidadTerminalStore.verificarEstadoCapacidad(nuevaCapacidad);
      estadoCapacidad.value = capacidadTerminalStore.estadoCapacidad
    } catch (error) {
      console.error('Error al verificar capacidad:', error);
    }
  }
});

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
const verNotasUnidad = async () => {
  if (!capacidadSeleccionada.value) {
    showToast("Selecciona una capacidad terminal primero.", "warning");
    return;
  }

  // Verificar estado actualizado
  if (capacidadTerminalStore.capacidadTerminalInfoLoading) {
    showToast("Verificando estado de la capacidad...", "info");
    return;
  }

  // Validar si puede subir notas
  if (!capacidadTerminalStore.puedeSubirNotas()) {
    showToast(capacidadTerminalStore.getMensajeEstado(), "warning");
    return;
  }

  // Mostrar fecha límite
  const fechaLimite = capacidadTerminalStore.getFechaLimite();
  if (fechaLimite) {
    const fecha = new Date(fechaLimite);
    const fechaFormateada = fecha.toLocaleString('es-PE', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
    showToast(`Puede subir notas hasta: ${fechaFormateada}`, "info", 5000);
  }

  showSlider(true, {
    capacidad: capacidadSeleccionada.value,
    idGroup: props.id,
    idType: "capacidad",
    estadoCapacidad: estadoCapacidad.value, // ✅ Pasar estado al slider
  });
};

const onHideSlider = async () => {
  hideSlider();
  capacidadSeleccionada.value = null;
  estadoCapacidad.value = null;
  await userStore.loadEstudiantes(props.id);
};

const getEstadoCapacidadClass = computed(() => {
  if (!estadoCapacidad.value) return '';

  const status = estadoCapacidad.value.status;
  if (status === 1) return 'border-green-500 bg-green-50 dark:bg-green-900/20';
  if (status === 0) return 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20';
  if (status === 4) return 'border-red-500 bg-red-50 dark:bg-red-900/20';
  return '';
});

</script>

<template>
  <AuthorizationFallback :permissions="[
    'todo-acceso-capacidad-terminal-notas-docente',
    'ver-capacidad-terminal-notas-docente',
  ]">
    <div v-if="!slider" class="space-y-4">
      <!-- Cabecera -->
      <div class="flex justify-between items-center">
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-300">
          Registro y visualización de notas
        </h3>
      </div>

      <!-- Filtros -->
      <div class="flex justify-between items-center gap-4">
        <div class="w-2/5 relative">
          <BaseSelectGrupo v-model="capacidadSeleccionada" :options="opcionesCapacidades" label="nombre_capacidad"
            placeholder="Seleccione una capacidad terminal"
            :class="['transition-all duration-200', getEstadoCapacidadClass]" />

          <!-- ✅ NUEVO: Indicador de estado -->
          <div v-if="estadoCapacidad && capacidadSeleccionada" class="mt-1 text-xs flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" :class="{
              'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': estadoCapacidad.status === 1,
              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': estadoCapacidad.status === 0,
              'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': estadoCapacidad.status === 4,
            }">
              {{ estadoCapacidad.status_texto }}
            </span>
            <span v-if="estadoCapacidad.puede_subir_notas" class="text-gray-600 dark:text-gray-400">
              Fecha límite: {{ new Date(estadoCapacidad.fecha_limite_subida).toLocaleDateString('es-PE') }}
            </span>
          </div>
        </div>

        <BaseButton :title="'Asignar Nota'" @click="verNotasUnidad"
          :disabled="!capacidadSeleccionada || capacidadTerminalStore.capacidadTerminalInfoLoading || !capacidadTerminalStore.puedeSubirNotas()"
          :class="{ 'opacity-50 cursor-not-allowed': !capacidadSeleccionada || !capacidadTerminalStore.puedeSubirNotas() }" />

        <SearchBar class="ml-auto" :totalResultados="estudiantesOrdenados.length" @search="filtrarEstudiantes" />
      </div>

      <!-- ✅ NUEVO: Alert de estado -->
      <div v-if="capacidadSeleccionada && estadoCapacidad && !estadoCapacidad.puede_subir_notas"
        class="p-4 rounded-lg border" :class="{
          'bg-yellow-50 border-yellow-200 text-yellow-800 dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-200': estadoCapacidad.status === 0,
          'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-200': estadoCapacidad.status === 4,
        }">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
              clip-rule="evenodd" />
          </svg>
          <p class="text-sm font-medium">{{ estadoCapacidad.mensaje }}</p>
        </div>
      </div>

      <!-- Tabla -->
      <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">
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
            <!-- Si está retirado -->
            <template v-if="est.matriculado === 0">
              <Td>{{ (pagina - 1) * itemsPorPagina + index + 1 }}</Td>
              <Td class="font-medium whitespace-nowrap">{{ est.apellidos_nombres }}</Td>
              <Td :colspan="lengthUnit + 3" class="text-center">
                <span class="px-3 py-1 rounded bg-red-100 text-red-700 font-semibold text-sm uppercase tracking-wide">
                  RETIRADO POR INASISTENCIA
                </span>
              </Td>
            </template>

            <!-- Si está activo -->
            <template v-else>
              <Td>{{ (pagina - 1) * itemsPorPagina + index + 1 }}</Td>
              <Td class="font-medium whitespace-nowrap">{{ est.apellidos_nombres }}</Td>

              <!-- Notas -->
              <Td v-for="(cap, i) in est.capacidades" :key="i" class="text-center"
                :class="getNotaClass(cap.nota_capacidad)">
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
            </template>
          </Tr>

        </TBody>
      </Table>
    </div>

    <!-- Slider -->
    <NotasEstudianteSlider v-if="slider" :show="slider" :idgroup="props.id" :id-capacidad-note="capacidadSeleccionada"
      :idType="'capacidad'" :estado-capacidad="estadoCapacidad" @hide="onHideSlider" />
  </AuthorizationFallback>
</template>

<style scoped>
.nota-vacia {
  @apply text-gray-400 italic;
}
</style>
