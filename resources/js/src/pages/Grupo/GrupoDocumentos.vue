<script setup>
import { ref, onMounted, computed } from 'vue'
import SearchBar from '../../components/head_table/headSearch.vue'
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue'
import Table from '../../components/table/Table.vue'
import THead from '../../components/table/THead.vue'
import TBody from '../../components/table/TBody.vue'
import Tr from '../../components/table/Tr.vue'
import Th from '../../components/table/Th.vue'
import Td from '../../components/table/Td.vue'
import useProgramacionAdmintore from '../../store/Documento/useDocumentoStore'
import { useIconoArchivo } from '../../store/Documento/useIconoArchivoStore'
import useTableData from "../../composables/tabla/useTableData";
const props = defineProps({
  id: { type: String, required: true },
})

const documentoStore = useProgramacionAdmintore()
const { iconoArchivo } = useIconoArchivo()

const carpetas = ref([])

const carpetasAbiertas = ref({})
const isRecargando = ref(false)

// 🌀 Cargar las carpetas al iniciar
const cargarCarpetas = async () => {
  await documentoStore.loadGetProgramacionByGrupo(props.id)
  const data = documentoStore.programacionPorGrupo
  carpetas.value = data?.subcarpetas || []
}

// 🔄 Recargar datos (sin refrescar página)
const recargarDocumentos = async () => {
  isRecargando.value = true
  await cargarCarpetas()
  isRecargando.value = false
}

// --- FILTRO, ORDEN Y PAGINACIÓN ---
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


// 🧩 Abrir/cerrar carpeta
const toggleCarpeta = (id) => {
  carpetasAbiertas.value[id] = !carpetasAbiertas.value[id]
}

// 📅 Formatear fecha
const formatFecha = (fecha) => {
  if (!fecha) return ''
  return new Date(fecha).toLocaleDateString('es-PE', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

// 🧭 Cargar al montar
onMounted(async () => {
  await cargarCarpetas()
})
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-grupos', 'ver-grupos']">
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
      <div v-else
        class=" bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
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
      </div>
    </div>
  </AuthorizationFallback>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

.list-leave-active {
  position: absolute;
}
</style>