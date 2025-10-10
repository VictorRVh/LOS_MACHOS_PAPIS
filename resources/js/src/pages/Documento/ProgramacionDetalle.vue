<script setup>
import { ref, onMounted, computed } from 'vue';


import useModalToast from '../../composables/useModalToast';
import useProgramacionSubidostore from "../../store/Documento/useDocumentoSubidoStore";

const props = defineProps({
  id: {
    type: String,
    required: true
  }
});

const { showToast, showPromptModal } = useModalToast();
const programacionStore = useProgramacionSubidostore();

const loading = ref(true);
const programacion = ref(null);
const gruposProgramados = ref([]);
const openMenuId = ref(null);

const currentPage = ref(1);
const itemsPerPage = 6;

// 🔹 Cargar los datos desde el store real
onMounted(async () => {
  loading.value = true;
  try {
    await programacionStore.loadgetProgramacionSubidos(props.id);

    const data = programacionStore.programacionSubidos;
    if (data && data.programacion) {
      programacion.value = data.programacion;
      gruposProgramados.value = data.grupos_programados || [];
    } else {
      showToast("No se encontró información de la programación.", "error");
    }
  } catch (error) {
    console.error(error);
    showToast("Error al cargar la programación.", "error");
  } finally {
    loading.value = false;
  }
});

// 🔹 Computed para paginación
const paginatedGrupos = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return gruposProgramados.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(gruposProgramados.value.length / itemsPerPage));

const changePage = (page) => {
  if (page < 1 || page > totalPages.value) return;
  currentPage.value = page;
};



const toggleMenu = (grupoId) => {
  openMenuId.value = openMenuId.value === grupoId ? null : grupoId;
};

const habilitarEntrega = (grupo) => {
  showPromptModal({
    title: `Habilitar Plazo para Grupo ${grupo.grupo_detalle.seccion}`,
    text: 'Seleccione los días de prórroga (1-5):',
    inputType: 'select',
    inputOptions: { '1': '1 día', '2': '2 días', '3': '3 días', '4': '4 días', '5': '5 días' },
    confirmButtonText: 'Habilitar',
  }, (days) => {
    if (days) {
      showToast(`Plazo extendido por ${days} día(s) para el grupo ${grupo.grupo_detalle.seccion}.`, 'success');
    }
  });
};

const handleAction = (action, grupo) => {
  openMenuId.value = null;
  if (action === 'Habilitar Entrega') {
    habilitarEntrega(grupo);
  } else {
    showToast(`Acción '${action}' en grupo ${grupo.grupo_detalle.seccion}.`, 'info');
  }
};
</script>

<template>
  <div class="p-4 md:p-6 space-y-6">
    <!-- LOADING -->
    <div v-if="loading" class="text-center py-20 text-gray-600 dark:text-gray-300">Cargando datos...</div>

    <!-- DATOS DE PROGRAMACIÓN -->
    <div v-else-if="programacion">
      <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
          {{ programacion.tipo_entrega }}
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">
          <span class="font-semibold">Inicio:</span> {{ formatFecha(programacion.fecha_inicio) }} |
          <span class="font-semibold">Fin:</span> {{ formatFecha(programacion.fecha_fin) }}
        </p>
      </header>

      <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-4">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300">
          Estado de entrega por grupo
        </h2>
      </div>

      <!-- LISTA DE GRUPOS -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="grupo in paginatedGrupos"
          :key="grupo.id"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col transition-shadow"
        >
          <!-- ENCABEZADO DEL GRUPO -->
          <div class="p-4 border-b dark:border-gray-700 flex justify-between items-start">
            <div>
              <p class="text-sm font-bold text-cetpro dark:text-cetpro-light uppercase truncate">
                {{ grupo.grupo_detalle.nombre_especialidad }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ grupo.grupo_detalle.nombre_modulo }} |
                Sección {{ grupo.grupo_detalle.seccion }} |
                Turno {{ grupo.grupo_detalle.turno }}
              </p>
            </div>

            <div class="relative">
              <button
                @click.stop="toggleMenu(grupo.id)"
                class="p-1 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <EllipsisVerticalIcon class="h-5 w-5" />
              </button>

              <!-- MENÚ DE OPCIONES -->
              <transition name="fade">
                <div
                  v-if="openMenuId === grupo.id"
                  class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-900 rounded-md shadow-lg z-20 border border-gray-200 dark:border-gray-700"
                >
                  <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                    <li>
                      <a
                        href="#"
                        @click.prevent=""
                        class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800"
                      >
                        <DocumentArrowUpIcon class="h-5 w-5" /> Subir Formato/Guía
                      </a>
                    </li>
                    <li>
                      <a
                        href="#"
                        @click.prevent=""
                        class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800"
                      >
                        <ChatBubbleBottomCenterTextIcon class="h-5 w-5" /> Agregar Observación
                      </a>
                    </li>
                    <li><hr class="my-1 dark:border-gray-700" /></li>
                    <li>
                      <a
                        href="#"
                        @click.prevent="handleAction('Habilitar Entrega', grupo)"
                        class="flex items-center gap-3 px-4 py-2 text-yellow-600 dark:text-yellow-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                      >
                        <CalendarDaysIcon class="h-5 w-5" /> Habilitar Plazo Extra
                      </a>
                    </li>
                  </ul>
                </div>
              </transition>
            </div>
          </div>

          <!-- DETALLE DEL GRUPO -->
          <div class="p-4 flex-grow">
            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
              {{ grupo.grupo_detalle.nombre_docente }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Documento: {{ grupo.documento_admin }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Observación: {{ grupo.observacion || 'Ninguna' }}
            </p>
          </div>

          <!-- ESTADO -->
          <div
            class="p-4 bg-gray-50 dark:bg-gray-800/50 border-t dark:border-gray-700 flex items-center justify-between"
          >
            <div class="flex items-center gap-2" :class="grupo.estado ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
              <component :is="grupo.estado ? CheckCircleIcon : ClockIcon" class="h-5 w-5" />
              <span class="text-sm font-semibold">
                {{ grupo.estado ? 'Entregado' : 'Pendiente' }}
              </span>
            </div>
            <span class="text-xs text-gray-500 dark:text-gray-400">
              {{ formatFecha(grupo.created_at) }}
            </span>
          </div>
        </div>
      </div>

      <!-- PAGINACIÓN -->
      <div v-if="totalPages > 1" class="flex items-center justify-center pt-6">
        <nav class="inline-flex -space-x-px rounded-md shadow-sm">
          <button
            @click="changePage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="relative inline-flex items-center rounded-l-md px-3 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50"
          >
            Anterior
          </button>
          <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 ring-1 ring-inset ring-gray-300">
            Página {{ currentPage }} de {{ totalPages }}
          </span>
          <button
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="relative inline-flex items-center rounded-r-md px-3 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50"
          >
            Siguiente
          </button>
        </nav>
      </div>
    </div>

    <!-- SIN DATOS -->
    <div v-else class="text-center py-20">
      <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
        No se encontró la programación
      </h3>
    </div>

    <!-- CIERRE DE MENÚ AL HACER CLICK FUERA -->
    <div v-if="openMenuId" @click.stop="openMenuId = null" class="fixed inset-0 z-10"></div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease-out, transform 0.15s ease-out;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
