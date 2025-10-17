<script setup>
import { ref, onMounted } from 'vue'
import { FolderIcon, ChevronUpIcon, EyeIcon, ArrowDownTrayIcon, CalendarIcon, LinkIcon } from '@heroicons/vue/24/outline'
import SearchBar from '../../components/head_table/headSearch.vue'
import useProgramacionAdmintore from '../../store/Documento/useDocumentoStore'
import { useIconoArchivo } from '../../store/Documento/useIconoArchivoStore'

const props = defineProps({
  id: { type: String, required: true },
})

const documentoStore = useProgramacionAdmintore()
const { iconoArchivo } = useIconoArchivo()

const carpetas = ref([])
const searchQuery = ref('')
const carpetasAbiertas = ref({})

onMounted(async () => {
  await documentoStore.loadGetProgramacionByGrupo(props.id)
  const data = documentoStore.programacionPorGrupo
  carpetas.value = data.subcarpetas || []
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
    <div
      v-if="documentoStore.ProgramacionByGrupoLoading"
      class="text-center py-16 bg-white dark:bg-gray-800 rounded-lg shadow-md"
    >
      <FolderIcon class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" />
      <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
        Cargando carpetas...
      </h3>
    </div>

    <!-- Carpetas -->
    <div v-else>
      <div
        v-for="carpeta in filtrarCarpetas()"
        :key="carpeta.id"
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm"
      >
        <!-- Título -->
        <div
          class="flex justify-between items-center p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
          @click="toggleCarpeta(carpeta.id)"
        >
          <div class="flex items-center gap-2">
            <FolderIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            <div>
              <p class="font-semibold text-gray-800 dark:text-gray-100 leading-tight">
                {{ carpeta.nombre }}
              </p>
              <!-- Info adicional de programación -->
              <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-1">
                <CalendarIcon class="h-4 w-4" />
                {{ formatFecha(carpeta.programacion?.fecha_inicio) }} - {{ formatFecha(carpeta.programacion?.fecha_fin) }}
              </p>
            </div>
          </div>

          <ChevronUpIcon
            class="h-5 w-5 text-gray-400 transition-transform"
            :class="{ 'rotate-180': !carpetasAbiertas[carpeta.id] }"
          />
        </div>

        <!-- Archivos -->
        <transition name="fade">
          <div
            v-if="carpetasAbiertas[carpeta.id]"
            class="px-6 pb-3 flex flex-wrap gap-2"
          >
            <!-- Archivos individuales -->
            <div
              v-for="archivo in carpeta.archivos"
              :key="archivo.id"
              class="flex items-center justify-between bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition w-auto"
              style="min-width: 250px; max-width: 100%;"
            >
              <div class="flex items-center gap-2 overflow-hidden">
                <img
                  :src="iconoArchivo(archivo.mimeType)"
                  alt="icon"
                  class="h-5 w-5 flex-shrink-0"
                />
                <span
                  class="text-gray-700 dark:text-gray-200 text-sm truncate max-w-[180px]"
                  :title="archivo.nombre"
                >
                  {{ archivo.nombre }}
                </span>
              </div>

              <div class="flex items-center gap-2 flex-shrink-0">
                <a
                  :href="archivo.webViewLink"
                  target="_blank"
                  title="Ver archivo"
                  class="text-gray-500 hover:text-blue-600 dark:hover:text-blue-400"
                >
                  <EyeIcon class="h-4 w-4" />
                </a>
                <a
                  :href="archivo.webViewLink"
                  target="_blank"
                  download
                  title="Descargar"
                  class="text-gray-500 hover:text-green-600 dark:hover:text-green-400"
                >
                  <ArrowDownTrayIcon class="h-4 w-4" />
                </a>
              </div>
            </div>

            <!-- Sin archivos -->
            <div
              v-if="carpeta.archivos.length === 0"
              class="text-center text-gray-500 dark:text-gray-400 w-full py-3"
            >
              No hay archivos en esta carpeta.
            </div>
          </div>
        </transition>
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
