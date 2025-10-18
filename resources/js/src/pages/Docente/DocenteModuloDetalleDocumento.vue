<script setup>
import { ref, onMounted } from 'vue'
import { FolderIcon, ChevronUpIcon, EyeIcon, ArrowDownTrayIcon, CalendarIcon, LinkIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { PlusIcon } from '@heroicons/vue/24/solid'
import SearchBar from '../../components/head_table/headSearch.vue'
import useProgramacionAdmintore from '../../store/Documento/useDocumentoStore'
import { useIconoArchivo } from '../../store/Documento/useIconoArchivoStore'
import axios from 'axios'
import useModalToast from '../../composables/useModalToast'

const props = defineProps({
  id: { type: String, required: true },
})

const { showConfirmModal, showToast } = useModalToast();

const documentoStore = useProgramacionAdmintore();
const { iconoArchivo } = useIconoArchivo()

const carpetas = ref([])
const searchQuery = ref('')
const carpetasAbiertas = ref({})
const errorCarga = ref(false)
const showUploadModal = ref(false)
const carpetaSeleccionada = ref(null)
const archivo = ref(null)

onMounted(async () => {
  try {
    await documentoStore.loadGetProgramacionByGrupo(props.id)
    const data = documentoStore.programacionPorGrupo

    if (data && typeof data === 'object') {
      carpetas.value = data.subcarpetas || []
    } else {
      console.warn('No se encontraron datos de programación')
      carpetas.value = []
    }
  } catch (error) {
    console.error('Error al cargar programación:', error)
    errorCarga.value = true
    carpetas.value = []
  }
})

const toggleCarpeta = (id) => {
  carpetasAbiertas.value[id] = !carpetasAbiertas.value[id]
}

const filtrarCarpetas = () => {
  const query = searchQuery.value.toLowerCase()
  return carpetas.value.filter(
    (c) =>
      c.nombre.toLowerCase().includes(query) ||
      (c.programacion?.tipo_entrega?.toLowerCase().includes(query))
  )
}

// 🧩 Formatear fecha
const formatFecha = (fecha) => {
  if (!fecha) return ''
  return new Date(fecha).toLocaleDateString('es-PE', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const abrirModalSubir = (carpeta) => {
  carpetaSeleccionada.value = carpeta;
  console.log('carpeta seleccionada', carpeta)
  showUploadModal.value = true;
}

const cerrarModal = () => {
  showUploadModal.value = false;
  carpetaSeleccionada.value = null;
  archivo.value = null;
}

const handleFileUpload = (event) => {
  archivo.value = event.target.files[0];
  console.log('Archivo seleccionado:', archivo.value);
}

const subirArchivo = async () => {
  if (!archivo.value) {
    console.warn('No hay archivo seleccionado');
    return;
  }

  try {
    const formData = new FormData();
    formData.append('file', archivo.value);
    formData.append('parentFolderId', carpetaSeleccionada.value.id);

    const response = await axios.post('/drive/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    // ✅ Actualizar la carpeta local con el nuevo archivo
    const carpetaIndex = carpetas.value.findIndex(c => c.id === carpetaSeleccionada.value.id);
    if (carpetaIndex !== -1) {
      carpetas.value[carpetaIndex].archivos.push(response.data);
    }

    // Recargar datos del store (opcional, si quieres mantener sincronizado)
    await documentoStore.loadGetProgramacionByGrupo(props.id);

    // Actualizar las carpetas con los datos frescos
    const data = documentoStore.programacionPorGrupo;
    if (data && typeof data === 'object') {
      carpetas.value = data.subcarpetas || [];
    }

    cerrarModal();
  } catch (error) {
    console.error('Error al subir archivo:', error);
    alert('Error al subir el archivo. Por favor intenta nuevamente.');
  }
}

const eliminarArchivo = async (carpeta, archivo) => {

  // if (!confirm(`¿Estás seguro de eliminar "${archivo.nombre}"?`)) {
  //   return;
  // }

  // if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    try {
      await axios.delete(`/drive/file/${archivo.id}`);

      const carpetaIndex = carpetas.value.findIndex(c => c.id === carpeta.id);
      if (carpetaIndex !== -1) {
        const archivoIndex = carpetas.value[carpetaIndex].archivos.findIndex(a => a.id === archivo.id);
        if (archivoIndex !== -1) {
          carpetas.value[carpetaIndex].archivos.splice(archivoIndex, 1);
        }
      }
    } catch (error) {
      console.error('Error al eliminar archivo:', error);
      alert('Error al eliminar el archivo. Por favor intenta nuevamente.');
    }
  });
}

</script>

<template>
  <div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex justify-between items-center">
      <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
        Documentos en Google Drive
      </h3>
      <SearchBar :totalResultados="carpetas.length" @search="searchQuery = $event" />
    </div>

    <!-- Cargando -->
    <div v-if="documentoStore.ProgramacionByGrupoLoading"
      class="text-center py-16 bg-white dark:bg-gray-800 rounded-lg shadow-md">
      <FolderIcon class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" />
      <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
        Cargando carpetas...
      </h3>
    </div>

    <div v-else-if="errorCarga"
      class="text-center py-16 bg-red-50 dark:bg-red-900/20 rounded-lg shadow-md border border-red-200 dark:border-red-800">
      <FolderIcon class="mx-auto h-16 w-16 text-red-400 dark:text-red-500" />
      <h3 class="mt-2 text-lg font-semibold text-red-700 dark:text-red-400">
        Error al cargar documentos
      </h3>
      <p class="text-sm text-red-600 dark:text-red-500 mt-1">
        No se pudieron obtener los documentos del grupo
      </p>
    </div>

    <!-- Sin carpetas 👈 Nuevo bloque -->
    <div v-else-if="carpetas.length === 0" class="text-center py-16 bg-white dark:bg-gray-800 rounded-lg shadow-md">
      <FolderIcon class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" />
      <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
        No hay carpetas disponibles
      </h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Aún no se han creado carpetas para este grupo
      </p>
    </div>

    <!-- Carpetas -->
    <div v-else>
      <div v-for="carpeta in filtrarCarpetas()" :key="carpeta.id"
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
        <!-- Título -->
        <div class="flex justify-between items-center p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
          @click="toggleCarpeta(carpeta.id)">
          <!-- IZQUIERDA -->
          <div class="flex items-center gap-2">
            <FolderIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            <div>
              <p class="font-semibold text-gray-800 dark:text-gray-100 leading-tight">
                {{ carpeta.nombre }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-1">
                <CalendarIcon class="h-4 w-4" />
                {{ formatFecha(carpeta.programacion?.fecha_inicio) }} -
                {{ formatFecha(carpeta.programacion?.fecha_fin) }}
              </p>
            </div>
          </div>

          <!-- DERECHA -->
          <div class="flex items-center gap-2">
            <button @click.stop="abrirModalSubir(carpeta)"
              class="p-1 rounded-full bg-blue-600 hover:bg-blue-700 text-white" title="Agregar documento">
              <PlusIcon class="h-5 w-5" />
            </button>

            <ChevronUpIcon class="h-5 w-5 text-gray-400 transition-transform"
              :class="{ 'rotate-180': !carpetasAbiertas[carpeta.id] }" />
          </div>
        </div>


        <!-- Archivos -->
        <transition name="fade">
          <div v-if="carpetasAbiertas[carpeta.id]" class="px-6 pb-3 flex flex-wrap gap-2">
            <!-- Archivos individuales -->
            <div v-for="archivo in carpeta.archivos" :key="archivo.id"
              class="flex items-center justify-between bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition w-auto"
              style="min-width: 250px; max-width: 100%;">
              <div class="flex items-center gap-2 overflow-hidden">
                <img :src="iconoArchivo(archivo.mimeType)" alt="icon" class="h-5 w-5 flex-shrink-0" />
                <span class="text-gray-700 dark:text-gray-200 text-sm truncate max-w-[180px]" :title="archivo.nombre">
                  {{ archivo.nombre }}
                </span>
              </div>

              <div class="flex items-center gap-2 flex-shrink-0">
                <a :href="archivo.webViewLink" target="_blank" title="Ver archivo"
                  class="text-gray-500 hover:text-blue-600 dark:hover:text-blue-400">
                  <EyeIcon class="h-4 w-4" />
                </a>
                <a :href="archivo.webViewLink" target="_blank" download title="Descargar"
                  class="text-gray-500 hover:text-green-600 dark:hover:text-green-400">
                  <ArrowDownTrayIcon class="h-4 w-4" />
                </a>
                <button title="Eliminar" @click="eliminarArchivo(carpeta, archivo)"
                  class="text-gray-500 hover:text-red-600 dark:hover:text-red-400">
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>
            </div>

            <!-- Sin archivos -->
            <div v-if="carpeta.archivos.length === 0" class="text-center text-gray-500 dark:text-gray-400 w-full py-3">
              No hay archivos en esta carpeta.
            </div>
          </div>
        </transition>
      </div>

      <div v-if="showUploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md">
          <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
            Subir archivo a: {{ carpetaSeleccionada?.nombre }}
          </h2>

          <!-- Aquí va el input de archivo -->
          <input type="file" class="mb-4" @change="handleFileUpload">

          <div class="flex justify-end gap-2">
            <button @click="cerrarModal" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
              Cancelar
            </button>
            <button @click="subirArchivo" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
              Subir
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
