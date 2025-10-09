<script setup>
import { ref, watch } from "vue";
import * as yup from "yup";

import FormInput from "../../ui/FormInput.vue";
import CheckBox from "../../ui/CheckBox.vue";
import Button from "../../ui/Button.vue";

import useModalToast from "../../../composables/useModalToast";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";


import AuthorizationFallback from "../../../components/page/AuthorizationFallback.vue";

const props = defineProps({
    programacionToEdit: {
        type: Object,
        default: null,
    },
    idProgrmacionAdmin: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["form-submitted", "cancel-edit"]);

const { showToast } = useModalToast();
const { runYupValidation } = useValidation();
const { store, update, saving, updating } = useHttpRequest("/entrega_docente");

const isEditing = ref(false);
const formErrors = ref({});

const initialFormData = () => ({
    fecha_inicio: "",
    fecha_fin: "",
    estado: 0,
    id_admin: "",
    documento_admin: "",
    observacion: "",
    fecha_aplazada: "",
    dias_aplazados: "",
});

const formData = ref(initialFormData());

const requiredPermissions = computed(() => {
  return props.comision?.id
    ? ["todo-acceso-programacion-documentos-subidos", "editar-programacion-documentos-subidos"]
    : ["todo-acceso-programacion-documentos-subidos", "crear-programacion-documentos-subidos"];
});


const schema = yup.object().shape({
    fecha_inicio: yup
        .date()
        .required("La fecha de inicio es requerida."),
    fecha_fin: yup
        .date()
        .required("La fecha de fin es requerida.")
        .min(yup.ref("fecha_inicio"), "La fecha de fin no puede ser anterior a la de inicio."),
    observacion: yup.string().nullable(),
    fecha_aplazada: yup.date().nullable(),
    dias_aplazados: yup.string().nullable(),
    documento_admin: yup.string().required("El documento del administrador es requerido."),
    id_admin: yup.string().required("El ID del administrador es requerido."),
});

watch(
    () => props.programacionToEdit,
    (newVal) => {
        if (newVal) {
            formData.value = { ...newVal };
            isEditing.value = true;
            formErrors.value = {};
            window.scrollTo({ top: 0, behavior: "smooth" });
        } else {
            resetForm();
        }
    },
    { deep: true }
);

const resetForm = () => {
    formData.value = initialFormData();
    isEditing.value = false;
    formErrors.value = {};
    emit("cancel-edit");
};

const onSubmit = async () => {
    const { validated, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    try {
        let response;
        if (isEditing.value) {
            response = await update(formData.value.id, formData.value);
        } else {
            response = await store(formData.value);
        }

        if (response) {
            showToast(
                `Entrega ${isEditing.value ? "actualizada" : "registrada"} con éxito.`,
                "success"
            );
            emit("form-submitted");
        }
    } catch (error) {
        showToast("Ocurrió un error al guardar.", "error");
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 h-fit sticky top-6">
            <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
                {{ isEditing ? "Editar Entrega" : "Nueva Entrega" }}
            </h3>
            <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

            <form @submit.prevent="onSubmit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <FormInput v-model="formData.fecha_inicio" label="Fecha de Inicio *" type="date"
                        :error-message="formErrors.fecha_inicio" />
                    <FormInput v-model="formData.fecha_fin" label="Fecha de Fin *" type="date"
                        :error-message="formErrors.fecha_fin" />
                </div>

                <FormInput v-model="formData.documento_admin" label="Documento del Administrador *"
                    :error-message="formErrors.documento_admin" placeholder="Ej: Resolución 123-2025" />

                <FormInput v-model="formData.id_admin" label="ID del Administrador *"
                    :error-message="formErrors.id_admin" placeholder="Ej: 1001" />

                <FormInput v-model="formData.observacion" label="Observaciones" :error-message="formErrors.observacion"
                    placeholder="Escribe alguna observación si es necesaria..." />

                <div class="grid grid-cols-2 gap-4">
                    <FormInput v-model="formData.fecha_aplazada" label="Fecha Aplazada" type="date"
                        :error-message="formErrors.fecha_aplazada" />
                    <FormInput v-model="formData.dias_aplazados" label="Días Aplazados" type="number"
                        :error-message="formErrors.dias_aplazados" />
                </div>

                <div class="flex items-center space-x-3 pt-2">
                    <CheckBox v-model="formData.estado" />
                    <div>
                        <label class="font-medium text-gray-800 dark:text-gray-200">Activo</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Marca esta casilla si la entrega está activa.
                        </p>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <Button :title="isEditing ? 'Guardar Cambios' : 'Crear Entrega'" type="submit"
                        :loading="saving || updating" class="w-full" />
                    <Button v-if="isEditing" title="Cancelar" variant="outline" @click="resetForm" />
                </div>
            </form>
        </div>
    </AuthorizationFallback>
</template>
