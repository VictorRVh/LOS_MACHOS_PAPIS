<script setup>
import { computed, ref, watch } from "vue";
import FormInput from "../../ui/FormInput.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../../components/page/AuthorizationFallback.vue";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";
import * as yup from "yup";
import useCapacidadTerminalStore from "../../../store/CapacidadTerminal/UseCapacidadTerminalStore";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    capacidad: {
        type: [Object, null],
        default: () => null,
    },
    idGrupo: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["hide"]);

const capacidadStore = useCapacidadTerminalStore();
const { store: createCapacidad, saving, update: updateCapacidad, updating } =
    useHttpRequest("/capacidad_terminal");
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
    if (!props.capacidad?.id)
        return ["todo-acceso-capacidad-terminal", "crear-capacidad-terminal"];
    return ["todo-acceso-capacidad-terminal", "editar-capacidad-terminal"];
});

const initialFormData = () => ({
    nombre_capacidad: "",
    fecha_inicio: "",
    fecha_fin: "",
    id_grupo: props.idGrupo,
    status: 1,
});

const formData = ref(initialFormData());
const formErrors = ref({});
const isEditing = computed(() => !!props.capacidad?.id);

const onCancelEdit = () => {
    formData.value = initialFormData();
    formErrors.value = {};
    emit("hide");
};

// cuando se recibe una capacidad para editar
watch(
    () => props.capacidad,
    (newVal) => {
        if (props.show && newVal?.id) {
            formData.value = { ...initialFormData(), ...newVal };
            formErrors.value = {};
        }
    },
    { immediate: true }
);

// Validación con Yup
const schema = yup.object().shape({
    nombre_capacidad: yup
        .string()
        .nullable()
        .required("El nombre de la capacidad es obligatorio."),
    fecha_inicio: yup
        .date()
        .nullable()
        .required("La fecha de inicio es obligatoria."),
    fecha_fin: yup
        .date()
        .nullable()
        .required("La fecha de fin es obligatoria."),
    status: yup
        .number()
        .oneOf([0, 1])
        .required("El estado es obligatorio."),
});

const onSubmit = async () => {
    if (saving.value || updating.value) return;

    const data = { ...formData.value };

    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    formErrors.value = {};

    const response = props.capacidad?.id
        ? await updateCapacidad(props.capacidad.id, data)
        : await createCapacidad(data);

    if (response?.id) {
        showToast(
            `Capacidad terminal ${isEditing.value ? "editada" : "creada"} exitosamente.`
        );
        await capacidadStore.loadCapacidadTerminal(props.idGrupo);
        formData.value = initialFormData();
        emit("hide");
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <div class="mt-2 space-y-2 font-inter">
            <FormInput v-model="formData.nombre_capacidad" :focus="show" label="Nombre de la capacidad terminal"
                :error="formErrors?.nombre_capacidad" required />

            <div class="flex gap-2">
                <FormInput type="date" v-model="formData.fecha_inicio" label="Fecha de inicio"
                    :error="formErrors?.fecha_inicio" required />
                <FormInput type="date" v-model="formData.fecha_fin" label="Fecha de fin" :error="formErrors?.fecha_fin"
                    required />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">
                    Estado
                </label>
                <select v-model="formData.status" class="w-full border rounded-md p-2 dark:bg-gray-700 dark:text-white">
                    <option :value="1">Activo</option>
                    <option :value="0">Inactivo</option>
                </select>
                <p v-if="formErrors?.status" class="text-red-500 text-sm mt-1">
                    {{ formErrors.status }}
                </p>
            </div>

            <div class="flex gap-2 mt-3">
                <Button :title="isEditing ? 'Guardar Cambios' : 'Crear Capacidad'"
                    :loading-title="isEditing ? 'Guardando...' : 'Creando...'" :loading="saving || updating"
                    @click="onSubmit" class="!w-full" />
                <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
                    class="bg-red-500 hover:bg-red-600 text-white px-4" />
            </div>
        </div>
    </AuthorizationFallback>
</template>
