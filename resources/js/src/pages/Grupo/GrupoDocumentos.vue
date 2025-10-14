<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { FolderIcon, ArrowUpRightIcon, ArrowPathIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import axios from '../../utils/axios';
import useSlider from '../../composables/useSlider';
import useModalToast from '../../composables/useModalToast';

import CreateButton from '../../components/ui/CreateButton.vue';
import GrupoDocumentoSlider from '../../components/page/Grupo/GrupoDocumentoSlider.vue';

const props = defineProps({
  id: { type: String, required: true },
});

const { slider, sliderData, showSlider, hideSlider } = useSlider();
const { showToast } = useModalToast();

const carpetas = ref([]);
const isLoading = ref(true);
const error = ref(null);

const fetchDriveFolders = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const { data } = await axios.get('/drive/files');
    carpetas.value = data;
    
    if (data.length > 0) {
        showToast('Carpetas de Drive cargadas.', 'success');
    }

  } catch (err) {
    console.error("Error al cargar carpetas de Google Drive:", err);
    if (err.response && err.response.status === 401) {
        error.value = "No se pudo conectar con Google Drive. Por favor, conecta tu cuenta para continuar.";
    } else {
        error.value = "Ocurrió un error inesperado al intentar cargar los archivos de Google Drive.";
    }
    showToast(error.value, 'error');
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchDriveFolders);

const abrirCarpetaEnDrive = (carpeta) => {
  if (carpeta.webViewLink) {
    window.open(carpeta.webViewLink, '_blank');
  } else {
    showToast('Esta carpeta no tiene un enlace para visualizar.', 'warning');
  }
};

const handleSubmitted = () => {
    hideSlider();
    fetchDriveFolders();
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-300">
        Archivos de Google Drive
      </h3>
      <div class="flex items-center gap-4">
        <button @click="fetchDriveFolders" :disabled="isLoading" class="p-2 text-gray-500 hover:text-cetpro dark:hover:text-cetpro-light rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed" title="Refrescar carpetas">
            <ArrowPathIcon class="h-5 w-5" :class="{'animate-spin': isLoading}" />
        </button>
        <CreateButton @click="showSlider(true)">
          Crear Carpeta
        </CreateButton>
      </div>
    </div>

    <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="i in 4" :key="i" class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 animate-pulse">
            <div class="h-12 w-12 bg-gray-200 dark:bg-gray-700 rounded-md"></div>
            <div class="mt-4 h-6 w-3/4 bg-gray-200 dark:bg-gray-700 rounded-md"></div>
        </div>
    </div>

    <div v-else-if="error" class="text-center py-16 bg-red-50 dark:bg-red-900/20 rounded-lg shadow-md border border-red-200 dark:border-red-700">
      <ExclamationTriangleIcon class="mx-auto h-16 w-16 text-red-400 dark:text-red-500" />
      <h3 class="mt-2 text-lg font-semibold text-red-800 dark:text-red-200">Error de Conexión</h3>
      <p class="mt-1 text-sm text-red-600 dark:text-red-400 max-w-md mx-auto">{{ error }}</p>
      <a href="http://127.0.0.1:8000/google/redirect" class="mt-4 inline-block bg-cetpro hover:bg-cetpro-dark text-white font-bold py-2 px-4 rounded">
        Conectar con Google Drive
      </a>
    </div>

    <div v-else-if="carpetas.length > 0"
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div v-for="carpeta in carpetas" :key="carpeta.id" @click="abrirCarpetaEnDrive(carpeta)"
        class="group relative bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700 cursor-pointer transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1 hover:border-cetpro/50">

        <div class="flex justify-between items-start">  
          <FolderIcon class="h-12 w-12 text-cetpro dark:text-cetpro-light transition-colors duration-300" />
          <ArrowUpRightIcon
            class="h-6 w-6 text-gray-400 dark:text-gray-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
        </div>

        <div class="mt-4">
          <h4
            class="text-lg font-bold text-gray-800 dark:text-white transition-colors duration-300 group-hover:text-cetpro-dark dark:group-hover:text-cetpro-light truncate"
            :title="carpeta.name"
          >
            {{ carpeta.name }}
          </h4>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-lg shadow-md">
      <img src="https://www.google.com/drive/static/images/drive/restore/empty_trash.svg" alt="Drive Vacío" class="mx-auto h-32 w-32">
      <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Un lugar para todos tus archivos</h3>
      <p class="mt-1 text-sm text-gray-500">Tu Google Drive está vacío. Usa el botón "Crear Carpeta" para empezar.</p>
    </div>
  </div>

  <GrupoDocumentoSlider :show="slider" :documento-data="sliderData" @hide="hideSlider" @submitted="handleSubmitted" />
</template>