<script setup>
import {
  EyeIcon,
  PencilSquareIcon,
  TrashIcon,
  ArrowDownTrayIcon,
  CalendarDaysIcon,
  EllipsisVerticalIcon,
  ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";
import { ref, onMounted, onBeforeUnmount, nextTick, computed } from "vue";

const props = defineProps({
  actions: {
    type: Object,
    default: () => ({
      view: false,
      edit: false,
      delete: false,
      download: false,
      custom1: false,
      deactivate: false,
    }),
  },
  entityLabel: {
    type: String,
    default: "",
  },
  labels: {
    type: Object,
    default: () => ({
      view: "Ver",
      edit: "Editar",
      delete: "Eliminar",
      download: "Descargar",
      custom1: "Acción personalizada",
      deactivate: "Desactivar",
    }),
  },
});

const defaultLabels = {
  view: "Ver",
  edit: "Editar",
  delete: "Eliminar",
  download: "Descargar",
  custom1: "Acción personalizada",
  deactivate: "Desactivar",
};

// 🔥 Mezcla los labels por defecto con los personalizados enviados
const mergedLabels = computed(() => ({
  ...defaultLabels,
  ...props.labels,
}));

const emit = defineEmits([
  "view",
  "edit",
  "delete",
  "download",
  "custom1",
  "deactivate",
]);

const isOpen = ref(false);
const positionTop = ref(true);
const menuRef = ref(null);
const buttonRef = ref(null);
const menuStyles = ref({});

const toggleMenu = async () => {
  isOpen.value = !isOpen.value;

  if (!isOpen.value) return;

  await nextTick();

  const button = buttonRef.value;
  const menu = menuRef.value;
  const container = menuRef.value.parentElement; // tu contenedor relativo (.relative)

  if (!button || !menu || !container) return;

  // posición del botón dentro del contenedor
  const buttonTop = button.offsetTop;
  const buttonLeft = button.offsetLeft;
  const buttonHeight = button.offsetHeight;

  // altura del menú
  const menuHeight = menu.offsetHeight;

  // altura visible del contenedor (la celda o fila con overflow)
  const containerHeight = container.offsetHeight;

  // espacio libre debajo del botón
  const spaceBelow = containerHeight - (buttonTop + buttonHeight);

  let topPosition;

  // 👉 Si no hay espacio abajo, lo mostramos ARRIBA
  if (spaceBelow < menuHeight) {
    topPosition = buttonTop - menuHeight - 8; // margen de 8px
  } else {
    // 👉 Mostrar abajo
    topPosition = buttonTop + buttonHeight + 4;
  }

  menuStyles.value = {
    top: `${topPosition}px`,
    left: `${buttonLeft - 150}px`,
  };
};


const emitAndClose = (action) => {
  emit(action);
  isOpen.value = false;
};

const handleClickOutside = (event) => {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => document.addEventListener("click", handleClickOutside));
onBeforeUnmount(() =>
  document.removeEventListener("click", handleClickOutside)
);
</script>

<template>
  <div class="relative inline-block text-left" ref="menuRef">
    <button ref="buttonRef" @click="toggleMenu"
      class="text-xl text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
      <EllipsisVerticalIcon class="h-5 w-5" />
    </button>

    <transition name="fade">
      <div v-if="isOpen" class="absolute z-40 w-[190px] origin-top-right rounded-lg 
  bg-white dark:bg-gray-800 
  shadow-lg ring-1 ring-gray-300 dark:ring-gray-700" :style="menuStyles">

        <div class="py-1 text-sm text-gray-300 dark:text-gray-200">

          <!-- VIEW -->
          <button v-if="actions.view" @click="emitAndClose('view')"
            class="flex items-center w-full gap-3 px-4 py-1 hover:bg-gray-100 text-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
            <EyeIcon class="w-4 h-4 text-black dark:text-gray-300" />
            {{ mergedLabels.view }} {{ entityLabel }}
          </button>

          <!-- EDIT -->
          <button v-if="actions.edit" @click="emitAndClose('edit')"
            class="flex items-center w-full gap-3 px-4 py-1 hover:bg-gray-100 text-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
            <PencilSquareIcon class="w-4 h-4 text-black dark:text-gray-300" />
            {{ mergedLabels.edit }} {{ entityLabel }}
          </button>
          <!-- CUSTOM -->
          <button v-if="actions.custom1" @click="emitAndClose('custom1')"
            class="flex items-center  w-full gap-3 px-4 py-1 hover:bg-gray-100 text-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
            <CalendarDaysIcon class="w-4 h-4 text-black dark:text-gray-300" />
            {{ mergedLabels.custom1 }}
          </button>

          <hr class="my-1 mx-2 border-gray-300">


          <!-- DEACTIVATE -->
          <button v-if="actions.deactivate" @click="emitAndClose('deactivate')"
            class="flex items-center w-full gap-3 px-4 py-1 hover:bg-gray-100 text-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
            <ExclamationTriangleIcon class="w-4 h-4 text-black dark:text-gray-300" /> 
            {{ mergedLabels.deactivate }} {{ entityLabel }}
          </button>

          <!-- DELETE -->
          <button v-if="actions.delete" @click="emitAndClose('delete')"
            class="flex items-center w-full gap-3 px-4 py-1 hover:bg-gray-100 text-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
            <TrashIcon class="w-4 h-4 text-black dark:text-gray-300" />
            {{ mergedLabels.delete }} {{ entityLabel }}
          </button>

          <!-- DOWNLOAD -->
          <button v-if="actions.download" @click="emitAndClose('download')"
            class="flex items-center w-full gap-3 px-4 py-1 hover:bg-gray-100 text-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
            <ArrowDownTrayIcon class="w-4 h-4 text-black dark:text-gray-300" />
            {{ mergedLabels.download }} {{ entityLabel }}
          </button>





        </div>


      </div>
    </transition>
  </div>
</template>


<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease-in-out;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
