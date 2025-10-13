<script setup>
import { ref, computed } from "vue";
import * as yup from "yup";
import { ArrowUpTrayIcon, XCircleIcon, PaperClipIcon } from '@heroicons/vue/24/outline';

import FormInput from "../../ui/FormInput.vue";
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

const emit = defineEmits(["form-submitted"]);

const { showToast } = useModalToast();
const { runYupValidation } = useValidation();
const { update, updating } = useHttpRequest("/entrega_docente");

const fileInput = ref(null);
const formErrors = ref({});

const formData = ref({
    documentos: [],
    descripcion: '',
    observacion: props.grupo?.observacion || "",
});

const schema = yup.object().shape({
    documentos: yup.array().min(1, "Debe seleccionar al menos un archivo."),
    descripcion: yup.string().nullable(),
    observacion: yup.string().nullable(),
});

const requiredPermissions = computed(() => {
    return ["todo-acceso-programacion-documentos-subidos", "editar-programacion-documentos-subidos"];
});

const handleFileChange = (event) => {
    formData.value.documentos = Array.from(event.target.files);
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const removeFile = (index) => {
    formData.value.documentos.splice(index, 1);
};

const onSubmit = async () => {
    const { validated, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    const dataPayload = new FormData();
    formData.value.documentos.forEach((file) => {
        dataPayload.append('documentos[]', file);
    });
    if (formData.value.descripcion) {
        dataPayload.append('descripcion', formData.value.descripcion);
    }
    if (formData.value.observacion) {
        dataPayload.append('observacion', formData.value.observacion);
    }
    
    dataPayload.append('_method', 'PATCH');

    try {
        const response = await update(props.grupo.id, dataPayload);
        if (response) {
            showToast("Entrega actualizada con éxito.", "success");
            emit("form-submitted");
        }
    } catch (error) {
        showToast("Ocurrió un error al actualizar la entrega.", "error");
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <form @submit.prevent="onSubmit" class="space-y-5">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Gestione los archivos para la entrega del grupo: 
                <span class="font-bold">{{ grupo.grupo_detalle.nombre_especialidad }} - Sección {{ grupo.grupo_detalle.seccion }}</span>.
            </p>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Documentos de Entrega *
                </label>
                <div 
                    @click="triggerFileInput"
                    class="mt-1 flex justify-center items-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md cursor-pointer hover:border-cetpro dark:hover:border-cetpro-light"
                >
                    <div class="space-y-1 text-center">
                        <ArrowUpTrayIcon class="mx-auto h-10 w-10 text-gray-400" />
                        <div class="flex text-sm text-gray-600 dark:text-gray-400">
                            <p class="pl-1">Haz clic para seleccionar uno o varios archivos</p>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-500">Cualquier tipo de archivo</p>
                    </div>
                    <input ref="fileInput" type="file" @change="handleFileChange" class="sr-only" multiple />
                </div>
                <p v-if="formErrors.documentos" class="mt-1 text-xs text-red-500">{{ formErrors.documentos }}</p>
                
                <div v-if="formData.documentos.length > 0" class="mt-3 space-y-2">
                    <div v-for="(file, index) in formData.documentos" :key="index" class="flex items-center justify-between text-sm p-2 bg-gray-100 dark:bg-gray-700 rounded-md">
                        <div class="flex items-center gap-2 truncate">
                            <PaperClipIcon class="h-4 w-4 text-gray-500" />
                            <span class="text-gray-800 dark:text-gray-200 truncate">{{ file.name }}</span>
                        </div>
                        <button @click="removeFile(index)" type="button" class="text-red-500 hover:text-red-700">
                            <XCircleIcon class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
            
            <FormInput 
                v-model="formData.descripcion" 
                label="Descripción (Opcional)" 
                :error-message="formErrors.descripcion"
                placeholder="Describe el contenido de los archivos..."
            />
            
            <FormInput 
                v-model="formData.observacion" 
                label="Observación  (Opcional)" 
                :error-message="formErrors.observacion"
                placeholder="Escribe alguna observación importante..." 
                type="textarea"
            />

            <div class="flex justify-end gap-2 pt-4">
                <Button 
                    title="Guardar Cambios" 
                    type="submit"
                    :loading="updating" 
                    :disabled="updating" 
                />
            </div>
        </form>
    </AuthorizationFallback>
</template>