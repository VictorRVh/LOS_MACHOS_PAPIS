<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { PencilSquareIcon, TrashIcon } from "@heroicons/vue/24/outline";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import ProgramaSlider from "../../components/page/Programa/ProgramaSlider.vue";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useProgramaStore from "../../store/Programa/useProgramaStore";
import useCicloStore from "../../store/Ciclo/useCicloStore";

const router = useRouter();
const programaStore = useProgramaStore();
const cicloStore = useCicloStore();

const { programaLoading } = storeToRefs(programaStore);


if (!programaStore.programa.length) await programaStore.loadPrograma();
if (!cicloStore?.ciclo?.length) await cicloStore.loadCiclo();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();

const filtroCiclo = ref('Ciclo Técnico');

const programasFiltrados = computed(() => {
  if (!programaStore.programa.programas) return [];
  return programaStore.programa.programas.filter(p => p.nameCiclo === filtroCiclo.value);
});

const handleProgramaGuardado = (programaGuardado) => {
  const nombreCiclo = cicloStore.ciclo.find(c => c.id === programaGuardado.id_ciclo)?.nombre_ciclo || '';
  if (nombreCiclo) {
    filtroCiclo.value = nombreCiclo;
  }
};

const onDelete = async (programa) => {
  if (programaLoading.value) return;
  showConfirmModal(`¿Seguro que quieres eliminar "${programa?.nameCiclo} - ${programa?.año}"?`, async (confirmed) => {
    if (!confirmed) return;
    try {
      await programaStore.removePrograma(programa.id);
      showToast(`Programa eliminado exitosamente.`);
    } catch (error) {
      showToast('Error al eliminar el programa.', 'error');
    }
  });
};

const seeMore = (programa) => {
  router.push({
    name: "especialidadPrograma",
    params: { idPrograma: programa.id },
  });
};

const tooltip = ref({
  visible: false,
  text: 'Asignar Especialidades',
  x: 0,
  y: 0,
});

const showTooltip = () => {
  tooltip.value.visible = true;
};

const hideTooltip = () => {
  tooltip.value.visible = false;
};

const updateTooltipPos = (event) => {
  if (tooltip.value.visible) {
    tooltip.value.x = event.clientX + 15;
    tooltip.value.y = event.clientY + 15;
  }
};
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-programas', 'ver-programas']">
    <div class="p-4 md:p-6 space-y-6">
      <header>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Programas de Estudio</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Administra los programas de ciclo técnico y auxiliar.</p>
      </header>
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 h-fit bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
          <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
            Agregar Programa
          </h3>
          <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
          <ProgramaSlider 
            :show="true" 
            :programa="sliderData" 
            :ciclo="cicloStore.ciclo" 
            @hide="hideSlider" 
            @programa-guardado="handleProgramaGuardado"
          />
        </div>

        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center space-x-2 bg-white dark:bg-gray-800 p-2 rounded-lg shadow-sm">
            <button
              @click="filtroCiclo = 'Ciclo Técnico'"
              :class="['w-full py-2 px-4 rounded-md text-sm font-semibold transition-colors', 
                       filtroCiclo === 'Ciclo Técnico' ? 'bg-cetpro text-white shadow' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700']">
              Ciclo Técnico
            </button>
            <button
              @click="filtroCiclo = 'Ciclo Auxiliar Técnico'"
              :class="['w-full py-2 px-4 rounded-md text-sm font-semibold transition-colors', 
                       filtroCiclo === 'Ciclo Auxiliar Técnico' ? 'bg-cetpro text-white shadow' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700']">
              Ciclo Auxiliar Técnico
            </button>
          </div>

          <div class="space-y-3" @mousemove="updateTooltipPos">
            <div 
              v-for="programa in programasFiltrados" 
              :key="programa.id"
              class="bg-white dark:bg-gray-800 rounded-lg shadow-md border-l-4 flex"
              :class="[programa.status ? 'border-green-500' : 'border-red-500']"
            >
              <div 
                @click="seeMore(programa)"
                @mouseover="showTooltip"
                @mouseleave="hideTooltip"
                class="flex-grow p-3 cursor-pointer rounded-l-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 items-center h-full">
                  <div class="flex flex-col">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Programa</span>
                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">{{ programa.nameCiclo }}</p>
                  </div>
                  <div class="flex flex-col">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Periodo</span>
                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">{{ programa.año }}</p>
                  </div>
                  <div class="flex flex-col">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Resolución</span>
                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">RD {{ programa.numero_rd }}</p>
                  </div>
                  <div class="flex flex-col">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Estado</span>
                    <span 
                      class="text-xs font-bold py-1 px-2 rounded-full w-fit"
                      :class="[programa.status ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300']"
                    >
                      {{ programa.status ? 'En Curso' : 'Finalizado' }}
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="flex-shrink-0 flex flex-col items-center justify-center space-y-2 px-3 py-2 border-l border-gray-200 dark:border-gray-700">
                <button @click="showSlider(true, programa)" title="Editar" class="p-2 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 transition-colors duration-200 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                    <PencilSquareIcon class="h-5 w-5" />
                </button>
                 <button @click="onDelete(programa)" title="Eliminar" class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors duration-200 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                    <TrashIcon class="h-5 w-5" />
                </button>
              </div>
            </div>

            <div v-if="!programasFiltrados.length && !programaLoading" class="text-center py-10 bg-white dark:bg-gray-800 rounded-lg shadow-md">
              <p class="text-gray-500 dark:text-gray-400">No hay programas para mostrar en esta categoría.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div 
      v-if="tooltip.visible"
      class="fixed z-50 px-3 py-1.5 bg-cetpro-dark text-white text-xs font-semibold rounded-md shadow-lg pointer-events-none transition-opacity duration-200"
      :style="{ left: `${tooltip.x}px`, top: `${tooltip.y}px` }"
    >
      {{ tooltip.text }}
    </div>
  </AuthorizationFallback>
</template>