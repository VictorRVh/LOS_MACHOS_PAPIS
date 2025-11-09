<script setup>
import { ref, computed, watch } from "vue";
import * as yup from "yup";
import { useHttpRequest } from "@/composables/useHttpRequest";
import { useModalToast } from "@/composables/useModalToast";
import { useValidation } from "@/composables/useValidation";
import {
    Button,
    FormInput,
    CheckBox,
} from "@/components/ui";

const props = defineProps({
    show: { type: Boolean, default: false },
    experiencia: { type: [Object, null], default: () => null },
    idGrupo: { type: String, required: true },
});

const emit = defineEmits(["hide", "saved"]);

const { store: createExperiencia, update: updateExperiencia, saving, updating } =
    useHttpRequest("/experiencia_formativa");
const { showToast } = useModalToast();
const { runYupValidation } = useValidation();

// 🧾 Inicialización
const initialFormData = () => ({
    nombre_experiencia: "",
    fecha_inicio: "",
    fecha_fin: "",
    horas: "",
    id_grupo: props.idGrupo,
    status: 1,
});

const formData = ref(initialFormData());
const formErrors = ref({});
const isEditing = computed(() => !!props.experiencia?.id);

// 📅 Validación con Yup
yup.setLocale({
    mixed: { required: "Este campo es obligatorio." },
    number: { typeError: "Debe ser un número válido." },
    date: { typeError: "Debe ingresar una fecha válida." },
});

const schema = yup.object().shape({
    nombre_experiencia: yup
        .string()
        .required("El nombre de la experiencia es obligatorio."),
    fecha_inicio: yup
        .date()
        .required("La fecha de inicio es requerida."),
    fecha_fin: yup
        .date()
        .required("La fecha de fin es requerida.")
        .min(yup.ref("fecha_inicio"), "La fecha de fin no puede ser anterior."),
    horas: yup
        .number()
        .required("Las horas son obligatorias.")
        .min(1, "Debe ser al menos 1 hora."),
    status: yup.number().oneOf([0, 1]),
});

// 🔄 Cargar datos al editar
watch(
    () => props.experiencia,
    (newExp) => {
        if (props.show && newExp?.id) {
            formData.value = { ...initialFormData(), ...newExp };
            formErrors.value = {};
        }
    },
    { immediate: true }
);

// ❌ Cancelar edición
const onCancelEdit = () => {
    formData.value = initialFormData();
    formErrors.value = {};
    emit("hide");
};

// ✅ Guardar experiencia
const onSubmit = async () => {
    if (saving.value || updating.value) return;

    const data = { ...formData.value };
    const { validated, errors } = await runYupValidation(schema, data);

    if (!validated) {
        formErrors.value = errors;
        return;
    }

    formErrors.value = {};

    try {
        const response = isEditing.value
            ? await updateExperiencia(props.experiencia.id, data)
            : await createExperiencia(data);

        showToast(
            `Experiencia formativa ${isEditing.value ? "editada" : "creada"} correctamente.`,
            "success"
        );

        emit("saved", response?.data || response);
        emit("hide");

        formData.value = initialFormData();
    } catch (error) {
        console.error(error);
        showToast("Error al guardar la experiencia", "error");
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="[
        'todo-acceso-experiencia-formativa',
        isEditing ? 'editar-experiencia-formativa' : 'crear-experiencia-formativa',
    ]">
        <h2 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
            {{ isEditing ? "Editar Experiencia Formativa" : "Registrar Experiencia Formativa" }}
        </h2>
        <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

        <div class="mt-2 space-y-3 font-inter">
            <FormInput v-model="formData.nombre_experiencia" label="Nombre de la experiencia *"
                :error="formErrors?.nombre_experiencia" />

            <div class="flex gap-2">
                <FormInput type="date" v-model="formData.fecha_inicio" label="Fecha de inicio *"
                    :error="formErrors?.fecha_inicio" />
                <FormInput type="date" v-model="formData.fecha_fin" label="Fecha de fin *"
                    :error="formErrors?.fecha_fin" />
            </div>

            <FormInput type="number" v-model="formData.horas" label="Horas *" :error="formErrors?.horas" min="1" />

            <CheckBox v-model="formData.status" label="Activo" class="flex items-center" />

            <div class="flex gap-2 mt-4">
                <Button :title="isEditing ? 'Guardar Cambios' : 'Crear Experiencia'" :loading="saving || updating"
                    :disabled="saving || updating" class="!w-full" @click="onSubmit" />
                <Button v-if="isEditing" title="Cancelar" variant="outline"
                    class="bg-red-500 hover:bg-red-600 text-white px-4" @click="onCancelEdit" />
            </div>
        </div>
    </AuthorizationFallback>
</template>
