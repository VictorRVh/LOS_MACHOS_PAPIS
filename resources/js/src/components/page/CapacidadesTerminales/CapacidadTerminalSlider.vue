<script setup>
import { computed, ref, watch } from "vue";
import FormInput from "../../ui/FormInput.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../../components/page/AuthorizationFallback.vue";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import CheckBox from "../../ui/CheckBox.vue";
import useModalToast from "../../../composables/useModalToast";
import * as yup from "yup";
import useCapacidadTerminalStore from "../../../store/CapacidadTerminal/UseCapacidadTerminalStore";
import useCapacidadTerminalCalificacionStore from "../../../store/Estudiante/UseEstudianteCapacidadGrupoStore";
import BaseSelectModulo from "../../ui/BaseSelectCiclo.vue";
import FormLabelError from "../../ui/FormLabelError.vue";

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
    indexCapacidades: {
        type: [Object, null],
        default: () => null,
    },
});

const emit = defineEmits(["hide"]);

const capacidadStore = useCapacidadTerminalStore();
const calificacionCapacidad = useCapacidadTerminalCalificacionStore();

const { store: createCapacidad, saving, update: updateCapacidad, updating } =
    useHttpRequest("/capacidad_terminal");
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
    if (!props.capacidad?.id)
        return ["todo-acceso-capacidad-terminal-docente", "crear-capacidad-terminal-docente"];
    return ["todo-acceso-capacidad-terminal-docente", "editar-capacidad-terminal-docente"];
});

const capacidades = ref([]);

const initialFormData = () => ({
    numero_capacidad: "",
    nombre_capacidad: "",
    fecha_inicio: "",
    fecha_fin: "",
    id_grupo: props.idGrupo,
    status: 0,
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
    [() => props.capacidad, () => props.capacidades],
    ([newCapacidad, newCapacidades]) => {

        if (props.show && newCapacidad?.id) {
            formData.value = { ...initialFormData(), ...newCapacidad };
            formErrors.value = {};
        }

        const total = parseInt(newCapacidades, 10);
        if (!isNaN(total) && total > 0) {
            capacidades.value = Array.from({ length: total }, (_, i) => {
                const num = String(i + 1).padStart(2, "0");
                return {
                    id: num,
                    name: `Capacidad terminal ${num}`,
                };
            });
        }
    },
    { immediate: true }
);

// Lista de módulos ya creados (simulada)
const capacidadesCreadas = ref([])

// Opciones filtradas: excluye las ya creadas
const filteredCapacidades = computed(() => {
    return capacidades.value.filter(
        cap => !capacidadesCreadas.value.includes(cap.id)
    )
})

yup.setLocale({
    mixed: {
        required: 'Este campo es obligatorio.',
    },
    string: {
        email: 'Debe ser un correo válido.',
    },
    date: {
        min: 'La fecha no puede ser anterior a ${min}',
        max: 'La fecha no puede ser posterior a ${max}',
        typeError: 'Debe ser una fecha válida',
    },
});

// Validación con Yup
const schema = yup.object().shape({
    numero_capacidad: yup
        .string()
        .nullable()
        .required("El numero de la capacidad es obligatorio."),
    nombre_capacidad: yup
        .string()
        .nullable()
        .required("El nombre de la capacidad es obligatorio."),
    fecha_inicio: yup.date().required("La fecha de inicio es requerida.").typeError("Debe ingresar una fecha válida"),
    fecha_fin: yup.date()
        .required("La fecha de fin es requerida.")
        .min(yup.ref("fecha_inicio"), "La fecha de fin no puede ser anterior a la de inicio.")
        .typeError("Debe ingresar una fecha válida"),
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

        capacidadesCreadas.value.push(data.numero_capacidad);

        formData.value = initialFormData();
        formErrors.value = {};
        showToast(
            `Capacidad terminal ${isEditing.value ? "editada" : "creada"} exitosamente.`
        );
        await capacidadStore.loadCapacidadTerminal(props.idGrupo);
        await calificacionCapacidad.loadCapacidadTerminal(props.idGrupo);
        emit("hide");
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <h2 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
            {{ capacidad?.id ? "Editar Capacidad Terminal" : "Agregar Capacidad Terminal" }}
        </h2>
        <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

        <div class="mt-2 space-y-3 font-inter">
            <FormLabelError label="Número de capacidad terminal" required :error="formErrors?.numero_capacidad">
                <BaseSelectModulo v-model="formData.numero_capacidad" :options="props.indexCapacidades" label="name"
                    placeholder="Número de capacidad" />
            </FormLabelError>


            <FormInput v-model="formData.nombre_capacidad" :focus="show" label="Nombre de la capacidad terminal"
                :error="formErrors?.nombre_capacidad" required />

            <div class="flex gap-2">
                <FormInput type="date" v-model="formData.fecha_inicio" label="Fecha de inicio"
                    :error="formErrors?.fecha_inicio" required />
                <FormInput type="date" v-model="formData.fecha_fin" label="Fecha de fin" :error="formErrors?.fecha_fin"
                    required />
            </div>

            <div>
                <CheckBox v-model="formData.status" label="Estado" class="flex items-center" />
            </div>

            <div class="flex gap-2 mt-3">
                <Button :title="isEditing ? 'Guardar Cambios' : 'Crear Capacidad'"
                    :loading-title="isEditing ? 'Guardando...' : 'Creando...'" :disabled="saving || updating"
                    :loading="saving || updating" @click="onSubmit" class="!w-full" />
                <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
                    class="bg-red-500 hover:bg-red-600 text-white px-4" />
            </div>
        </div>
    </AuthorizationFallback>
</template>
