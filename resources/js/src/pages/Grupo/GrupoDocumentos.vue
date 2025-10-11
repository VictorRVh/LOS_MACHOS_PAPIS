<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { FolderIcon, ArrowUpRightIcon } from '@heroicons/vue/24/outline'; // Asegúrate de importar ArrowUpRightIcon
import useTableData from '../../composables/tabla/useTableData';
import useSlider from '../../composables/useSlider';

import SearchBar from '../../components/head_table/headSearch.vue';
import CreateButton from '../../components/ui/CreateButton.vue';
import GrupoDocumentoSlider from '../../components/page/Grupo/GrupoDocumentoSlider.vue';
import useProgramacionAdmintore from '../../store/Documento/useDocumentoStore';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});


const { slider, sliderData, showSlider, hideSlider } = useSlider();

const documentoStore = useProgramacionAdmintore();

const documentos = ref([]);
const isLoading = ref(true);

onMounted(async () => {
  try {
    await documentoStore.loadGetProgramacionByGrupo(props.id)

    console.log('respuesta en el onmounted: ', documentoStore.programacionPorGrupo);
    documentos.value = documentoStore.programacionPorGrupo.programaciones;
  } finally {
    isLoading.value = false;
  }
});


const {
  paginados: documentosPaginados,
  ordenados: documentosOrdenados,
  filtrar: filtrarDocumentos
} = useTableData(documentos, {
  defaultOrderBy: "titulo",
  searchFields: ["titulo"]
});

const refreshDocumentos = () => {
  console.log('Recargando lista de documentos...');
};

const abrirCarpeta = (doc) => {
  console.log('Navegando a la carpeta del documento:', doc.id);
  // Descomenta la siguiente línea y ajusta el 'name' de la ruta cuando la tengas definida
  // router.push({ name: 'grupo.documentos.detalle', params: { id: props.id, docId: doc.id } });
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-300">
        Lista de documentos
      </h3>
      <div class="flex items-center gap-4">
        <SearchBar :totalResultados="documentosOrdenados.length" @search="filtrarDocumentos" />
        <CreateButton @click="showSlider(true)">
          Agregar Nuevo
        </CreateButton>
      </div>
    </div>

    <div v-if="documentosPaginados.length > 0"
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div v-for="doc in documentosPaginados" :key="doc.id" @click="abrirCarpeta(doc)"
        class="group relative bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700 cursor-pointer transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1 hover:border-cetpro/50">

        <div class="flex justify-between items-start">  
          <FolderIcon class="h-12 w-12 text-cetpro dark:text-cetpro-light transition-colors duration-300" />
          <ArrowUpRightIcon
            class="h-6 w-6 text-gray-400 dark:text-gray-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
        </div>

        <div class="mt-4">
          <h4
            class="text-lg font-bold text-gray-800 dark:text-white transition-colors duration-300 group-hover:text-cetpro-dark dark:group-hover:text-cetpro-light">
            {{ doc.programacion_general.tipo_entrega }}
          </h4>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 h-10">
            {{ doc.descripcion }} dedoemdoe
          </p>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-lg shadow-md">
      <FolderIcon class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" />
      <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">No se encontraron documentos</h3>
      <p class="mt-1 text-sm text-gray-500">Intenta con otra búsqueda o agrega un nuevo tipo de documento.</p>
    </div>
  </div>

  <GrupoDocumentoSlider :show="slider" :documento-data="sliderData" @hide="hideSlider" @submitted="refreshDocumentos" />
</template>