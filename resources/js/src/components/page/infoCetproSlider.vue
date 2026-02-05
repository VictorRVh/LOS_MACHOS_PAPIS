<script setup>
import { computed, ref, watch } from "vue";
import axios from "axios";
import FormInput from "../ui/FormInput.vue";
import Button from "../ui/Button.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import Slider from "../ui/Slider.vue";
import useValidation from "../../composables/useValidation";
import useHttpRequest from "../../composables/useHttpRequest";
import useModalToast from "../../composables/useModalToast";
import useCetproStore from "../../store/useCetproStore";



import * as yup from "yup";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["hide"]);
const cetproStore = useCetproStore();
/**
 * Endpoint único (create / update)
 */
const { store: saveCetpro, saving } = useHttpRequest("/cetprodata");

const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

/**
 * Permisos (singleton)
 */
const requiredPermissions = computed(() => [
    "todo-acceso-cetpro",
    "editar-informacion-cetpro",
]);

const title = computed(() => "Datos del CETPRO");

/**
 * Estado inicial
 */
const initialFormData = () => ({
    cetpro: "",
    rd_autorizacion: "",
    rd_conversion: "",
    ugel: "",
    dre: "",
    tipo_gestion: "",
    region: "",
    provincia: "",
    distrito: "",
    lugar: "",
    direccion: "",
    numero: "",
});

const formData = ref(initialFormData());
const formErrors = ref({});

/**
 * Cargar datos existentes al abrir el slider
 */
watch(
    () => props.show,
    async (open) => {
        if (!open) return;

        try {
            const { data } = await axios.get("/cetprodata");
            if (data) {
                formData.value = {
                    ...initialFormData(),
                    ...data,
                };
            }
        } catch (e) {
            // Si no existe aún, simplemente se crea
            formData.value = initialFormData();
        }
    }
);

/**
 * Validación
 */
const schema = yup.object({
    cetpro: yup.string().required("El CETPRO es obligatorio"),
    rd_autorizacion: yup.string().required("La R.D. de autorización es obligatoria"),
    rd_conversion: yup.string().nullable(),
    ugel: yup.string().required("UGEL es obligatorio"),
    dre: yup.string().required("DRE es obligatorio"),
    tipo_gestion: yup.string().required("Tipo de gestión es obligatorio"),
    region: yup.string().required("Región es obligatoria"),
    provincia: yup.string().required("Provincia es obligatoria"),
    distrito: yup.string().required("Distrito es obligatorio"),
    lugar: yup.string().nullable(),
    direccion: yup.string().nullable(),
    numero: yup.string().nullable(),
});

/**
 * Guardar (crear o actualizar)
 */
const onSubmit = async () => {
    if (saving.value) return;

    const { validated, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    formErrors.value = {};

    const response = await saveCetpro(formData.value);

    if (response) {
        showToast("Datos del CETPRO guardados correctamente");
        emit("hide");
         await cetproStore.loadCetpro();
    }
};
</script>

<template>
    <Slider :show="show" :title="title" @hide="emit('hide')">
        <AuthorizationFallback :permissions="requiredPermissions">
            <div class="mt-2 space-y-2 font-inter">
                <hr class="border-t-2 border-cetpro mb-4" />

                <FormInput
                    v-model="formData.cetpro"
                    label="CETPRO"
                    :error="formErrors.cetpro"
                    required
                     :uppercase="true"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormInput
                        v-model="formData.rd_autorizacion"
                        label="R.D. de Autorización"
                        :error="formErrors.rd_autorizacion"
                        required
                         :uppercase="true"
                    />
                    <FormInput
                        v-model="formData.rd_conversion"
                        label="R.D. de Conversión"
                         :uppercase="true"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.ugel" label="UGEL" required  :uppercase="true" />
                    <FormInput v-model="formData.dre" label="DRE" required   :uppercase="true"/>
                    <FormInput
                        v-model="formData.tipo_gestion"
                        label="Tipo de Gestión"
                        required
                         :uppercase="true"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.region" label="Región" required  :uppercase="true"/>
                    <FormInput v-model="formData.provincia" label="Provincia" required  :uppercase="true" />
                    <FormInput v-model="formData.distrito" label="Distrito" required   :uppercase="true"/>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.lugar" label="Lugar"  :uppercase="true" />
                    <FormInput v-model="formData.direccion" label="Dirección"  :uppercase="true"/>
                    <FormInput v-model="formData.numero" label="Número" />
                </div>

                <div class="mt-4">
                    <Button
                        title="Guardar datos del CETPRO"
                        :loading="saving"
                        class="!w-full"
                        @click="onSubmit"
                    />
                </div>
            </div>
        </AuthorizationFallback>
    </Slider>
</template>
