<script setup>
import { ref, onMounted, computed } from 'vue';
import { ClockIcon, CheckCircleIcon, ExclamationTriangleIcon, EllipsisVerticalIcon, CalendarDaysIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';
import useModalToast from '../../composables/useModalToast';
import useProgramacionSubidostore from "../../store/Documento/useDocumentoSubidoStore";
import Slider from '../../components/ui/Slider.vue';
import DocumentoItemsSlider from '../../components/page/Documento/documetoItemsSlider.vue';
import DocumentoPlazo from '../../components/page/Documento/DocumentoPlazo.vue';
import MenuTable from "../../components/table/MenuTable.vue";
import useHttpRequest from "../../composables/useHttpRequest";
import axios from 'axios';

const props = defineProps({
  id: {
    type: String,
    required: true
  }
});

const { showConfirmModal, showToast } = useModalToast();
const programacionStore = useProgramacionSubidostore();

const { destroy: eliminarPorgramacion } = useHttpRequest("entrega_docente"); // <- DELETE /drive/file/{id}

const programacion = ref(null);
const gruposProgramados = ref([]);
const openMenuId = ref(null);
const selectedGrupo = ref(null);


const currentPage = ref(1);
const itemsPerPage = 6;

if (!programacionStore?.programacionSubidos?.length) await programacionStore.loadgetProgramacionSubidos(props.id);

const data = programacionStore.programacionSubidos;
if (data && data.programacion) {

  console.log('DATA', data)
  programacion.value = data.programacion;
  gruposProgramados.value = data.grupos_programados || [];
} else {
  showToast("No se encontró información de la programación.", "error");
}


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

const selectedGrupoItems = ref(null);
const selectedGrupoPlazo = ref(null);
const showItemsSlider = ref(false);
const showPlazoSlider = ref(false);

const openItemsModal = (grupo) => {
  console.log("aequi", grupo)
  selectedGrupoPlazo.value = grupo;
  showItemsSlider.value = true;
  openMenuId.value = null;
};

const openPlazoModal = (grupo) => {
  console.log("aequi", grupo)
  selectedGrupoPlazo.value = grupo;
  showPlazoSlider.value = true;
  openMenuId.value = null;
};

// 📴 Desactivar grupo
const desactivarGrupo = async (grupo) => {
  // Mostrar modal de confirmación reutilizable
  showConfirmModal(
    {
      title: "Confirmar Acción", // opcional, si tu modal lo usa
      message: `¿Seguro que deseas desactivar el grupo "${grupo.grupo_detalle.nombre_modulo}"?`,
      actionButton: {
        class: "bg-orange-600 hover:bg-orange-700",
        text: "Desactivar",
      },
      returnButton: {
        class: "bg-gray-100 hover:bg-gray-200",
        text: "Volver",
      },
    },
    async (confirmed) => {
      if (!confirmed) return;

      try {
        await eliminarPorgramacion(grupo.id);
        await programacionStore.loadgetProgramacionSubidos(props.id);
        showToast("Grupo desactivado con éxito.", "success");
      } catch (error) {
        console.error("❌ Error al desactivar grupo:", error);
        showToast("No se pudo desactivar el grupo. Intenta nuevamente.", "error");
      }
    }
  );
};

const generarReporte = async () => {

  try {
    const idAdmin = programacion.value.id;
    if (!idAdmin) {
      showToast("No hay una programación seleccionada.", "error");
      return;
    }

    const response = await axios.get(`/reporte-entregas-docentes`, {
      params: { id_admin: idAdmin },
      responseType: 'blob', // 🔹 Importante para archivos binarios
    });

    // Crear una URL temporal para descargarlo
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const a = document.createElement('a');
    a.href = url;
    a.download = `Reporte_Entregas_${new Date().toISOString().slice(0, 10)}.xlsx`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);

    const grupos = programacionStore?.programacionSubidos?.grupos_programados || [];
    const total = grupos.length;
    const cumplidos = grupos.filter(g => g.cumplio).length;
    const noCumplidos = total - cumplidos;

    showToast(
      `Reporte descargado correctamente.\nCumplieron: ${cumplidos}\nNo cumplieron: ${noCumplidos}`,
      "success"
    );
  } catch (error) {
    console.error('Error:', error);
    showToast("Ocurrió un error al generar el reporte.", "error");
  }


};

</script>

<template>
  <div class="p-4 md:p-6 space-y-6">
    <div v-if="programacionStore?.programacionSubidosLoading"
      class="text-center py-20 text-gray-600 dark:text-gray-300">Cargando datos...</div>

    <div v-else-if="programacion">
      <header class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
            {{ programacion.nombre_programacion }}
          </h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">
            <span class="font-semibold">Inicio:</span> {{ programacion.fecha_inicio }} |
            <span class="font-semibold">Fin:</span> {{ programacion.fecha_fin }}
          </p>
        </div>

        <div class="mt-4 md:mt-0 flex items-center gap-3">
          <Button class="bg-cetpro hover:bg-cetpro-dark text-white px-4 py-2 rounded-lg text-sm shadow"
            @click="generarReporte">
            Generar reporte
          </Button>
        </div>
      </header>

      <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">
        Estado de entrega por grupo
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="grupo in programacionStore?.programacionSubidos?.grupos_programados" :key="grupo.id"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-visible flex flex-col transition-shadow">

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
          

              <MenuTable :actions="{ edit: true, custom1: true, deactivate: false }" :labels="{
                edit: 'Subir documento',
                custom1: grupo.estado === 4 ? 'Habilitar plazo extra' : 'Observación',
                deactivate: 'Desactivar grupo',
              }" entityLabel="" @edit="() => openItemsModal(grupo)" @custom1="() => openPlazoModal(grupo)"
                @deactivate="() => desactivarGrupo(grupo)" />
       
          </div>

          <div class="p-4 flex-grow space-y-2">
            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
              {{ grupo.grupo_detalle.nombre_docente }}
            </p>

            <div class="flex items-center gap-2"
              :class="grupo.cumplio ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
              <span class="text-sm font-semibold">
                {{ grupo.cumplio ? 'Entregado' : 'No entregado' }}
              </span>
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-400">
              Observación: {{ grupo.observacion || 'Ninguna' }}
            </p>

            <!-- Bloque de fechas -->
            <div class="mt-3 bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-600 dark:text-gray-300">Inicio:</span>
                <span class="text-xs font-semibold text-gray-800 dark:text-gray-100">
                  {{ grupo.fecha_inicio }}
                </span>
              </div>

              <div class="flex items-center justify-between mt-1">
                <span class="text-xs text-gray-600 dark:text-gray-300">Fin:</span>
                <span class="text-xs font-semibold"
                  :class="grupo.dias_aplazados ? 'line-through text-gray-400 dark:text-gray-500' : 'text-gray-800 dark:text-gray-100'">
                  {{ grupo.fecha_fin }}
                </span>
              </div>

              <div v-if="grupo.dias_aplazados" class="flex items-center justify-between mt-1">
                <span class="text-xs text-yellow-600 dark:text-yellow-400">Fin aplazado (+{{ grupo.dias_aplazados }}
                  días):</span>
                <span class="text-xs font-semibold text-yellow-700 dark:text-yellow-300">
                  {{ grupo.fecha_aplazada }}
                </span>
              </div>
            </div>
          </div>

          <div
            class="p-4 bg-gray-50 dark:bg-gray-800/50 border-t dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2"
              :class="grupo.estado === 1 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
              <component :is="grupo.estado === 1 ? CheckCircleIcon : ClockIcon" class="h-5 w-5" />
              <span class="text-sm font-semibold">
                {{ grupo.estado === 1 ? 'Activo' : grupo.estado === 4 ? 'Finalizado' : 'Desconocido' }}
              </span>
            </div>

            <span class="text-xs text-gray-500 dark:text-gray-400">
              {{ grupo.created_at }}
            </span>
          </div>
        </div>
      </div>

      <div v-if="totalPages > 1" class="flex items-center justify-center pt-6">
        <nav class="inline-flex -space-x-px rounded-md shadow-sm">
          <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
            class="relative inline-flex items-center rounded-l-md px-3 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50">
            Anterior
          </button>
          <span
            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 ring-1 ring-inset ring-gray-300">
            Página {{ currentPage }} de {{ totalPages }}
          </span>
          <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages"
            class="relative inline-flex items-center rounded-r-md px-3 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50">
            Siguiente
          </button>
        </nav>
      </div>
    </div>

    <div v-else class="text-center py-20">
      <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
        No se encontró la programación
      </h3>
    </div>

    <div v-if="openMenuId" @click.stop="openMenuId = null" class="fixed inset-0 z-10"></div>

    <!-- Slider 1: Subir documentos -->
    <DocumentoItemsSlider v-show="showItemsSlider" :show="showItemsSlider" @hide="showItemsSlider = false"
      :grupo="selectedGrupoPlazo" />

    <!-- Slider 2: Observaciones / plazo -->
    <DocumentoPlazo v-show="showPlazoSlider" :show="showPlazoSlider" @hided="showPlazoSlider = false"
      :grupo="selectedGrupoPlazo" :load="programacionStore?.programacionSubidos?.programacion?.id" />


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