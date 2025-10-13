<script setup>
import { computed, nextTick, ref, watch } from "vue";

import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";

import * as yup from "yup";
import FormInput from "../../ui/FormInput.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../AuthorizationFallback.vue";
import BaseSelectModulo from "../../ui/BaseSelectCiclo.vue";

import useModuloStore from "../../../store/Modulos/useModulosStore";

const props = defineProps({
    show: {
        type: Boolean,
        default: () => false,
    },
    modulo: {
        type: [Object, null],
        default: () => null,
    },
    indexModulo: {
        type: [Object, null],
        default: () => null,
    },
    especialidad: {
        type: Array,
        default: () => []
    },
});
const emit = defineEmits(["hide"]);

const moduloStore = useModuloStore();

const { store: createModulo, saving, update: updateModulo, updating } = useHttpRequest(
    "/modulo"
);
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
    if (!props.role?.id) return ["todo-acceso-modulos", "crear-modulos"];
    else return ["todo-acceso-modulos", "editar-modulos"];
});

const initialFormData = () => {
    return {
        numero_modulo: null,
        descripcion: null,
        creditos: null,
        horas: null,
        nro_capacidades: null,
        id_especialidad: props.especialidad,

        nro_capacidades: null,
    };
};

const formData = ref(initialFormData());
const formErrors = ref({});

const isEditing = computed(() => !!props.modulo?.id);

const onCancelEdit = () => {
    formData.value = initialFormData();
    formErrors.value = {};
    emit("hide");
};

watch(
    () => props.modulo,
    (newRole) => {
        if (props.show && newRole?.id) {

            const baseData = initialFormData();

            formData.value = {
                ...baseData,
                ...Object.keys(baseData).reduce((acc, key) => {
                    if (newRole[key] !== undefined) acc[key] = newRole[key];
                    return acc;
                }, {})
            }; formErrors.value = {};
        }
    },
    { immediate: true }
);

const schema = yup.object().shape({
    numero_modulo: yup
        .number()
        .typeError("El número de módulo debe ser un valor numérico")
        .required("El número de módulo es obligatorio")
        .positive("El número de módulo debe ser positivo")
        .integer("El número de módulo debe ser un número entero"),
    descripcion: yup.string().required("El nombre de módulo es obligatoria"),
    creditos: yup.number().required("Los créditos son obligatorios"),
    horas: yup.number().required("Las horas son obligatorias"),
    id_especialidad: yup.string().required("La especialidad es obligatoria"),
    nro_capacidades: yup.number().required("El número de capacidades es obligatorio"),
});

const onSubmit = async () => {

    if (saving.value || updating.value) return;

    let data = {
        ...formData.value,
    };

    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }
    formErrors.value = {};

    console.log('modulo.id:', props.modulo?.id);


    const response = props.modulo?.id
        ? await updateModulo(props.modulo?.id, data)
        : await createModulo(data);

    if (response?.id) {
        showToast(`Modulo ${props.modulo?.id ? "editado" : "creado"} exitosamente.`);
        moduloStore.loadModuloById(props.especialidad)
        formData.value = initialFormData();
        formErrors.value = {};

        emit("hide");
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <div class="mt-2 space-y-1.5 font-inter">

            <FormInput v-model="formData.descripcion" :focus="show" label="Nombre del módulo"
                :error="formErrors?.descripcion" required />


            <FormLabelError label="Número de módulo" required :error="formErrors?.numero_modulo">
                <BaseSelectModulo v-model="formData.numero_modulo" :options="props.indexModulo" label="name"
                    placeholder="Seleccione un módulo" />
            </FormLabelError>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <FormInput v-model="formData.creditos" :focus="show" label="Créditos" :error="formErrors?.creditos"
                    required type="number" />
                <FormInput v-model="formData.horas" :focus="show" label="Horas" :error="formErrors?.horas" required
                    type="number" />
            </div>


            <FormInput v-model="formData.nro_capacidades" :focus="show" label="Número de capacidades"
                :error="formErrors?.nro_capacidades" required type="number" />



            <div class="w-full space-y-3">

                <div class="flex gap-2 mt-1">
                    <!-- Botón Guardar: ancho completo -->
                    <Button :title="modulo?.id ? 'Guardar Cambios' : 'Crear Módulo'"
                        :loading-title="role?.id ? 'Guardando...' : 'Creando...'"
                        :disabled="saving || updating"
                         :loading="saving || updating"
                        key="submit-btn" @click="onSubmit" class="!w-full" />

                    <!-- Botón Cancelar: ancho flexible solo si se está editando -->
                    <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
                        class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer px-4" />
                </div>
            </div>
        </div>
    </AuthorizationFallback>
</template>
