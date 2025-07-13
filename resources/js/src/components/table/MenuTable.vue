<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from "vue";

const props = defineProps({
  actions: {
    type: Object,
    default: () => ({
      view: false,
      edit: false,
      delete: false,
      download: false,
    }),
  },
  entityLabel: {
    type: String,
    default: "elemento",
  },
});

const emit = defineEmits(["view", "edit", "delete", "download"]);

const isOpen = ref(false);
const positionTop = ref(true);
const menuRef = ref(null);
const buttonRef = ref(null);

const toggleMenu = async () => {
  isOpen.value = !isOpen.value;

  if (isOpen.value) {
    await nextTick();

    const button = buttonRef.value;
    if (!button || typeof button.getBoundingClientRect !== "function") {
      console.warn("buttonRef is not a valid DOM element");
      return;
    }

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

const menuStyles = ref({});

const emitAndClose = (action) => {
  emit(action);
  isOpen.value = false;
};

const handleClickOutside = (event) => {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});
onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
  <div class="relative inline-block text-left" ref="menuRef">
    <button
      ref="buttonRef"
      @click="toggleMenu"
      class="text-xl hover:text-cetpro dark:hover:text-cetpro-light"
    >
      ⋮
    </button>

    <transition name="fade">
      <div
        v-if="isOpen"
        class="fixed z-40 w-[180px]  origin-top-right rounded-md bg-white dark:bg-gray-800 dark:ring-white dark:ring-opacity-20  shadow-lg ring-1 ring-black ring-opacity-5"
        :style="menuStyles"
      >
        <div class="py-1 text-sm text-gray-700  dark:text-gray-200">
          <button
            v-if="actions.view"
            @click="emitAndClose('view')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <EyeIcon class="w-5 h-5" />
            Ver {{ entityLabel }}
          </button>

          <button
            v-if="actions.edit"
            @click="emitAndClose('edit')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <PencilIcon class="w-5 h-5" />
            Editar {{ entityLabel }}
          </button>

          <button
            v-if="actions.delete"
            @click="emitAndClose('delete')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <TrashIcon class="w-5 h-5" />
            Eliminar {{ entityLabel }}
          </button>

          <button
            v-if="actions.download"
            @click="emitAndClose('download')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <ArrowDownTrayIcon class="w-5 h-5" />
            Descargar {{ entityLabel }}
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
