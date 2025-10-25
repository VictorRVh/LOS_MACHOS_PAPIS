<script setup>
import { ref, onMounted } from 'vue'
import { FolderIcon, ChevronUpIcon, EyeIcon, ArrowDownTrayIcon, CalendarIcon, LinkIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { PlusIcon } from '@heroicons/vue/24/solid'
import SearchBar from '../../components/head_table/headSearch.vue'
import useProgramacionAdmintore from '../../store/Documento/useDocumentoStore'
import { useIconoArchivo } from '../../store/Documento/useIconoArchivoStore'
import axios from 'axios'
import useModalToast from '../../composables/useModalToast'
import useHttpRequest from '../../composables/useHttpRequest'
import useTableData from "../../composables/tabla/useTableData";
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue'
import Table from '../../components/table/Table.vue'
import THead from '../../components/table/THead.vue'
import TBody from '../../components/table/TBody.vue'
import Tr from '../../components/table/Tr.vue'
import Th from '../../components/table/Th.vue'
import Td from '../../components/table/Td.vue'
import FormInputFile from "../../components/ui/FormFileInput.vue";

const props = defineProps({
  id: { type: String, required: true },
})

const { store: uploadArchivo, saving: uploadLoading } = useHttpRequest('/drive/upload')
const { showConfirmModal, showToast } = useModalToast();

const documentoStore = useProgramacionAdmintore();
const { iconoArchivo } = useIconoArchivo();

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

    console.log('respuesta del data', data)

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
    showToast('No hay archivo seleccionado.', 'warning');
    return;
  }

  try {
    const formData = new FormData();
    formData.append('file', archivo.value);
    formData.append('parentFolderId', carpetaSeleccionada.value.id);
    formData.append('id_entrega', carpetaSeleccionada.value.programacion.id);

    // Subir archivo
    const response = await uploadArchivo(formData);

    // Actualizar la carpeta seleccionada con el nuevo archivo
    const carpetaIndex = carpetas.value.findIndex(c => c.id === carpetaSeleccionada.value.id);

    if (carpetaIndex !== -1) {
      // Inicializar array de archivos si no existe
      if (!Array.isArray(carpetas.value[carpetaIndex].archivos)) {
        carpetas.value[carpetaIndex].archivos = [];
      }

      // Agregar response directamente (es el archivo de Google Drive)
      carpetas.value[carpetaIndex].archivos.push(response);

      showToast('Archivo subido exitosamente', 'success');
    } else {
      console.error('No se encontró la carpeta seleccionada');
      showToast('Error: carpeta no encontrada', 'error');
    }

    // Recargar datos actualizados
    await documentoStore.loadGetProgramacionByGrupo(props.id);
    const data = documentoStore.programacionPorGrupo;
    if (data && typeof data === 'object') {
      carpetas.value = data.subcarpetas || [];
    }

    cerrarModal();
  } catch (error) {
    console.log(error)
    // console.error('Error al subir archivo:', error);
    const msg = error.response?.data?.error || 'Error al subir el archivo';
    showToast(msg, 'error');
  }
};

const recargarDocumentos = async () => {
  isRecargando.value = true
  await cargarCarpetas()
  isRecargando.value = false
}

const eliminarArchivo = async (carpeta, archivo) => {

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

const {
  query,
  orderBy,
  orderDirection,
  pagina,
  itemsPorPagina,
  paginados: carpetasPaginadas,
  totalPaginas,
  ordenados: carpetasOrdenadas,
  filtrar: filtrarCarpetas
} = useTableData(carpetas, {
  defaultOrderBy: "nombre",
  searchFields: ["nombre"]
})


</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-grupos', 'ver-grupos', 'ver-mis-modulos']">

    <div class="p-4 md:p-6 space-y-6">
      <!-- Header -->
      <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
          Documentos en Google Drive
        </h1>

        <div class="flex items-center gap-3">
          <SearchBar v-if="!documentoStore.ProgramacionByGrupoLoading && carpetas.length > 0"
            :totalResultados="carpetasOrdenadas.length" :campoOrden="'nombre'" @search="filtrarCarpetas" />


          <button @click="recargarDocumentos"
            class="bg-cetpro hover:bg-cetpro-dark text-cetpro-text px-3 py-2 rounded-md flex items-center gap-2 transition-colors duration-300"
            :disabled="isRecargando">
            <svg class="w-5 h-5 animate-spin" v-if="isRecargando" xmlns="http://www.w3.org/2000/svg" fill="none"
              viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <span v-else>Recargar</span>
          </button>
        </div>
      </header>

      <!-- Loading -->
      <div v-if="documentoStore.ProgramacionByGrupoLoading"
        class=" dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 text-center py-16">
        <FolderIcon class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" />
        <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
          Cargando carpetas...
        </h3>
      </div>

      <!-- Carpetas -->
      <div v-else class=" dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event"
          v-if="carpetasPaginadas?.length > 0">
          <THead class="hidden">
            <Th>Carpeta</Th>
            <Th>Periodo</Th>
            <Th>Archivos</Th>
          </THead>

          <TBody>
            <template v-for="carpeta in carpetasPaginadas" :key="carpeta.id">

              <!-- Fila principal -->
              <Tr @click="toggleCarpeta(carpeta.id)"
                class="dark:bg-cetpro-dark hover:bg-cetpro-dark dark:hover:bg-cetpro cursor-pointer transition-colors duration-200 border-b border-white dark:border-cetpro">
                <Td colspan="3" class=" bg-cetpro  px-4 py-3 font-bold uppercase tracking-wider text-sm">
                  <div class="flex items-center justify-between text-cetpro-text">
                    <div class="flex items-center gap-2">
                      <FolderIcon class="h-6 w-6 text-cetpro-text " />
                      <div>
                        <span>{{ carpeta.nombre }}</span>
                        <p class="text-xs text-gray-100 dark:text-gray-300 flex items-center gap-1 mt-1">
                          <CalendarIcon class="h-4 w-4" />
                          {{ formatFecha(carpeta.programacion?.fecha_inicio) }} -
                          {{ formatFecha(carpeta.programacion?.fecha_fin) }}
                        </p>
                      </div>
                      <button @click.stop="abrirModalSubir(carpeta)"
                        class="p-1 rounded-full bg-blue-600 hover:bg-blue-700 text-white" title="Agregar documento">
                        <PlusIcon class="h-5 w-5" />
                      </button>
                    </div>

                    <ChevronDownIcon :class="[
                      'h-6 w-6 text-cetpro-text transition-transform duration-300',
                      { 'rotate-180': carpetasAbiertas[carpeta.id] }
                    ]" />

                  </div>

                </Td>

              </Tr>

              <!-- Fila expandida -->
              <Tr v-if="carpetasAbiertas[carpeta.id]" class="bg-white dark:bg-gray-800 border-t-0">
                <Td colspan="3" class="p-0">
                  <TransitionGroup name="list" tag="div" class=" grid md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div v-for="archivo in carpeta.archivos" :key="archivo.id"
                      class="flex items-center justify-between bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition">
                      <div class="flex items-center gap-2 overflow-hidden">
                        <img :src="iconoArchivo(archivo.mimeType)" alt="icon" class="h-5 w-5 flex-shrink-0" />
                        <span class="text-gray-700 dark:text-gray-200 text-sm truncate max-w-[180px]"
                          :title="archivo.nombre">
                          {{ archivo.nombre }}
                        </span>
                      </div>

                      <div class="flex items-center gap-2 flex-shrink-0">
                        <a :href="archivo.webViewLink" target="_blank" title="Ver archivo"
                          class="text-gray-500 hover:text-blue-600 dark:hover:text-blue-400">
                          <EyeIcon class="h-5 w-5" />
                        </a>
                        <a :href="archivo.webViewLink" target="_blank" download title="Descargar"
                          class="text-gray-500 hover:text-green-600 dark:hover:text-green-400">
                          <ArrowDownTrayIcon class="h-5 w-5" />
                        </a>
                        <button title="Eliminar" @click="eliminarArchivo(carpeta, archivo)"
                          class="text-gray-500 hover:text-red-600 dark:hover:text-red-400">
                          <TrashIcon class="h-4 w-4" />
                        </button>
                      </div>
                    </div>

                    <div v-if="carpeta.archivos.length === 0"
                      class="text-center text-gray-500 dark:text-gray-400 col-span-full py-3">
                      No hay archivos en esta carpeta.
                    </div>
                  </TransitionGroup>
                </Td>
              </Tr>
            </template>
          </TBody>
        </Table>

        <!-- No hay carpetas -->
        <div v-else class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            aria-hidden="true">
            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2z" />
          </svg>
          <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
            No se encontraron carpetas
          </h3>
          <p class="mt-1 text-sm text-gray-500">
            Intenta con otro grupo o periodo.
          </p>
        </div>


        <div v-if="showUploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
              Subir archivo a: {{ carpetaSeleccionada?.nombre }}
            </h2>

            <!-- Input del archivo -->
            <FormInputFile v-model="archivo" :multiple="false" class="mb-4" />

            <div class="flex justify-end gap-2">
              <!-- Botón Cancelar -->
              <button @click="cerrarModal"
                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 disabled:opacity-50"
                :disabled="uploadLoading">
                Cancelar
              </button>

              <!-- Botón Subir con loading -->
              <button @click="subirArchivo"
                class="px-4 py-2 bg-cetpro text-white rounded hover:bg-cetpro-dark flex items-center justify-center gap-2 disabled:opacity-50"
                :disabled="uploadLoading">
                <template v-if="uploadLoading">
                  <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                  </svg>
                  Subiendo...
                </template>
                <template v-else>Subir</template>
              </button>
            </div>
          </div>
        </div>


      </div>
    </div>
  </AuthorizationFallback>
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
