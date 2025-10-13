<script setup>
import { ref, computed } from "vue";
import * as yup from "yup";
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../page/AuthorizationFallback.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
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
    { label: '1 día', value: 1 },
    { label: '2 días', value: 2 },
    { label: '3 días', value: 3 },
    { label: '4 días', value: 4 },
    { label: '5 días', value: 5 },
];

const schema = yup.object().shape({
    dias_aplazados: yup.number().required("Debe seleccionar los días de prórroga."),
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
        <form @submit.prevent="onSubmit" class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Seleccione los días de prórroga para la entrega del grupo:
                <span class="font-bold">{{ grupo.grupo_detalle.nombre_especialidad }} - Sección {{ grupo.grupo_detalle.seccion }}</span>.
            </p>

            <FormLabelError label="Días de Prórroga *">
                <div class="form-input-wrapper">
                    <v-select
                        v-model="formData.dias_aplazados"
                        :options="diasOptions"
                        label="label"
                        :reduce="opt => opt.value"
                        placeholder="Seleccione una cantidad"
                        class="form-v-select"
                    />
                </div>
                <span v-if="formErrors.dias_aplazados" class="text-xs text-red-500 mt-1">{{ formErrors.dias_aplazados }}</span>
            </FormLabelError>

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

<style>
.form-input-wrapper {
  @apply w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm;
}
.form-v-select .vs__dropdown-toggle {
  @apply w-full h-[38px] border-none p-0;
}
.form-v-select .vs__search,
.form-v-select .vs__selected {
  @apply m-0 p-0 pl-3 text-sm text-gray-900 dark:text-gray-300;
}
.form-v-select .vs__actions {
  @apply p-0 pr-2;
}
</style>