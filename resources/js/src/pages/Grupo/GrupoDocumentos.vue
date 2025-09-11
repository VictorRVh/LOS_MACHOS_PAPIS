<script setup>
import { ref, onMounted } from 'vue';
import { EyeIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';
import useTableData from '../../composables/tabla/useTableData';
import useSlider from '../../composables/useSlider';

import SearchBar from '../../components/head_table/headSearch.vue';
import CreateButton from '../../components/ui/CreateButton.vue';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import GrupoDocumentoSlider from '../../components/page/Grupo/GrupoDocumentoSlider.vue';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});

const documentos = ref([]);
const { slider, sliderData, showSlider, hideSlider } = useSlider();

const {
  query,
  pagina,
  itemsPorPagina,
  paginados: documentosPaginados,
  totalPaginas,
  ordenados: documentosOrdenados,
  filtrar: filtrarDocumentos
} = useTableData(documentos, {
  defaultOrderBy: "titulo",
  searchFields: ["titulo", "descripcion", "observaciones"]
});

const refreshDocumentos = () => {
    console.log('Recargando lista de documentos...');
};

onMounted(() => {
  documentos.value = [
    { id: 1, fecha_inicio: '10/03/2025', titulo: 'Sílabo', descripcion: '-', observaciones: 'falta mas datos' },
    { id: 2, fecha_inicio: '10/03/2025', titulo: 'Sesiones', descripcion: '-', observaciones: 'falta mas datos' },
    { id: 3, fecha_inicio: '10/03/2025', titulo: 'Capacidades terminales', descripcion: '-', observaciones: 'falta mas datos' },
    { id: 4, fecha_inicio: '10/03/2025', titulo: 'Documentos', descripcion: '-', observaciones: 'falta mas datos' },
  ];
});

const verDocumento = (doc) => {
  console.log('Viendo documento:', doc.id);
};
</script>

<template>
  <div class="space-y-4">
    <div class="flex justify-between items-center">
      <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-300">
        Lista de documentos
      </h3>
      <CreateButton @click="showSlider(true)">
        Agregar Nuevo
      </CreateButton>
    </div>
    
    <div class="flex justify-end">
        <SearchBar :totalResultados="documentosOrdenados.length" @search="filtrarDocumentos" />
    </div>

    <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">
      <THead>
        <Th>N°</Th>
        <Th>Fecha Inicio</Th>
        <Th>Título</Th>
        <Th>Descripción</Th>
        <Th>Observaciones</Th>
        <Th class="text-center">Opciones</Th>
      </THead>
      <TBody>
        <Tr v-for="(doc, index) in documentosPaginados" :key="doc.id">
          <Td>{{ (pagina - 1) * itemsPorPagina + index + 1 }}</Td>
          <Td>{{ doc.fecha_inicio }}</Td>
          <Td class="font-medium">{{ doc.titulo }}</Td>
          <Td>{{ doc.descripcion }}</Td>
          <Td>{{ doc.observaciones }}</Td>
          <Td>
            <div class="flex items-center justify-center space-x-2">
              <button @click="showSlider(true, doc)" class="p-1 text-gray-500 hover:text-blue-600 transition-colors">
                <ArrowUpTrayIcon class="h-5 w-5" />
              </button>
              <button @click="verDocumento(doc)" class="p-1 text-gray-500 hover:text-blue-600 transition-colors">
                <EyeIcon class="h-5 w-5" />
              </button>
            </div>
          </Td>
        </Tr>
      </TBody>
    </Table>
  </div>
  
  <GrupoDocumentoSlider 
    :show="slider"
    :documento-data="sliderData"
    @hide="hideSlider"
    @submitted="refreshDocumentos"
  />
</template>