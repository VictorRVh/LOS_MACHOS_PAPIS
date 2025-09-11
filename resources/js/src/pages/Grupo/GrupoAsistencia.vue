<script setup>
import { ref, onMounted } from 'vue';
import { EyeIcon, DocumentTextIcon, ArrowUpRightIcon } from '@heroicons/vue/24/outline';
import useTableData from '../../composables/tabla/useTableData';

import SearchBar from '../../components/head_table/headSearch.vue';
import CreateButton from '../../components/ui/CreateButton.vue';
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

const sesiones = ref([]);

const {
  query,
  pagina,
  itemsPorPagina,
  paginados: sesionesPaginadas,
  totalPaginas,
  ordenados: sesionesOrdenadas,
  filtrar: filtrarSesiones
} = useTableData(sesiones, {
  defaultOrderBy: "titulo",
  searchFields: ["titulo", "descripcion", "estado"]
});

onMounted(() => {
  sesiones.value = [
    { id: 2, fecha_inicio: '10/03/2025', fecha_final: '10/03/2025', titulo: 'Sesión uno de prueba', descripcion: '', estado: 'Pendiente' },
    { id: 3, fecha_inicio: '10/03/2025', fecha_final: '10/03/2025', titulo: 'Sesión dos de prueba', descripcion: '', estado: 'Pendiente' },
    { id: 1, fecha_inicio: '10/03/2025', fecha_final: '10/03/2025', titulo: 'Sesión tres de prueba', descripcion: '', estado: 'En proceso' },
    { id: 4, fecha_inicio: '10/03/2025', fecha_final: '10/03/2025', titulo: 'Sesión cuatro de prueba', descripcion: '.', estado: 'Finalizado' },
  ];
});

const getEstadoClass = (estado) => {
  switch (estado) {
    case 'Pendiente': return 'text-green-600 dark:text-green-400';
    case 'En proceso': return 'text-blue-600 dark:text-blue-400';
    case 'Finalizado': return 'text-red-600 dark:text-red-400';
    default: return 'text-gray-500';
  }
};

const verAsistencia = (sesion) => {
  console.log('Viendo asistencia de la sesión:', sesion.id);
};

const descargarReporte = (sesion) => {
  console.log('Descargando reporte de la sesión:', sesion.id);
};

const abrirSliderNuevaSesion = () => {
  console.log('Abriendo slider para nueva sesión');
};
</script>

<template>
  <div class="space-y-4">
    <div class="flex justify-between items-center">
      <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-300">
        Lista de sesiones
      </h3>
      <CreateButton @click="abrirSliderNuevaSesion()">
        Agregar Sesión
      </CreateButton>
    </div>
    
    <div class="flex justify-end">
        <SearchBar :totalResultados="sesionesOrdenadas.length" @search="filtrarSesiones" />
    </div>

    <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">
      <THead>
        <Th>N°</Th>
        <Th>Fecha Inicio</Th>
        <Th>Fecha final</Th>
        <Th>Título</Th>
        <Th>Descripción</Th>
        <Th>Estado</Th>
        <Th class="text-center">Opciones</Th>
      </THead>
      <TBody>
        <Tr v-for="(sesion, index) in sesionesPaginadas" :key="sesion.id">
          <Td>{{ (pagina - 1) * itemsPorPagina + index + 1 }}</Td>
          <Td>{{ sesion.fecha_inicio }}</Td>
          <Td>{{ sesion.fecha_final }}</Td>
          <Td class="font-medium">{{ sesion.titulo }}</Td>
          <Td>{{ sesion.descripcion }}</Td>
          <Td>
            <span class="inline-flex items-center font-medium" :class="getEstadoClass(sesion.estado)">
              {{ sesion.estado }}
              <ArrowUpRightIcon class="h-4 w-4 ml-1" />
            </span>
          </Td>
          <Td>
            <div class="flex items-center justify-center space-x-2">
              <button @click="descargarReporte(sesion)" class="p-1 text-gray-500 hover:text-blue-600 transition-colors">
                <DocumentTextIcon class="h-5 w-5" />
              </button>
              <button @click="verAsistencia(sesion)" class="p-1 text-gray-500 hover:text-blue-600 transition-colors">
                <EyeIcon class="h-5 w-5" />
              </button>
            </div>
          </Td>
        </Tr>
      </TBody>
    </Table>
  </div>
</template>