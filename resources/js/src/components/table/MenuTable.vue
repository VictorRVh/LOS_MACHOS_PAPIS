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
    default: "elemento",
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

  if (isOpen.value) {
    await nextTick();

    const button = buttonRef.value;
    if (!button || typeof button.getBoundingClientRect !== "function") return;

    const rect = button.getBoundingClientRect();
    const windowHeight = window.innerHeight;
    const espacioDisponibleAbajo = windowHeight - rect.bottom;
    positionTop.value = espacioDisponibleAbajo > 160;

    menuStyles.value = {
      top: positionTop.value ? `${rect.bottom + 4}px` : `${rect.top - 80}px`,
      left: `${rect.left - 150}px`,
    };
  }
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
    <button
      ref="buttonRef"
      @click="toggleMenu"
      class="text-xl hover:text-cetpro dark:hover:text-cetpro-light"
    >
      <EllipsisVerticalIcon class="h-5 w-5" />
    </button>

    <transition name="fade">
      <div
        v-if="isOpen"
        class="fixed z-40 w-[180px] origin-top-right rounded-md bg-white dark:bg-gray-800 dark:ring-white dark:ring-opacity-20 shadow-lg ring-1 ring-black ring-opacity-5"
        :style="menuStyles"
      >
        <div class="py-1 text-sm text-gray-700 dark:text-gray-200">
          <button
            v-if="actions.view"
            @click="emitAndClose('view')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <EyeIcon class="w-5 h-5" />
            {{ mergedLabels.view }} {{ entityLabel }}
          </button>

          <button
            v-if="actions.edit"
            @click="emitAndClose('edit')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <PencilSquareIcon class="w-5 h-5" />
            {{ mergedLabels.edit }} {{ entityLabel }}
          </button>

          <button
            v-if="actions.delete"
            @click="emitAndClose('delete')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-red-600 dark:text-red-400"
          >
            <TrashIcon class="w-5 h-5" />
            {{ mergedLabels.delete }} {{ entityLabel }}
          </button>

          <button
            v-if="actions.download"
            @click="emitAndClose('download')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-green-600 dark:text-green-400"
          >
            <ArrowDownTrayIcon class="w-5 h-5" />
            {{ mergedLabels.download }} {{ entityLabel }}
          </button>

          <button
            v-if="actions.custom1"
            @click="emitAndClose('custom1')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-yellow-600 dark:text-yellow-400"
          >
            <CalendarDaysIcon class="w-5 h-5" />
            {{ mergedLabels.custom1 }}
          </button>

          <button
            v-if="actions.deactivate"
            @click="emitAndClose('deactivate')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-orange-600 dark:text-orange-400"
          >
            <ExclamationTriangleIcon class="w-5 h-5" />
            {{ mergedLabels.deactivate }} {{ entityLabel }}
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
