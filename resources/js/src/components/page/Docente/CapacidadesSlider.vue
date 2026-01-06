<script setup>
import { computed, ref, watch } from "vue";
import FormInput from "../../ui/FormInput.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../../components/page/AuthorizationFallback.vue";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";
import * as yup from "yup";

/* =========================
   PROPS & EMITS
========================= */
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    capacidad: {
        type: [Object, null],
        default: () => null,
    },
    idCompetencia: {
        type: [Number, String],
        required: true,
    },
});

const emit = defineEmits(["hide"]);

/* =========================
   COMPOSABLES
========================= */
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const {
    store: createCompetencia,
    saving,
    update: updateCompetencia,
    updating,
} = useHttpRequest("/capacidades-terminales-competencia");

/* =========================
   PERMISOS
========================= */
const requiredPermissions = computed(() => {
    if (!props.capacidad?.id) {
        return [
            "todo-acceso-capacidad-terminal-docente",
            "crear-capacidad-terminal-docente",
        ];
    }
    return [
        "todo-acceso-capacidad-terminal-docente",
        "editar-capacidad-terminal-docente",
    ];
});

/* =========================
   FORM DATA
========================= */
const initialFormData = () => ({
    id_competencia: props.idCompetencia,
    sigla: "",
    descripcion: "",
});

const formData = ref(initialFormData());
const formErrors = ref({});

const isEditing = computed(() => !!props.capacidad?.id);

/* =========================
   WATCH EDIT MODE
========================= */
watch(
    () => props.capacidad,
    (newCapacidad) => {
        if (props.show && newCapacidad?.id) {
            formData.value = { ...initialFormData(), ...newCapacidad };
            formErrors.value = {};
        }
    },
    { immediate: true }
);

/* =========================
   VALIDACIÓN YUP
========================= */
yup.setLocale({
    mixed: {
        required: "Este campo es obligatorio.",
    },
});

const schema = yup.object().shape({
    sigla: yup
        .string()
        .required("La sigla es obligatoria.")
        .max(10, "Máximo 10 caracteres."),
    descripcion: yup
        .string()
        .required("La descripción es obligatoria."),
});

/* =========================
   ACTIONS
========================= */
const onCancel = () => {
    formData.value = initialFormData();
    formErrors.value = {};
    emit("hide");
};

const onSubmit = async () => {
    if (saving.value || updating.value) return;

    const data = { ...formData.value };

    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    formErrors.value = {};

    const response = isEditing.value
        ? await updateCompetencia(props.capacidad.id, data)
        : await createCompetencia(data);

    if (response?.id) {
        showToast(
            `Competencia ${isEditing.value ? "editada" : "creada"} exitosamente.`
        );

        formData.value = initialFormData();
        emit("hide");
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <h2 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
            {{ isEditing ? "Editar Competencia" : "Agregar Competencia" }}
        </h2>

        <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

        <div class="space-y-3 font-inter">
            <FormInput
                v-model="formData.sigla"
                label="Sigla"
                required
                :error="formErrors?.sigla"
            />

            <FormInput
                v-model="formData.descripcion"
                label="Descripción"
                required
                :error="formErrors?.descripcion"
            />

            <div class="flex gap-2 mt-4">
                <Button
                    :title="isEditing ? 'Guardar cambios' : 'Crear competencia'"
                    :loading-title="isEditing ? 'Guardando...' : 'Creando...'"
                    :loading="saving || updating"
                    :disabled="saving || updating"
                    class="!w-full"
                    @click="onSubmit"
                />

                <Button
                    v-if="isEditing"
                    title="Cancelar"
                    variant="outline"
                    class="bg-red-500 hover:bg-red-600 text-white"
                    @click="onCancel"
                />
            </div>
        </div>
    </AuthorizationFallback>
</template>
