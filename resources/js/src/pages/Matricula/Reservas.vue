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

import useMatriculaStore from "../../store/Matricula/useMatriculaStore";
import useTableData from "../../composables/tabla/useTableData";
import useHttpRequest from "../../composables/useHttpRequest";
import { generatePdfReservaMatricula } from "../../pdf/reserMatricula"; // Ajusta la ruta
import useModalToast from "../../composables/useModalToast";

const { destroy: deleteReserva, deleting } = useHttpRequest("/reserva");

const { showToast, showConfirmModal } = useModalToast();

const matriculaStore = useMatriculaStore();
const loading = ref(true);

// Cargar estudiantes con reserva
onMounted(async () => {
  loading.value = true;
  await matriculaStore.loadListaReserva();
  loading.value = false;
});

// Computed de estudiantes
const estudiantes = computed(() => matriculaStore.matriculasReservadas?.estudiantes ?? []);

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

// Acciones simuladas
const descargarReserva = (reserva) => {
  //alert(`Simulación: Descargando documento para ${reserva.apellidos_nombres}`);
  generatePdfReservaMatricula(reserva);
};


const UtilizarReserva = (reserva) => {
  if (deleting.value) return;

  showConfirmModal(
    {
      title: "Confirmar acción",
      message: `¿Deseas utilizar la reserva de "${reserva?.apellidos_nombres}"?`,
      actionButton: {
        class:
          "bg-yellow-500 hover:bg-yellow-600 text-black dark:text-black",
        text: "Sí, utilizar",
      },
      returnButton: {
        class:
          "bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600",
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
          loading.value = true;
          await matriculaStore.loadListaReserva();
          loading.value = false;
          return;
        }

        showToast("No se pudo utilizar la reserva.", "warning");

        await matriculaStore.loadListaReserva();
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
  <div class="w-full space-y-4  px-3">


    <div class="flex-between flex-row-reverse my-5">
      <SearchBar :totalResultados="ordenados.length" :campoOrden="'apellidos_nombres'" @search="filtrar" />
      <div class="font-inter text-md w-full">Lista de estudiantes con reserva</div>
    </div>

    <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event"  >
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
            <MenuTable :actions="{ view: false, edit: false, delete: false, download: true,custom1:true }" :labels="{
              custom1: 'Utilizar reservar',
              download: 'Descargar reservar',
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
</template>
