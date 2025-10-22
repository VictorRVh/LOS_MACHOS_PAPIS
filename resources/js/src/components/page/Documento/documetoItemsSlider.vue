<script setup>
import { ref, computed } from "vue";
import * as yup from "yup";

import Slider from "../../ui/Slider.vue"; // 👈 asegúrate de importar esto
import FormInputFile from "../../ui/FormFileInput.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../page/AuthorizationFallback.vue";
import useModalToast from "../../../composables/useModalToast";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import { PaperClipIcon, XCircleIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
  grupo: {
    type: Object,
    required: true,
  },
  show: {
    type: Boolean,
    required: true,
  },

});

const emit = defineEmits(["hide"]);
const title = `Subir archivo de entrega para el grupo: ${props.grupo?.grupo_detalle?.nombre_especialidad } - Sección ${props.grupo?.grupo_detalle?.seccion}`

const { showToast } = useModalToast();
const { runYupValidation } = useValidation();
const { store, saving } = useHttpRequest("/drive/upload");

const formErrors = ref({});
const formData = ref({
  documento: null,
});

const schema = yup.object().shape({
  documento: yup.mixed().required("Debe seleccionar un archivo.").nullable(),
});

const requiredPermissions = computed(() => [
  "todo-acceso-programacion-documentos-subidos",
  "editar-programacion-documentos-subidos",
]);

const removeFile = () => {
  formData.value.documento = null;
};

const onSubmit = async () => {
  if (saving.value) return;

  const { validated, errors } = await runYupValidation(schema, formData.value);
  if (!validated) {
    formErrors.value = errors;
    return;
  }

  formErrors.value = {};

  const form = new FormData();
  form.append("file", formData.value.documento);
  form.append("parentFolderId", props.grupo.carpetas_drive);

  try {
    const response = await store(form);
    if (response?.id) {
      showToast("Archivo subido con éxito.", "success");
      formData.value.documento = null;
      emit("hide"); // 👈 esto cierra el modal correctamente
    } else {
      showToast("Error al subir el archivo.", "error");
    }
  } catch (error) {
    console.error(error);
    showToast("Error al subir el archivo.", "error");
  }
};
</script>

<template>
  <!-- Todo el contenido del formulario va dentro del Slider -->
  <Slider :show="show" :title="title" @hide="emit('hide')">
    <AuthorizationFallback :permissions="requiredPermissions">
      <FormInputFile
        v-model="formData.documento"
        label="Documento principal *"
        :error-message="formErrors.documento"
      />

      <!-- Vista previa -->
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

      <!-- Botón de envío -->
      <div class="flex justify-end gap-2 pt-4">
        <Button
          title="Subir Archivo"
          type="button"
          :loading="saving"
          :disabled="saving"
          @click="onSubmit"
        />
      </div>
    </AuthorizationFallback>
  </Slider>
</template>
