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

const practicas = ref([]);

const {
  query,
  pagina,
  itemsPorPagina,
  paginados: practicasPaginadas,
  totalPaginas,
  ordenados: practicasOrdenadas,
  filtrar: filtrarPracticas
} = useTableData(practicas, {
  defaultOrderBy: "nombre",
  searchFields: ["nombre", "efsrt", "observacion"]
});

onMounted(() => {
  practicas.value = [
    { id: 2, nombre: 'CALIZAYA MAMANI, Nelida Yudith', efsrt: 'EFSRT INTERNAS', observacion: '' },
    { id: 1, nombre: 'CHOQUEHUANCA MAMANI, Flor De Maria', efsrt: 'EFSRT INTERNAS', observacion: '' },
    { id: 4, nombre: 'CORDOVA MAMANI, Nayely Marilim', efsrt: 'EFSRT INTERNAS', observacion: '.' },
    { id: 5, nombre: 'CORDOVA MAMANI, Nayely Marilim', efsrt: 'EFSRT INTERNAS', observacion: '.' },
    { id: 6, nombre: 'CORDOVA MAMANI, Nayely Marilim', efsrt: 'EFSRT INTERNAS', observacion: '.' },
    { id: 7, nombre: 'CORDOVA MAMANI, Nayely Marilim', efsrt: 'EFSRT INTERNAS', observacion: '.' },
  ];
});

const verInforme = (practica) => {
  console.log('Viendo informe de:', practica.id);
};

const descargarInforme = (practica) => {
  console.log('Descargando informe de:', practica.id);
};
</script>

<template>
  <div class="space-y-4">
    <div class="flex justify-between items-center">
      <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-300">
        Lista de prácticas
      </h3>
    </div>
    
    <div class="flex justify-end">
        <SearchBar :totalResultados="practicasOrdenadas.length" @search="filtrarPracticas" />
    </div>

    <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">
      <THead>
        <Th>N°</Th>
        <Th>Apellidos y Nombres</Th>
        <Th>EFSRT</Th>
        <Th>Observación</Th>
        <Th class="text-center">Opciones</Th>
      </THead>
      <TBody>
        <Tr v-for="(practica, index) in practicasPaginadas" :key="practica.id">
          <Td>{{ (pagina - 1) * itemsPorPagina + index + 1 }}</Td>
          <Td class="font-medium whitespace-nowrap">{{ practica.nombre }}</Td>
          <Td>{{ practica.efsrt }}</Td>
          <Td>{{ practica.observacion }}</Td>
          <Td>
            <div class="flex items-center justify-center space-x-2">
              <button @click="descargarInforme(practica)" class="p-1 text-gray-500 hover:text-blue-600 transition-colors">
                <DocumentArrowDownIcon class="h-5 w-5" />
              </button>
              <button @click="verInforme(practica)" class="p-1 text-gray-500 hover:text-blue-600 transition-colors">
                <EyeIcon class="h-5 w-5" />
              </button>
            </div>
          </Td>
        </Tr>
      </TBody>
    </Table>
  </div>
</template>