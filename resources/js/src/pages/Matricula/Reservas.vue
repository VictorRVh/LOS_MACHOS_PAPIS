<script setup>
import { ref, computed, onMounted } from "vue";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import SearchBar from "../../components/head_table/headSearch.vue";
import MenuTable from "../../components/table/MenuTable.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import useMatriculaStore from "../../store/Matricula/useMatriculaStore";
import useTableData from "../../composables/tabla/useTableData";
import useHttpRequest from "../../composables/useHttpRequest";
import { generatePdfReservaMatricula } from "../../pdf/reserMatricula";
import useModalToast from "../../composables/useModalToast";
import BaseButton from "../../components/ui/Button.vue"

const { destroy: deleteReserva, deleting } = useHttpRequest("/reserva");
const { showToast, showConfirmModal } = useModalToast();

const matriculaStore = useMatriculaStore();
const loading = ref(true);


const tipoReservaActual = ref(1);
const cargarReservas = async (tipo) => {
  tipoReservaActual.value = tipo;
  loading.value = true;
  await matriculaStore.loadListaReserva(tipo);
  loading.value = false;
};

// onMounted inicial
onMounted(async () => {
  await cargarReservas(1); // Cargar activas por defecto
});

// Computed de estudiantes
const estudiantes = computed(
  () => matriculaStore.matriculasReservadas?.estudiantes ?? []
);


const botonTexto = computed(() =>
  tipoReservaActual.value === 1
    ? "Ver reservas utilizadas"
    : "Ver reservas activas"
);

const botonClase = computed(() =>
  tipoReservaActual.value === 1
    ? "bg-cetpro hover:bg-cetpro-light active:bg-cetpro-light"
    : "bg-green-600 hover:bg-green-700 active:bg-green-700"
);

const botonAccion = () =>
  tipoReservaActual.value === 1
    ? cargarReservas(3)
    : cargarReservas(1);


// Composable para filtrado, orden y paginación
const {
  query,
  orderBy,
  orderDirection,
  pagina,
  itemsPorPagina,
  paginados,
  totalPaginas,
  ordenados,
  filtrar
} = useTableData(estudiantes, {
  defaultOrderBy: "apellidos_nombres",
  searchFields: ["apellidos_nombres", "nro_documento", "especialidad", "modulo"]
});

// Descarga PDF
const descargarReserva = (reserva) => {
  generatePdfReservaMatricula(reserva);
};

// Utilizar reserva
const UtilizarReserva = (reserva) => {
  if (deleting.value) return;

  showConfirmModal(
    {
      title: "Confirmar acción",
      message: `¿Deseas utilizar la reserva de "${reserva?.apellidos_nombres}"?`,
      actionButton: {
        class: "bg-yellow-500 hover:bg-yellow-600 text-black dark:text-black",
        text: "Sí, utilizar",
      },
      returnButton: {
        class: "bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600",
        text: "Cancelar",
      },
    },
    async (confirmed) => {
      if (!confirmed) return;

      try {
        const isUsed = await deleteReserva(reserva.id_matricula);

        if (isUsed) {
          showToast(
            `Reserva de "${reserva?.apellidos_nombres}" utilizada correctamente.`,
            "success"
          );

          await cargarReservas(tipoReservaActual.value);
          return;
        }

        showToast("No se pudo utilizar la reserva.", "warning");
        await cargarReservas(tipoReservaActual.value);

      } catch (error) {
        console.error(error);
        const msg =
          error.response?.data?.message ||
          "Ocurrió un error al intentar utilizar la reserva.";
        showToast(msg, "error");
      }
    }
  );
};
</script>
<template>

  <AuthorizationFallback :permissions="['todo-acceso-matrículas', 'ver-matrículas-reserva']">
    <div class="w-full space-y-4 px-3">

      <!-- NUEVO: Botones + buscador -->
      <div class="flex-between my-5 items-center gap-4">

        <!-- BOTONES DE FILTRO -->
        <div class="flex gap-3">
          <BaseButton :loading="loading" :disabled="loading" :title="botonTexto" loadingTitle="Cargando..."
            :class="botonClase" @click="botonAccion()" />

        </div>

        <SearchBar :totalResultados="ordenados.length" :campoOrden="'apellidos_nombres'" @search="filtrar" />


      </div>

      <!-- TABLA -->
      <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">
        <THead>
          <Th>N°</Th>
          <Th>Estudiante</Th>
          <Th>DNI</Th>
          <Th>Especialidad - Módulo</Th>
          <Th>Turno</Th>
          <Th>Sección</Th>
          <Th>Fch. Reserva</Th>
          <Th class="text-center">Acciones</Th>
        </THead>

        <TBody :filas="ordenados.length">
          <Tr v-for="(reserva, index) in paginados" :key="reserva.id_matricula">
            <Td>{{ (pagina - 1) * itemsPorPagina + index + 1 }}</Td>
            <Td>{{ reserva.apellidos_nombres }}</Td>
            <Td>{{ reserva.nro_documento }}</Td>
            <Td>{{ reserva.especialidad }} - {{ reserva.modulo }}</Td>
            <Td>{{ reserva.turno }}</Td>
            <Td>{{ reserva.seccion }}</Td>
            <Td>{{ reserva.fecha_reserva ?? "---" }}</Td>

            <Td class="text-center text-gray-600 dark:text-gray-200">
              <MenuTable
                :actions="{ view: false, edit: false, delete: false, download: true, custom1: reserva.reserva != 3 }"
                :labels="{
                  custom1: 'Utilizar reserva',
                  download: 'Descargar reserva',
                }" @download="descargarReserva(reserva)" @custom1="UtilizarReserva(reserva)" />
            </Td>
          </Tr>

          <Tr v-if="ordenados.length === 0 && !loading">
            <Td colspan="8" class="text-center py-4">No se encontraron resultados.</Td>
          </Tr>

          <Tr v-if="loading">
            <Td colspan="8" class="text-center py-4">Cargando reservas...</Td>
          </Tr>
        </TBody>
      </Table>
    </div>
  </AuthorizationFallback>
</template>
