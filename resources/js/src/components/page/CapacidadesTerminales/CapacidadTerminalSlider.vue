<script setup>
import { computed, ref, watch } from "vue";
import FormInput from "../../ui/FormInput.vue";
import DatePickerInput from "../../ui/DatePickerInput.vue";
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
    notice: {
        type: String,
        default: () => "",
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
    if (!props.capacidad?.id) {
        return ["todo-acceso-unidad-didáctica-docente", "crear-unidad-didáctica-docente"];
    }
    return ["todo-acceso-unidad-didáctica-docente", "editar-unidad-didáctica-docente"];
});

const capacidades = ref([]);
const noticeDismissed = ref(false);

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
const visibleNotice = computed(() => !noticeDismissed.value && props.notice);

const onCancelEdit = () => {
    formData.value = initialFormData();
    formErrors.value = {};
    emit("hide");
};

watch(
    () => props.notice,
    () => {
        noticeDismissed.value = false;
    },
    { immediate: true }
);

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
                    name: `Unidad Didáctica ${num}`,
                };
            });
        }
    },
    { immediate: true }
);

const capacidadesCreadas = ref([]);

const filteredCapacidades = computed(() => {
    return capacidades.value.filter(
        cap => !capacidadesCreadas.value.includes(cap.id)
    );
});

yup.setLocale({
    mixed: {
        required: "Este campo es obligatorio.",
    },
    string: {
        email: "Debe ser un correo válido.",
    },
    date: {
        min: "La fecha no puede ser anterior a ${min}",
        max: "La fecha no puede ser posterior a ${max}",
        typeError: "Debe ser una fecha válida",
    },
});

const schema = yup.object().shape({
    numero_capacidad: yup
        .string()
        .nullable()
        .required("El numero de la unidad es obligatorio."),
    nombre_capacidad: yup
        .string()
        .nullable()
        .required("El nombre de la unidad es obligatorio."),
    creditos_teoricos: yup
        .number()
        .typeError("Los créditos teóricos deben ser un número.")
        .positive("Los créditos teóricos deben ser mayores a 0.")
        .integer("Los créditos teóricos deben ser un número entero.")
        .required("Los créditos teóricos son obligatorias."),
    creditos_practicos: yup
        .number()
        .typeError("Los créditos prácticos deben ser un número.")
        .positive("Los créditos prácticos deben ser mayores a 0.")
        .integer("Los créditos prácticos deben ser un número entero.")
        .required("Los créditos prácticos son obligatorias."),
    horas: yup
        .number()
        .typeError("Las horas deben ser un número.")
        .positive("Las horas deben ser mayores a 0.")
        .integer("Las horas deben ser un número entero.")
        .required("Las horas son obligatorias."),
    fecha_inicio: yup.date().required("La fecha de inicio es requerida.").typeError("Debe ingresar una fecha válida"),
    fecha_fin: yup.date()
        .required("La fecha de fin es requerida.")
        .min(yup.ref("fecha_inicio"), "La fecha de fin no puede ser anterior a la de inicio.")
        .typeError("Debe ingresar una fecha válida"),
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

    const now = new Date();
    const horaActual = now.toTimeString().slice(0, 8);
    const horaFin = "23:59:59";

    const formatDateTime = (fecha, hora) => {
        const [year, month, day] = fecha.split("-");
        return `${year}-${month}-${day} ${hora}`;
    };

    data.fecha_inicio = formatDateTime(data.fecha_inicio, horaActual);
    data.fecha_fin = formatDateTime(data.fecha_fin, horaFin);

    const response = props.capacidad?.id
        ? await updateCapacidad(props.capacidad.id, data)
        : await createCapacidad(data);

    if (response?.id) {
        capacidadesCreadas.value.push(data.numero_capacidad);
        formData.value = initialFormData();
        formErrors.value = {};
        showToast(
            `Unidad Didáctica ${isEditing.value ? "editada" : "creada"} exitosamente.`
        );
        await capacidadStore.loadCapacidadTerminal(props.idGrupo);
        await calificacionCapacidad.loadCapacidadTerminal(props.idGrupo);
        emit("hide");
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <div class="mb-2 flex flex-col gap-2">
            <div class="flex items-start justify-between gap-3">
                <h2 class="text-lg font-semibold text-cetpro dark:text-cetpro-light">
                    {{ capacidad?.id ? "Editar Unidad Didáctica" : "Agregar Unidad Didáctica" }}
                </h2>

                <div
                    v-if="visibleNotice"
                    class="flex max-w-[350px] items-start gap-2 border border-red-700 bg-red-700 px-3 py-1 text-[11px] leading-4 text-white"
                >
                    <span class="min-w-0 flex-1">{{ visibleNotice }}</span>
                    <button
                        type="button"
                        class="shrink-0 text-red-100 transition hover:text-white"
                        @click="noticeDismissed = true"
                    >
                        x
                    </button>
                </div>
            </div>

            <hr class="border-t-2 border-cetpro dark:border-cetpro-light" />
        </div>

        <div class="mt-2 space-y-3 font-inter">
            <FormInput
                v-model="formData.nombre_capacidad"
                :focus="show"
                label="Nombre de la unidad didáctica"
                :error="formErrors?.nombre_capacidad"
                required
            />

            <div class="flex gap-2">
                <div class="w-3/4">
                    <FormLabelError label="Número de unidad didáctica" required :error="formErrors?.numero_capacidad">
                        <BaseSelectModulo
                            v-model="formData.numero_capacidad"
                            :options="props.indexCapacidades"
                            label="name"
                            placeholder="seleccione"
                        />
                    </FormLabelError>
                </div>

                <div class="w-1/4">
                    <FormInput
                        v-model="formData.horas"
                        :focus="show"
                        label="Horas"
                        :error="formErrors?.horas"
                        required
                    />
                </div>
            </div>

            <div class="flex gap-2">
                <FormInput
                    v-model="formData.creditos_teoricos"
                    :focus="show"
                    label="Crédito teórico"
                    :error="formErrors?.creditos_teoricos"
                    required
                />

                <FormInput
                    v-model="formData.creditos_practicos"
                    :focus="show"
                    label="Crédito práctico"
                    :error="formErrors?.creditos_practicos"
                    required
                />
            </div>

            <div class="flex gap-2">
                <DatePickerInput
                    v-model="formData.fecha_inicio"
                    label="Fecha de inicio"
                    :error="formErrors?.fecha_inicio"
                    required
                />
                <DatePickerInput
                    v-model="formData.fecha_fin"
                    label="Fecha de fin"
                    :error="formErrors?.fecha_fin"
                    required
                />
            </div>

            <div class="mt-3 flex gap-2">
                <Button
                    :title="isEditing ? 'Guardar Cambios' : 'Crear Unidad'"
                    :loading-title="isEditing ? 'Guardando...' : 'Creando...'"
                    :disabled="saving || updating"
                    :loading="saving || updating"
                    @click="onSubmit"
                    class="!w-full"
                />
                <Button
                    v-if="isEditing"
                    title="Cancelar"
                    variant="outline"
                    @click="onCancelEdit"
                    class="bg-red-500 px-4 text-white hover:bg-red-600"
                />
            </div>
        </div>
    </AuthorizationFallback>
</template>
