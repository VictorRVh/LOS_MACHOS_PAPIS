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
  download: "Descargar Nomina",
  report: "Descargar Acta",
  reportConsol: "Descargar Consolidad",
  custom1: "Accion personalizada",
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
  "report",
  "reportConsol",
  "custom1",
  "deactivate",
]);

const isOpen = ref(false);
const menuRef = ref(null);
const buttonRef = ref(null);
const menuStyles = ref({});

const toggleMenu = async () => {
  isOpen.value = !isOpen.value;
  if (!isOpen.value) return;

  await nextTick();

  const button = buttonRef.value;
  const menu = menuRef.value;

  const buttonRect = button.getBoundingClientRect();
  const tableRect = button.closest("td, th").getBoundingClientRect();

  const menuHeight = menu.offsetHeight;
  const spaceBelow = window.innerHeight - buttonRect.bottom;

  let top;
  if (spaceBelow < menuHeight) {
    top = buttonRect.top - tableRect.top - menuHeight - 5;
  } else {
    top = buttonRect.bottom - tableRect.top - 28;
  }

  const left = buttonRect.left - tableRect.left - 195 + 20;

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
  <div class="relative inline-block text-left">
    <button
      ref="buttonRef"
      @click="toggleMenu"
      class="inline-flex h-7 w-7 items-center justify-center text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cetpro/30 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">
      <EllipsisVerticalIcon class="h-4.5 w-4.5" />
    </button>

    <div
      v-if="isOpen"
      ref="menuRef"
      :style="menuStyles"
      class="absolute w-[190px] rounded-md border border-slate-200 bg-white py-1 text-sm shadow-sm ring-1 ring-slate-200 dark:border-slate-700 dark:bg-gray-800 dark:ring-slate-700">
      <button
        v-if="actions.view"
        @click="emitAndClose('view')"
        class="flex w-full items-center gap-3 px-3 py-2 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
        <EyeIcon class="h-4 w-4" /> {{ mergedLabels.view }} {{ entityLabel }}
      </button>

      <button
        v-if="actions.edit"
        @click="emitAndClose('edit')"
        class="flex w-full items-center gap-3 px-3 py-2 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
        <PencilSquareIcon class="h-4 w-4" /> {{ mergedLabels.edit }}
        {{ entityLabel }}
      </button>

      <button
        v-if="actions.custom1"
        @click="emitAndClose('custom1')"
        class="flex w-full items-center gap-3 px-3 py-2 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
        <CalendarDaysIcon class="h-4 w-4" /> {{ mergedLabels.custom1 }}
      </button>

      <hr
        v-if="actions.view || actions.edit || actions.custom1"
        class="mx-2 my-1 border-slate-200 dark:border-slate-700" />

      <button
        v-if="actions.deactivate"
        @click="emitAndClose('deactivate')"
        class="flex w-full items-center gap-3 px-3 py-2 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
        <ExclamationTriangleIcon class="h-4 w-4" /> {{ mergedLabels.deactivate }}
        {{ entityLabel }}
      </button>

      <button
        v-if="actions.delete"
        @click="emitAndClose('delete')"
        class="flex w-full items-center gap-3 px-3 py-2 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
        <TrashIcon class="h-4 w-4" /> {{ mergedLabels.delete }} {{ entityLabel }}
      </button>

      <button
        v-if="actions.report"
        @click="emitAndClose('report')"
        class="flex w-full items-center gap-3 px-3 py-2 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
        <ArrowDownTrayIcon class="h-4 w-4" />
        {{ mergedLabels.report }} {{ entityLabel }}
      </button>

      <button
        v-if="actions.download"
        @click="emitAndClose('download')"
        class="flex w-full items-center gap-3 px-3 py-2 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
        <ArrowDownTrayIcon class="h-4 w-4" />
        {{ mergedLabels.download }} {{ entityLabel }}
      </button>

      <button
        v-if="actions.reportConsol"
        @click="emitAndClose('reportConsol')"
        class="flex w-full items-center gap-3 px-3 py-2 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
        <ArrowDownTrayIcon class="h-4 w-4" />
        {{ mergedLabels.reportConsol }} {{ entityLabel }}
      </button>
    </div>
  </div>
</template>
