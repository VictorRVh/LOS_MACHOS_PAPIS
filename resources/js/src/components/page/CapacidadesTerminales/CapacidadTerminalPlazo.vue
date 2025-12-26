<script setup>
import { ref, computed, watch } from "vue";
import * as yup from "yup";

import Slider from "../../ui/Slider.vue";
import FormInput from "../../ui/FormInput.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../page/AuthorizationFallback.vue";

import useModalToast from "../../../composables/useModalToast";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useCapacidadTerminalStore from "../../../store/CapacidadTerminal/UseCapacidadTerminalStore";

const props = defineProps({
    capacidad: {
        type: Object,
        required: true,
    },
    show: {
        type: Boolean,
        required: true,
    },
    load: {
        type: String,
        required: true,
    },
    accion: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["hided"]);

const capacidadStore = useCapacidadTerminalStore();

const { showToast } = useModalToast();
const { runYupValidation } = useValidation();

const { updateFormData, updating } = useHttpRequest("/capacidad_terminal_aplazar");

const formErrors = ref({});
const formData = ref({
    dias_aplazados: null,
});

const modalTitle = computed(() => {
    return props.accion === "rectificar"
        ? "Rectificar entrega"
        : "Aplazar entrega";
});

// Cargar datos cuando cambie la capacidad
watch(
    () => props.capacidad,
    (c) => {
        if (c) {
            formData.value = {
                dias_aplazados: c.dias_aplazados || null,
            };
        }
    },
    { immediate: true }
);

// Mostrar solo si la capacidad ya terminó
const estadoFinalizado = computed(() => {
    const fin = props.capacidad?.fecha_fin;
    if (!fin) return false;

    const hoy = new Date();
    const fechaFin = new Date(fin);

    return hoy > fechaFin; // ya terminó
});

// Validación Yup
const schema = computed(() =>
    yup.object().shape({
        observacion: yup.string().nullable().max(255),
        dias_aplazados: yup.string().required("Seleccione días de plazo"),
    })
);

const diasOptions = [
    { label: "1 día", value: "1" },
    { label: "2 días", value: "2" },
    { label: "3 días", value: "3" },
    { label: "4 días", value: "4" },
    { label: "5 días", value: "5" },
];

// Enviar datos
const onSubmit = async () => {
    if (updating.value) return;

    const { validated, errors } = await runYupValidation(schema.value, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    formErrors.value = {};

    try {
        const response = await updateFormData(props.capacidad.id, formData.value);

        if (response) {
            showToast("Plazo aplicado correctamente.", "success");
            emit("hided");

            await capacidadStore.loadCapacidadTerminal(props.load)

        }
    } catch (error) {
        console.error(error);
        showToast("Error al aplicar la prórroga.", "error");
    }
};
</script>

<template>
    <Slider :show="show" :title="modalTitle" @hide="emit('hided')">
        <AuthorizationFallback :permissions="['ver-grupos']">

            <!-- <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                Aplazando la capacidad:
                <span class="font-bold">{{ capacidad.nombre_capacidad }}</span>
            </p> -->

            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                {{ accion === 'rectificar' ? 'Rectificando' : 'Aplazando' }} la unidad:
                <span class="font-bold">{{ capacidad.nombre_capacidad }}</span>
            </p>


            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    ¿Cuántos días desea ampliar?
                </label>

                <fieldset class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                    <div v-for="option in diasOptions" :key="option.value">
                        <label :for="`dias_${option.value}`"
                            class="flex items-center justify-center w-full p-3 text-sm font-medium bg-white dark:bg-gray-800 border-2 rounded-lg cursor-pointer transition-colors"
                            :class="{
                                'border-cetpro text-cetpro dark:border-cetpro-light dark:text-cetpro-light ring-2 ring-cetpro/50':
                                    formData.dias_aplazados === option.value,
                                'border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700':
                                    formData.dias_aplazados !== option.value,
                            }">
                            {{ option.label }}
                        </label>

                        <input type="radio" :id="`dias_${option.value}`" v-model="formData.dias_aplazados"
                            :value="option.value" class="sr-only" />
                    </div>
                </fieldset>

                <p v-if="formErrors.dias_aplazados" class="text-xs text-red-500 mt-2">
                    {{ formErrors.dias_aplazados }}
                </p>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <Button title="Guardar Cambios" @click="onSubmit" :loading="updating" :disabled="updating" />
            </div>

        </AuthorizationFallback>
    </Slider>
</template>
