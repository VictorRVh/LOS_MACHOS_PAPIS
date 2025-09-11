<script setup>
import { ref, onMounted } from 'vue';
import useTableData from '../../composables/tabla/useTableData';

import SearchBar from '../../components/head_table/headSearch.vue';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});

const calificaciones = ref([]);
const criterios = ref(['CT1', 'CT2', 'CT3', 'CT4', 'CT5']);

const {
  query,
  pagina,
  itemsPorPagina,
  paginados: calificacionesPaginadas,
  totalPaginas,
  ordenados: calificacionesOrdenadas,
  filtrar: filtrarCalificaciones
} = useTableData(calificaciones, {
  defaultOrderBy: "nombre",
  searchFields: ["nombre"]
});

onMounted(() => {
  calificaciones.value = [
    { id: 1, nombre: 'ALENCASTRE LUQUE, Kelly Angie', notas: [12, 14, 16, 15, 14, 17], puntaje: 89, promedio: 15, estado: 'APROBADO' },
    { id: 2, nombre: 'ALFARO AVENDAÑO, Victoria Valentina', notas: [12, 8, 10, 8, 4, 7], puntaje: 9, promedio: 15, estado: 'DESAPROBADO' },
    { id: 3, nombre: 'APAZA HUARAYA, Ruth Gricelda', notas: [12, 14, 16, 15, 14, 17], puntaje: 89, promedio: 15, estado: 'APROBADO' },
    { id: 4, nombre: 'ARE QUISPE, Roxana Karina', notas: [12, 14, 16, 15, 14, 17], puntaje: 89, promedio: 15, estado: 'APROBADO' },
    { id: 5, nombre: 'ARENAS SANCA, Rosanna Anjeli', notas: [12, 14, 16, 15, 14, 17], puntaje: 89, promedio: 15, estado: 'APROBADO' },
    { id: 6, nombre: 'CALIZAYA MAMANI, Nelida Yudith', notas: [12, 14, 16, 15, 14, 17], puntaje: 89, promedio: 15, estado: 'APROBADO' },
  ];
});

const getNotaClass = (nota) => (nota < 11 ? 'text-red-600 font-bold' : '');
const getEstadoClass = (estado) => (estado === 'APROBADO' ? 'text-green-600' : 'text-red-600');
</script>

<template>
  <div class="space-y-4">
    <div class="flex justify-between items-center">
      <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-300">
        Lista de calificaciones
      </h3>
    </div>
    
    <div class="flex justify-end">
        <SearchBar :totalResultados="calificacionesOrdenadas.length" @search="filtrarCalificaciones" />
    </div>

    <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">
      <THead>
        <Th>N°</Th>
        <Th>APELLIDOS Y NOMBRES</Th>
        <Th v-for="criterio in criterios" :key="criterio">{{ criterio }}</Th>
        <Th>PUNTAJE</Th>
        <Th>PROMEDIO</Th>
        <Th>A-D-R</Th>
      </THead>
      <TBody>
        <Tr v-for="(calificacion, index) in calificacionesPaginadas" :key="calificacion.id">
          <Td>{{ (pagina - 1) * itemsPorPagina + index + 1 }}</Td>
          <Td class="font-medium whitespace-nowrap">{{ calificacion.nombre }}</Td>
          <Td v-for="(nota, i) in calificacion.notas" :key="i" class="text-center" :class="getNotaClass(nota)">
            {{ nota }}
          </Td>
          <Td class="text-center" :class="getNotaClass(calificacion.puntaje)">{{ calificacion.puntaje }}</Td>
          <Td class="text-center">{{ calificacion.promedio }}</Td>
          <Td class="font-bold text-center" :class="getEstadoClass(calificacion.estado)">
            {{ calificacion.estado }}
          </Td>
        </Tr>
      </TBody>
    </Table>
  </div>
</template>