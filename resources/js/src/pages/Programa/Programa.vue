<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
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

if (!programaStore.programa.programas.length) {
  await programaStore.loadPrograma();
}
if (!cicloStore.ciclo.length) {
  await cicloStore.loadCiclo();
}

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();

const onDelete = (programa) => {
  if (programaLoading.value) return;

  showConfirmModal(`¿Seguro que quieres eliminar "${programa?.nombre_ciclo} - ${programa?.año}"?`, async (confirmed) => {
    if (!confirmed) return;
    try {
      await programaStore.removePrograma(programa?.id);
      showToast(`Programa eliminado exitosamente.`);
    } catch (error) {
      showToast('Error al eliminar el programa.', 'error');
    }
  });
};

console.log('programas bread', programaStore?.programa?.programas)

// Esta función ahora se llamará al hacer clic en la tarjeta
const SeeMore = (programa) => {
  router.push({
    name: "especialidadPrograma",
    params: { idPrograma: programa.id },
  });
};

const openMenuId = ref(null);

const toggleMenu = (programaId) => {
  openMenuId.value = openMenuId.value === programaId ? null : programaId;
};

const handleAction = (action, programa) => {
  action(programa);
  openMenuId.value = null;
};
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Programa de estudio</h2>
    </div>

    <div class="flex flex-col lg:flex-row px-6 gap-6">
      <div class="lg:w-1/3 w-full bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 h-fit">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Agregar Programa
        </h3>
        <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
        <ProgramaSlider :show="slider" :programa="sliderData" :ciclo="cicloStore.ciclo" @hide="hideSlider" />
      </div>

      <!-- Contenedor de tarjetas con espacio reducido (space-y-3) -->
      <div class="lg:w-2/3 w-full space-y-1">
        <!-- Tarjeta clicleable con padding y hover effect reducido -->
        <div 
          v-for="programa in programaStore?.programa?.programas" 
          :key="programa.id"
          @click="SeeMore(programa)"
          class="relative flex items-center bg-white dark:bg-gray-800 rounded-lg shadow-md p-3 border-l-4 cursor-pointer transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700/50"
          :class="[programa.status ? 'border-green-500' : 'border-red-500']"
        >
          <!-- Contenido de la tarjeta con gap reducido -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 flex-grow">
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

          <!-- Botón de Acciones con @click.stop para evitar la navegación -->
          <div class="relative ml-4" @click.stop>
            <button @click="toggleMenu(programa.id)" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
              </svg>
            </button>
            <div 
              v-if="openMenuId === programa.id" 
              class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-900 rounded-md shadow-lg z-20 border border-gray-200 dark:border-gray-700"
            >
              <ul class="py-1">
                <li>
                  <a href="#" @click.prevent="handleAction(() => showSlider(true, programa), programa)" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">Editar</a>
                </li>
                <li>
                  <a href="#" @click.prevent="handleAction(onDelete, programa)" class="block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-800">Eliminar</a>
                </li>
                <!-- La opción de "Asignar especialidades" ya no es necesaria aquí -->
              </ul>
            </div>
          </div>
        </div>

        <div v-if="!programaStore?.programa?.programas?.length && !programaLoading" class="text-center py-10 bg-white dark:bg-gray-800 rounded-lg shadow-md">
            <p class="text-gray-500 dark:text-gray-400">No hay programas para mostrar.</p>
        </div>
      </div>
    </div>
    
    <div v-if="openMenuId" @click="openMenuId = null" class="fixed inset-0 z-10"></div>
  </AuthorizationFallback>
</template>