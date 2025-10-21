<script setup>
import { ref, computed } from "vue";
import * as yup from "yup";
import { PaperClipIcon, XCircleIcon } from "@heroicons/vue/24/outline";

import FormInput from "../../ui/FormInput.vue";
import FormInputFile from "../../ui/FormFileInput.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../page/AuthorizationFallback.vue";
import useModalToast from "../../../composables/useModalToast";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";

const props = defineProps({
    grupo: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["hide"]);

const { showToast } = useModalToast();
const { runYupValidation } = useValidation();
const { update, updating } = useHttpRequest("/entrega_docente");

const formErrors = ref({});

// 📁 Datos iniciales del formulario
const initialFormData = () => ({
    documento: null,
    descripcion: "",
    observacion: props.grupo?.observacion || "",
});

const formData = ref(initialFormData());

// 🧾 Validación con Yup
const schema = yup.object().shape({
    documento: yup
        .mixed()
        .required("Debe seleccionar un archivo.")
        .nullable(),
    descripcion: yup.string().nullable().max(255),
    observacion: yup.string().nullable().max(255),
});

const requiredPermissions = computed(() => [
    "todo-acceso-programacion-documentos-subidos",
    "editar-programacion-documentos-subidos",
]);

// 🗑️ Quitar archivo
const removeFile = () => {
    formData.value.documento = null;
};

// 📤 Envío del formulario
const onSubmit = async () => {
    if (updating.value) return;

    const data = { ...formData.value };

    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    formErrors.value = {};

    // Tu composable ya convierte a FormData si detecta archivos 👇
    const response = await update(props.grupo.id, data);

    if (response) {
        showToast("Entrega actualizada con éxito.", "success");
        emit("hide");
        formData.value = initialFormData();
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
            Gestione el archivo de entrega del grupo:
            <span class="font-bold">
                {{ grupo.grupo_detalle.nombre_especialidad }} - Sección
                {{ grupo.grupo_detalle.seccion }}
            </span>.
        </p>

        <FormInputFile v-model="formData.documento" label="Documento principal *"
            :error-message="formErrors.documento" />


        <div v-if="formData.documento" class="mt-3">
            <div class="flex items-center justify-between text-sm p-2 bg-gray-100 dark:bg-gray-700 rounded-md">
                <div class="flex items-center gap-2 truncate">
                    <PaperClipIcon class="h-4 w-4 text-gray-500" />
                    <span class="text-gray-800 dark:text-gray-200 truncate">
                        {{ formData.documento.name }}
                    </span>
                </div>
                <button @click="removeFile" type="button" class="text-red-500 hover:text-red-700">
                    <XCircleIcon class="h-5 w-5" />
                </button>
            </div>
        </div>

        <FormInput v-model="formData.descripcion" label="Descripción (Opcional)" :error-message="formErrors.descripcion"
            placeholder="Describe el contenido del archivo..." />

        <FormInput v-model="formData.observacion" label="Observación (Opcional)" :error-message="formErrors.observacion"
            placeholder="Escribe alguna observación importante..." />

        <div class="flex justify-end gap-2 pt-4">
            <Button title="Guardar Cambios" type="button" :loading="updating" :disabled="updating" @click="onSubmit" />
        </div>
    </AuthorizationFallback>
</template>
