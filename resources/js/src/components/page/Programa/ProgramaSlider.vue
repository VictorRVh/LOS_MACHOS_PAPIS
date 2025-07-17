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

const props = defineProps({
    show: {
        type: Boolean,
        default: () => false,
    },
    programa: {
        type: [Object, null],
        default: () => null,
    },
    ciclo: {
        type: Array,
        default: () => []
    }
});
const emit = defineEmits(["hide"]);

const programaStore = useProgramaStore();

const { store: createPrograma, saving, update: updatePrograma, updating } = useHttpRequest(
    "/programa_estudio"
);
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
    if (!props.role?.id) return ["todo-acceso-roles", "crear-roles"];
    else return ["todo-acceso-roles", "editar-roles"];
});

const title = computed(() =>
    props.programa ? `Editar programa "${props.programa?.nombre_programa}"` : "Agregar nuevo rol"
);

const initialFormData = () => {
    return {
        id_ciclo: null,
        año: null,
        numero_rd: null,
        status: 0,
        descripcion: null
    };
};

const formData = ref(initialFormData());
const formErrors = ref({});

const isEditing = computed(() => !!props.programa?.id);

const onCancelEdit = () => {
    formData.value = initialFormData();
    formErrors.value = {};
    emit("hide"); // oculta el formulario
};

watch(
    () => props.programa,
    (newRole) => {
        if (props.show && newRole?.id) {
            console.log(formData.value);
            formData.value = Object.entries(initialFormData()).reduce((r, [key, val]) => {
                if (newRole[key]) return { ...r, [key]: newRole[key] };
                return { ...r, [key]: val };
            }, {});
        }
    },
    { immediate: true }
);

const schema = yup.object().shape({
    id_ciclo: yup.string().nullable().required(),
    año: yup.string().nullable().required(),
    numero_rd: yup.string().nullable().required(),
    status: yup.bool().nullable().required(),
    descripcion: yup.string().nullable().required(),
});

const onSubmit = async () => {

    console.log('entrado aqqui')

    if (saving.value || updating.value) return;

    let data = {
        ...formData.value,
    };

    console.log('dedede', data)

    data.status = parseInt(data.status);

    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }
    formErrors.value = {};

    const response = props.programa?.id
        ? await updatePrograma(props.programa?.id, data)
        : await createPrograma(data);

    if (response?.id) {
        showToast(`programa ${props.programa?.id ? "editado" : "creado"} exitosamente.`);
        programaStore.loadPrograma();

        console.log('props', props.programa)

        if (!props.programa?.id) {
            formData.value = initialFormData();
            formErrors.value = {};
        }
        emit("hide");
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <div class="mt-2 space-y-1.5 font-inter">

            <FormLabelError label="Ciclo" required>
                <BaseSelectCiclo v-model="formData.id_ciclo" :options="ciclo" label="nombre_ciclo"
                    placeholder="Seleccione un ciclo" />
            </FormLabelError>

            <FormInput v-model="formData.numero_rd" :focus="show" label="Numero R.D." :error="formErrors?.numero_rd"
                required />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormInput v-model="formData.año" :focus="show" label="Año" :error="formErrors?.año" required />
                <CheckBox v-model="formData.status" label="Estado"
                    class="mt-8 pl-4 flex justify-center items-centers" />

            </div>


            <FormInput v-model="formData.descripcion" :focus="show" label="Descripcion" :error="formErrors?.descripcion"
                required />


            <div class="w-full space-y-3">

                <div class="flex gap-2 mt-1">
                    <!-- Botón Guardar: ancho completo -->
                    <Button :title="programa?.id ? 'Guardar Cambios' : 'Crear Programa'"
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
