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
  actions: Object,
  entityLabel: String,
  labels: Object,
});

const defaultLabels = {
  view: "Ver",
  edit: "Editar",
  delete: "Eliminar",
  download: "Descargar",
  custom1: "Acción personalizada",
  deactivate: "Desactivar",
};

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
const menuRef = ref(null);
const buttonRef = ref(null);
const menuStyles = ref({});

// ⭐⭐⭐ AQUÍ ESTÁ LA SOLUCIÓN ⭐⭐⭐
const toggleMenu = async () => {
  isOpen.value = !isOpen.value;
  if (!isOpen.value) return;

  await nextTick();

  const button = buttonRef.value;
  const menu = menuRef.value;

  const buttonRect = button.getBoundingClientRect();
  const tableRect = button.closest("td, th").getBoundingClientRect(); // ⭐ RELATIVO A LA CELDA

  const menuHeight = menu.offsetHeight;
  const spaceBelow = window.innerHeight - buttonRect.bottom;

  let top;
  if (spaceBelow < menuHeight) {
    // arriba
    top = buttonRect.top - tableRect.top - menuHeight - 5;
  } else {
    // abajo
    top = buttonRect.bottom - tableRect.top - 28;
  }

  const left =
    buttonRect.left - tableRect.left - 195 + 20; // pegado al borde derecho

  menuStyles.value = {
    position: "absolute",
    top: `${top}px`,
    left: `${left}px`,
    zIndex: 999,
  };
};

const emitAndClose = (action) => {
  emit(action);
  isOpen.value = false;
};

const handleClickOutside = (event) => {
  if (
    menuRef.value &&
    !menuRef.value.contains(event.target) &&
    !buttonRef.value.contains(event.target)
  ) {
    isOpen.value = false;
  }
};

onMounted(() => document.addEventListener("click", handleClickOutside));
onBeforeUnmount(() =>
  document.removeEventListener("click", handleClickOutside)
);
</script>

<template>
  <!-- ⭐ la celda PONE relative aquí ⭐ -->
  <div class="relative inline-block text-left">
    <button
      ref="buttonRef"
      @click="toggleMenu"
      class="text-xl text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
    >
      <EllipsisVerticalIcon class="h-5 w-5" />
    </button>

    <div
      v-if="isOpen"
      ref="menuRef"
      :style="menuStyles"
      class="absolute w-[190px] bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-gray-300 dark:ring-gray-700 py-1 text-sm"
    >
      <button
        v-if="actions.view"
        @click="emitAndClose('view')"
        class="flex items-center gap-3 w-full px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
      >
        <EyeIcon class="w-4 h-4" /> {{ mergedLabels.view }} {{ entityLabel }}
      </button>

      <button
        v-if="actions.edit"
        @click="emitAndClose('edit')"
        class="flex items-center gap-3 w-full px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
      >
        <PencilSquareIcon class="w-4 h-4" /> {{ mergedLabels.edit }}
        {{ entityLabel }}
      </button>

      <button
        v-if="actions.custom1"
        @click="emitAndClose('custom1')"
        class="flex items-center gap-3 w-full px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
      >
        <CalendarDaysIcon class="w-4 h-4" /> {{ mergedLabels.custom1 }}
      </button>

      <hr v-if="actions.view || actions.edit || actions.custom1 " class="my-1 mx-2 border-gray-300" />

      <button
        v-if="actions.deactivate"
        @click="emitAndClose('deactivate')"
        class="flex items-center gap-3 w-full px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
      >
        <ExclamationTriangleIcon class="w-4 h-4" /> {{ mergedLabels.deactivate }}
        {{ entityLabel }}
      </button>

      <button
        v-if="actions.delete"
        @click="emitAndClose('delete')"
        class="flex items-center gap-3 w-full px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
      >
        <TrashIcon class="w-4 h-4" /> {{ mergedLabels.delete }} {{ entityLabel }}
      </button>

      <button
        v-if="actions.download"
        @click="emitAndClose('download')"
        class="flex items-center gap-3 w-full px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
      >
        <ArrowDownTrayIcon class="w-4 h-4" /> {{ mergedLabels.download }}
        {{ entityLabel }}
      </button>
    </div>
  </div>
</template>
