<script setup>
import { ref, watch } from "vue";
import { XCircleIcon, ArrowUpTrayIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
  modelValue: {
    type: [Array, File, null],
    default: () => [],
  },
  label: {
    type: String,
    required: true,
  },
  errorMessage: {
    type: String,
    default: "",
  },
  multiple: {
    type: Boolean,
    default: false, // si es true, permite varios archivos
  },
});

const emit = defineEmits(["update:modelValue"]);

const fileInput = ref(null);
const fileNames = ref([]);

// 🧠 Sincroniza nombres con el modelo externo
watch(
  () => props.modelValue,
  (newVal) => {
    if (props.multiple && Array.isArray(newVal)) {
      fileNames.value = newVal.map((f) => f.name);
    } else if (newVal instanceof File) {
      fileNames.value = [newVal.name];
    } else {
      fileNames.value = [];
    }
  },
  { immediate: true }
);

// 📂 Seleccionar archivos
const handleFileChange = (event) => {
  const files = Array.from(event.target.files);

  if (props.multiple) {
    emit("update:modelValue", files);
    fileNames.value = files.map((f) => f.name);
  } else {
    const file = files[0] || null;
    emit("update:modelValue", file);
    fileNames.value = file ? [file.name] : [];
  }
};

// ❌ Eliminar archivo
const removeFile = (index) => {
  if (props.multiple) {
    const updatedFiles = [...props.modelValue];
    updatedFiles.splice(index, 1);
    emit("update:modelValue", updatedFiles);
    fileNames.value = updatedFiles.map((f) => f.name);
    if (updatedFiles.length === 0 && fileInput.value) fileInput.value.value = "";
  } else {
    emit("update:modelValue", null);
    fileNames.value = [];
    if (fileInput.value) fileInput.value.value = "";
  }
};

// 📎 Activar input oculto
const triggerFileInput = () => {
  fileInput.value.click();
};
</script>

<template>
  <div>
    <label
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
    >
      {{ label }}
    </label>

    <div
      @click="triggerFileInput"
      class="mt-1 flex flex-col justify-center items-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md cursor-pointer hover:border-cetpro dark:hover:border-cetpro-light"
    >
      <ArrowUpTrayIcon class="mx-auto h-10 w-10 text-gray-400" />

      <div
        v-if="fileNames.length === 0"
        class="text-sm text-gray-600 dark:text-gray-400"
      >
        Haz clic para seleccionar {{ multiple ? "archivos" : "un archivo" }}
      </div>

      <ul
        v-else
        class="text-sm font-semibold text-cetpro dark:text-cetpro-light mt-2 space-y-1 w-full"
      >
        <li
          v-for="(name, index) in fileNames"
          :key="index"
          class="flex items-center justify-between gap-2"
        >
          <span class="truncate">{{ name }}</span>
          <button
            @click.stop="removeFile(index)"
            class="text-red-500 hover:text-red-700"
          >
            <XCircleIcon class="h-5 w-5" />
          </button>
        </li>
      </ul>

      <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
        {{ multiple ? "Puedes seleccionar varios archivos" : "Solo un archivo permitido" }}
      </p>

      <input
        ref="fileInput"
        type="file"
        :multiple="multiple"
        @change="handleFileChange"
        class="sr-only"
      />
    </div>

    <p v-if="errorMessage" class="mt-1 text-xs text-red-500">
      {{ errorMessage }}
    </p>
  </div>
</template>
