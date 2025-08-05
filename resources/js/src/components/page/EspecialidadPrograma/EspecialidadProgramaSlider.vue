<script setup>
import { computed, nextTick, ref, watch } from "vue";

import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";

import * as yup from "yup";
import Slider from "../../ui/Slider.vue";
import FormInput from "../../ui/FormInput.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../AuthorizationFallback.vue";
import BaseSelect from "../../ui/BaseSelect.vue";
import useProgramaStore from "../../../store/Programa/useProgramaStore";
import CheckBox from "../../ui/CheckBox.vue";
import BaseSelectCiclo from "../../ui/BaseSelectCiclo.vue";
import useEspecialidadProgramaStore from "../../../store/EspecialidadPrograma/useEspecialidadProgramaStore";

const props = defineProps({
    show: {
        type: Boolean,
        default: () => false,
    },
    especialidadPrograma: {
        type: [Object, null],
        default: () => null,
    },
    especialidad: {
        type: Array,
        default: () => []
    },
    idPrograma: {
        type: Array,
        default: () => []
    }
});
const emit = defineEmits(["hide"]);

const especialidadProgramaStore = useEspecialidadProgramaStore();


const { store: createPrograma, saving, update: updatePrograma, updating } = useHttpRequest(
    "/especialidad_programa"
);
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
    if (!props.role?.id) return ["todo-acceso-roles", "crear-roles"];
    else return ["todo-acceso-roles", "editar-roles"];
});

const initialFormData = () => {
    return {
        id_especialidad: null,
        id_programa: props.idPrograma,
        nro_modulos: null,
    };
};

console.log('prop independiente: ', props.idPrograma)

const formData = ref(initialFormData());
const formErrors = ref({});


const isEditing = computed(() => !!props.especialidadPrograma?.id);

const onCancelEdit = () => {
    formData.value = initialFormData();
    formErrors.value = {};
    emit("hide"); // oculta el formulario
};

watch(
    () => props.especialidadPrograma,
    (newRole) => {
        if (props.show && newRole?.id) {

            const baseData = initialFormData();

            formData.value = {
                ...baseData,
                ...Object.keys(baseData).reduce((acc, key) => {
                    if (newRole[key] !== undefined) acc[key] = newRole[key];
                    return acc;
                }, {})
            };
        }
    },
    { immediate: true }
);

const schema = yup.object().shape({
    id_especialidad: yup.string().nullable().required(),
    id_programa: yup.string().nullable().required(),
    nro_modulos: yup.string().nullable().required(),
});



const onSubmit = async () => {

    console.log('entrado aqqui')

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

    const response = props.especialidadPrograma?.id
        ? await updatePrograma(props.especialidadPrograma?.id, data)
        : await createPrograma(data);

    if (response?.id) {
        showToast(`programa ${props.idPrograma ? "editado" : "creado"} exitosamente.`);
        especialidadProgramaStore.loadEspecialidadProgramaById(props.idPrograma)

        formData.value = initialFormData();
        formErrors.value = {};
        emit("hide");
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <div class="mt-2 space-y-1.5 font-inter">

            <FormLabelError label="Especialidad" required>
                <BaseSelectCiclo v-model="formData.id_especialidad" :options="especialidad" label="nombre_especialidad"
                    placeholder="Seleccione una especialidad" />
            </FormLabelError>

            <FormInput v-model="formData.nro_modulos" :focus="show" label="Numero de modulos"
                :error="formErrors?.nro_modulos" required />

            <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormInput v-model="formData.año" :focus="show" label="Año" :error="formErrors?.año" required />
                <CheckBox v-model="formData.status" label="Estado"
                    class="mt-8 pl-4 flex justify-center items-centers" />

            </div> -->

            <div class="w-full space-y-3">

                <div class="flex gap-2 mt-1">
                    <!-- Botón Guardar: ancho completo -->
                    <Button :title="especialidadPrograma?.id ? 'Guardar Cambios' : 'Asignar especialidad'"
                        :loading-title="role?.id ? 'Guardando...' : 'Creando...'" :loading="saving || updating"
                        key="submit-btn" @click="onSubmit" class="!w-full" />

                    <!-- Botón Cancelar: ancho flexible solo si se está editando -->
                    <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
                        class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer px-4" />
                </div>
            </div>
        </div>
    </AuthorizationFallback>
</template>
