<script setup>
import { ref, onMounted } from 'vue'
import { FolderIcon, CalendarIcon, ChevronDownIcon, EyeIcon, ArrowDownTrayIcon, DocumentTextIcon } from '@heroicons/vue/24/outline'
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
import useHttpRequest from '../../composables/useHttpRequest'

const props = defineProps({
  id: { type: String, required: true },
})

const { download } = useHttpRequest('/drive/file');

const documentoStore = useProgramacionAdmintore()
const { iconoArchivo } = useIconoArchivo()

const carpetas = ref([])
const carpetasAbiertas = ref({})
const isRecargando = ref(false)

const cargarCarpetas = async () => {
  await documentoStore.loadGetProgramacionByGrupo(props.id)
  const data = documentoStore.programacionPorGrupo
  carpetas.value = data?.subcarpetas || []
}

const recargarDocumentos = async () => {
  isRecargando.value = true
  await cargarCarpetas()
  isRecargando.value = false
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

onMounted(async () => {
  await cargarCarpetas()
})
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-grupos', 'ver-grupos', 'ver-mis-módulos']">
    <div class="space-y-3">
      <section
        class="border border-slate-200 bg-white p-3 transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
          <div class="min-w-0">
            <h3 class="text-[15px] font-medium text-slate-900 dark:text-slate-100">
              Documentos en Google Drive
            </h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
              Programaciones y archivos asociados al grupo.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <SearchBar v-if="!documentoStore.ProgramacionByGrupoLoading && carpetas.length > 0"
              :totalResultados="carpetasOrdenadas.length" :campoOrden="'nombre'" @search="filtrarCarpetas" />

            <button @click="recargarDocumentos"
              class="inline-flex h-9 items-center gap-2 rounded-[3px] border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 transition-colors duration-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
              :disabled="isRecargando">
              <svg class="h-4 w-4 animate-spin" v-if="isRecargando" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              <span>{{ isRecargando ? "Actualizando" : "Recargar" }}</span>
            </button>
          </div>
        </div>
      </section>

      <section v-if="documentoStore.ProgramacionByGrupoLoading"
        class="border border-slate-200 bg-white px-4 py-10 transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900">
        <div class="flex items-start gap-3">
          <FolderIcon class="h-10 w-10 text-slate-300 dark:text-slate-600" />
          <div>
            <h4 class="text-base font-semibold text-slate-900 dark:text-slate-100">Cargando carpetas</h4>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Espere mientras se obtiene la información del
              repositorio.</p>
          </div>
        </div>
      </section>

      <section v-else
        class="border border-slate-200 bg-white transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900">
        <Table v-if="carpetasPaginadas?.length > 0" :paginacion="true" :current-page="pagina"
          :total-pages="totalPaginas" @changePage="pagina = $event">
          <THead class="hidden">
            <Th>Carpeta</Th>
            <Th>Periodo</Th>
            <Th>Archivos</Th>
          </THead>

          <TBody>
            <template v-for="carpeta in carpetasPaginadas" :key="carpeta.id">
              <Tr @click="toggleCarpeta(carpeta.id)"
                class="cursor-pointer border-b border-slate-200 bg-white transition-colors duration-200 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800/60">
                <Td colspan="3" class="px-4 py-3">
                  <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                      <div
                        class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center border border-slate-200 bg-slate-50 text-cetpro dark:border-slate-700 dark:bg-slate-800 dark:text-cetpro-light">
                        <FolderIcon class="h-4 w-4" />
                      </div>

                      <div class="min-w-0">
                        <div
                          class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-900 dark:text-slate-100">
                          {{ carpeta.nombre }}
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                          <CalendarIcon class="h-3.5 w-3.5" />
                          <span>{{ formatFecha(carpeta.programacion?.fecha_inicio) }}</span>
                          <span>·</span>
                          <span>{{ formatFecha(carpeta.programacion?.fecha_fin) }}</span>
                        </div>
                      </div>
                    </div>

                    <ChevronDownIcon :class="[
                      'mt-1 h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 dark:text-slate-500',
                      { 'rotate-180': carpetasAbiertas[carpeta.id] }
                    ]" />
                  </div>
                </Td>
              </Tr>

              <Tr v-if="carpetasAbiertas[carpeta.id]"
                class="border-b border-slate-200 bg-slate-50/60 dark:border-slate-700 dark:bg-slate-900">
                <Td colspan="3" class="px-4 py-3">
                  <TransitionGroup name="list" tag="div" class="space-y-2">
                    <div v-for="archivo in carpeta.archivos" :key="archivo.id"
                      class="flex items-center justify-between gap-3 border border-slate-200 bg-white px-3 py-2.5 transition-colors dark:border-slate-700 dark:bg-slate-800">
                      <div class="flex min-w-0 items-center gap-2">
                        <img :src="iconoArchivo(archivo.mimeType)" alt="icon" class="h-4 w-4 flex-shrink-0" />
                        <span class="truncate text-sm text-slate-700 dark:text-slate-200" :title="archivo.nombre">
                          {{ archivo.nombre }} edwedwed
                        </span>
                      </div>

                      <div class="flex items-center gap-2 flex-shrink-0">
                        <a :href="archivo.webViewLink" target="_blank" title="Ver archivo"
                          class="text-gray-500 hover:text-blue-600 dark:hover:text-blue-400">
                          <EyeIcon class="h-5 w-5" />
                        </a>
                        <button title="Descargar" @click="download(archivo.id, archivo.name || archivo.nombre)"
                          class="text-gray-500 hover:text-green-600 dark:hover:text-green-400">
                          <ArrowDownTrayIcon class="h-5 w-5" />
                        </button>
                      </div>
                    </div>

                    <div v-if="carpeta.archivos.length === 0"
                      class="flex items-start gap-3 border border-dashed border-slate-200 bg-white px-3 py-3 text-left dark:border-slate-700 dark:bg-slate-800">
                      <DocumentTextIcon class="mt-0.5 h-5 w-5 shrink-0 text-slate-300 dark:text-slate-600" />
                      <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">No hay archivos disponibles
                        </p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                          Esta carpeta aún no contiene documentos cargados.
                        </p>
                      </div>
                    </div>
                  </TransitionGroup>
                </Td>
              </Tr>
            </template>
          </TBody>
        </Table>

        <div v-else class="px-4 py-10">
          <div
            class="flex items-start gap-3 border border-dashed border-slate-200 bg-slate-50/60 px-4 py-4 text-left dark:border-slate-700 dark:bg-slate-900">
            <FolderIcon class="mt-0.5 h-6 w-6 shrink-0 text-slate-300 dark:text-slate-600" />
            <div>
              <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">No se encontraron carpetas</h4>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Intente con otro grupo o verifique si existen documentos programados.
              </p>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AuthorizationFallback>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.25s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.list-leave-active {
  position: absolute;
}
</style>
