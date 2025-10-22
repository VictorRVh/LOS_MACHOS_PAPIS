<script setup>
import { ref, computed, watch } from "vue";
import * as yup from "yup";
import { PaperClipIcon, XCircleIcon, ArrowPathIcon } from "@heroicons/vue/24/outline";

import Slider from "../../ui/Slider.vue";
import FormInputFile from "../../ui/FormFileInput.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../page/AuthorizationFallback.vue";

import useModalToast from "../../../composables/useModalToast";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";

const props = defineProps({
  grupo: { type: Object, required: true },
  show: { type: Boolean, required: true },
});

const emit = defineEmits(["hide"]);

const { showToast } = useModalToast();
const { runYupValidation } = useValidation();

// 💾 composables HTTP
const { store, saving } = useHttpRequest("/drive/upload");
const { show: listarArchivos, loading: loadingFiles } = useHttpRequest("/drive/files");

// 📁 archivos existentes
const archivos = ref([]);

// 📄 formulario
const formData = ref({ documento: null });
const formErrors = ref({});

// ✅ esquema Yup
const schema = yup.object().shape({
  documento: yup.mixed().required("Debe seleccionar un archivo.").nullable(),
});

// 🔐 permisos
const requiredPermissions = computed(() => [
  "todo-acceso-programacion-documentos-subidos",
  "editar-programacion-documentos-subidos",
]);

// 🧹 limpiar archivo seleccionado
const removeFile = () => {
  formData.value.documento = null;
};

// 📂 cargar archivos desde el drive
const loadFiles = async () => {
  const folderId = props.grupo?.carpetas_drive;

  if (!folderId) {
    console.warn("⚠️ No se encontró 'carpetas_drive' en el grupo.");
    archivos.value = [];
    return;
  }

  try {
    // ✅ CORRECTO: ahora se pasa folderId como parámetro query (?folderId=XXXX)
    const response = await listarArchivos( folderId );

    archivos.value = Array.isArray(response)
      ? response
      : response?.files || [];

  } catch (error) {
    console.error("❌ Error al cargar archivos:", error);
    showToast("Error al cargar archivos de la carpeta.", "error");
    archivos.value = [];
  }
};

// 🧩 watch: recargar archivos cada vez que cambie el grupo o se abra el modal
watch(
  () => props.grupo,
  async (nuevoGrupo) => {
    if (nuevoGrupo?.carpetas_drive?.trim()) {
      console.log("📁 Cargando archivos para:", nuevoGrupo?.carpetas_drive);
      formData.value = { documento: null };
      await loadFiles();
    } else {
      console.log("⚠️ grupo sin 'carpetas_drive' válido:", nuevoGrupo);
      archivos.value = [];
    }
  },
  { immediate: true, deep: true }
);

// 🚀 subir archivo
const onSubmit = async () => {
  if (saving.value) return;

  const { validated, errors } = await runYupValidation(schema, formData.value);
  if (!validated) {
    formErrors.value = errors;
    return;
  }

  formErrors.value = {};

  const form = new FormData();
  form.append("file", formData.value?.documento);
  form.append("parentFolderId", props.grupo?.carpetas_drive);

  try {
    const response = await store(form);
    if (response?.id) {
      showToast("Archivo subido con éxito.", "success");
      formData.value.documento = null;
      await loadFiles(); // recargar archivos
    } else {
      showToast("Error al subir el archivo.", "error");
    }
  } catch (error) {
    console.error(error);
    showToast("Error al subir el archivo.", "error");
  }
};

// 📛 título dinámico
const title = computed(() =>
  `Subir archivo de entrega para el grupo: ${
    props.grupo?.grupo_detalle?.nombre_especialidad || ""
  } - Sección ${props.grupo?.grupo_detalle?.seccion || ""}`
);
</script>

<template>
  <Slider :show="show" :title="title" @hide="emit('hide')">
    <AuthorizationFallback :permissions="requiredPermissions">
      <!-- 🗂 campo archivo -->
      <FormInputFile
        v-model="formData.documento"
        label="Documento principal *"
        :error-message="formErrors.documento"
      />

      <!-- 🧾 vista previa -->
      <div v-if="formData.documento" class="mt-3">
        <div
          class="flex items-center justify-between text-sm p-2 bg-gray-100 dark:bg-gray-700 rounded-md"
        >
          <div class="flex items-center gap-2 truncate">
            <PaperClipIcon class="h-4 w-4 text-gray-500" />
            <span class="text-gray-800 dark:text-gray-200 truncate">
              {{ formData.documento.name }}
            </span>
          </div>
          <button
            @click="removeFile"
            type="button"
            class="text-red-500 hover:text-red-700"
          >
            <XCircleIcon class="h-5 w-5" />
          </button>
        </div>
      </div>

      <!-- 🚀 botón -->
      <div class="flex justify-end gap-2 pt-4">
        <Button
          title="Subir Archivo"
          type="button"
          :loading="saving"
          :disabled="saving"
          @click="onSubmit"
        />
      </div>

      <!-- 📁 lista de archivos -->
      <div class="mt-6">
        <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200 mb-2">
          Archivos existentes en la carpeta
        </h3>

        <div v-if="loadingFiles" class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
          <ArrowPathIcon class="animate-spin h-5 w-5" />
          <span>Cargando archivos...</span>
        </div>

        <ul v-else-if="archivos.length" class="space-y-2">
          <li
            v-for="file in archivos"
            :key="file.id"
            class="flex items-center justify-between p-2 bg-gray-100 dark:bg-gray-700 rounded-md text-sm"
          >
            <a
              :href="file.webViewLink"
              target="_blank"
              class="flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:underline truncate"
            >
              <PaperClipIcon class="h-4 w-4" />
              <span class="truncate">{{ file.name }}</span>
            </a>
            <span class="text-xs text-gray-500 dark:text-gray-400">
              {{ file.modifiedTime?.split("T")[0] }}
            </span>
          </li>
        </ul>

        <p v-else class="text-sm text-gray-500 dark:text-gray-400">
          No hay archivos en esta carpeta.
        </p>
      </div>
    </AuthorizationFallback>
  </Slider>
</template>
