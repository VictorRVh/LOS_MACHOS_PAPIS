<script setup>
import { ref, computed } from "vue";
import * as yup from "yup";

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

const formErrors = ref({});

const formData = ref({
    dias_aplazados: null,
});

const diasOptions = [
    { label: '1 día', value: '1' },
    { label: '2 días', value: '2' },
    { label: '3 días', value: '3' },
    { label: '4 días', value: '4' },
    { label: '5 días', value: '5' },
];

const schema = yup.object().shape({
    dias_aplazados: yup.string().required("Debe seleccionar los días de prórroga."),
});

const requiredPermissions = computed(() => {
    return ["todo-acceso-programacion-documentos-subidos", "editar-programacion-documentos-subidos"];
});

const onSubmit = async () => {
    const { validated, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }
    
    try {
        const response = await update(props.grupo.id, formData.value);
        if (response) {
            showToast("Plazo extra habilitado con éxito.", "success");
            emit("form-submitted");
        }
    } catch (error) {
        showToast("Ocurrió un error al habilitar el plazo.", "error");
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <form @submit.prevent="onSubmit" class="space-y-6">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Seleccione los días de prórroga para la entrega del grupo:
                    <span class="font-bold">{{ grupo.grupo_detalle.nombre_especialidad }} - Sección {{ grupo.grupo_detalle.seccion }}</span>.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Días de Prórroga *
                </label>
                <fieldset class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                    <div v-for="option in diasOptions" :key="option.value">
                        <label 
                            :for="`dias_${option.value}`" 
                            class="flex items-center justify-center w-full p-3 text-sm font-medium text-gray-700 bg-white border-2 rounded-lg cursor-pointer dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 transition-colors"
                            :class="{
                                'border-cetpro text-cetpro dark:border-cetpro-light dark:text-cetpro-light ring-2 ring-cetpro/50': formData.dias_aplazados === option.value,
                                'border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700': formData.dias_aplazados !== option.value
                            }"
                        >
                            {{ option.label }}
                        </label>
                        <input 
                            type="radio" 
                            :id="`dias_${option.value}`"
                            v-model="formData.dias_aplazados"
                            :value="option.value"
                            class="sr-only"
                        >
                    </div>
                </fieldset>
                <p v-if="formErrors.dias_aplazados" class="mt-2 text-xs text-red-500">{{ formErrors.dias_aplazados }}</p>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <Button 
                    title="Habilitar Plazo" 
                    type="submit" 
                    :loading="updating" 
                    :disabled="updating" 
                />
            </div>
        </form>
    </AuthorizationFallback>
</template>