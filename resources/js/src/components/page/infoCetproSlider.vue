<script setup>
import { computed, ref, watch } from "vue";
import FormInput from "../ui/FormInput.vue";
import Button from "../ui/Button.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import Slider from '../ui/Slider.vue';
import useUserStore from "../../store/useUserStore";
import useRoleStore from "../../store/useRoleStore";
import useValidation from "../../composables/useValidation";
import useHttpRequest from "../../composables/useHttpRequest";
import useModalToast from "../../composables/useModalToast";

import * as yup from "yup";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    cetpro: {
        type: [Object, null],
        default: null,
    },
});

const emit = defineEmits(["hide"]);

const userStore = useUserStore();
const roleStore = useRoleStore();

const { store: createCetpro, saving, update: updateCetpro, updating } =
    useHttpRequest("/cetpro");

const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
    return props.cetpro?.id
        ? ["todo-acceso-cetpro", "editar-cetpro"]
        : ["todo-acceso-cetpro", "crear-cetpro", "ver-informacion-cetpro"];
});
const title = computed(() => (props.user ? `Actualizar Cetpro "${props.user?.name}"` : 'Añadir Cetpro'));

const initialFormData = () => ({
    cetpro: null,
    rd_autorizacion: null,
    rd_conversion: null,
    ugel: null,
    dre: null,
    tipo_gestion: null,
    region: null,
    provincia: null,
    distrito: null,
    lugar: null,
    direccion: null,
    numero: null,
});

const formData = ref(initialFormData());
const formErrors = ref({});

const isEditing = computed(() => !!props.cetpro?.id);

const onCancelEdit = () => {
    formData.value = initialFormData();
    formErrors.value = {};
    emit("hide");
};

watch(
    () => props.cetpro,
    (newData) => {
        if (props.show && newData?.id) {
            formData.value = Object.entries(initialFormData()).reduce(
                (acc, [key, val]) => ({
                    ...acc,
                    [key]: newData[key] ?? val,
                }),
                {}
            );
            formErrors.value = {};
        }
    },
    { immediate: true }
);

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

const onSubmit = async () => {
    if (saving.value || updating.value) return;

    const { validated, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    formErrors.value = {};

    const response = isEditing.value
        ? await updateCetpro(props.cetpro.id, formData.value)
        : await createCetpro(formData.value);

    if (response?.id) {
        showToast(`CETPRO ${isEditing.value ? "actualizado" : "creado"} correctamente`);
        roleStore.loadRoles();
        userStore.loadUsers();

        formData.value = initialFormData();
        emit("hide");
    }
};
</script>

<template>
    <Slider :show="show" :title="title" @hide="emit('hide')">
        <AuthorizationFallback :permissions="requiredPermissions">
            <div class="mt-2 space-y-2 font-inter">


                <hr class="border-t-2 border-cetpro mb-4" />

                <FormInput v-model="formData.cetpro" label="CETPRO" :error="formErrors.cetpro" required />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormInput v-model="formData.rd_autorizacion" label="R.D. de Autorización"
                        :error="formErrors.rd_autorizacion" required />
                    <FormInput v-model="formData.rd_conversion" label="R.D. de Conversión" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.ugel" label="UGEL" :error="formErrors.ugel" required />
                    <FormInput v-model="formData.dre" label="DRE" :error="formErrors.dre" required />
                    <FormInput v-model="formData.tipo_gestion" label="Tipo de Gestión" :error="formErrors.tipo_gestion"
                        required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.region" label="Región" required />
                    <FormInput v-model="formData.provincia" label="Provincia" required />
                    <FormInput v-model="formData.distrito" label="Distrito" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.lugar" label="Lugar" />
                    <FormInput v-model="formData.direccion" label="Dirección" />
                    <FormInput v-model="formData.numero" label="Número" />

                </div>



                <div class="flex gap-2 mt-4">
                    <Button :title="isEditing ? 'Guardar cambios' : 'Crear CETPRO'" :loading="saving || updating"
                        class="!w-full" @click="onSubmit" />

                    <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
                        class="bg-red-500 text-white" />
                </div>
            </div>
        </AuthorizationFallback>
    </Slider>
</template>