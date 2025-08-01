<script setup>
import { computed, ref, watch } from "vue";
import useValidation from "../../../composables/useValidation";
import useModalToast from "../../../composables/useModalToast";
import * as yup from "yup";
import FormInput from "../../ui/FormInput.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../AuthorizationFallback.vue";
import BaseSelectCiclo from "../../ui/BaseSelectCiclo.vue";
import CheckBox from "../../ui/CheckBox.vue";
import useProgramaStore from "../../../store/Programa/useProgramaStore";
import { storeToRefs } from "pinia";

const props = defineProps({
    show: Boolean,
    programa: [Object, null],
    ciclo: Array,
});
const emit = defineEmits(["hide"]);

const programaStore = useProgramaStore();
const { showToast } = useModalToast();
const { runYupValidation } = useValidation();

const { programaLoading } = storeToRefs(programaStore);

const requiredPermissions = computed(() => {
    return props.programa?.id ? ["todo-acceso-roles", "editar-roles"] : ["todo-acceso-roles", "crear-roles"];
});

const title = computed(() =>
    props.programa ? `Editar programa "${props.programa?.nombre_programa}"` : "Agregar nuevo programa"
);

const initialFormData = () => ({
    id_ciclo: null,
    año: null,
    numero_rd: null,
    status: 0,
    descripcion: null,
});

const formData = ref(initialFormData());
const formErrors = ref({});
const isEditing = computed(() => !!props.programa?.id);

const onCancelEdit = () => {
    formData.value = initialFormData();
    formErrors.value = {};
    emit("hide");
};

watch(() => props.programa, (newVal) => {
    formErrors.value = {};
    if (props.show && newVal?.id) {
        formData.value = { ...initialFormData(), ...newVal };
    } else {
        formData.value = initialFormData();
    }
}, { immediate: true });

const schema = yup.object().shape({
    id_ciclo: yup.string().nullable().required(),
    año: yup.string().nullable().required(),
    numero_rd: yup.string().nullable().required(),
    status: yup.bool().nullable().required(),
    descripcion: yup.string().nullable().required(),
});

const onSubmit = async () => {
    if (programaLoading.value) return;

    let data = { ...formData.value };
    data.status = data.status ? 1 : 0;

    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }
    formErrors.value = {};

    try {
        if (isEditing.value) {
            await programaStore.updatePrograma(props.programa.id, data);
            showToast(`Programa editado exitosamente.`);
        } else {
            await programaStore.addPrograma(data);
            showToast(`Programa creado exitosamente.`);
        }
        
        formData.value = initialFormData();
        emit("hide");

    } catch (error) {
        showToast("Ocurrió un error al guardar.", "error");
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <div class="mt-2 space-y-1.5 font-inter">

            <FormLabelError label="Ciclo" required :error="formErrors?.id_ciclo">
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
                    <Button :title="isEditing ? 'Guardar Cambios' : 'Crear Programa'"
                        :loading-title="isEditing ? 'Guardando...' : 'Creando...'" :loading="programaLoading"
                        key="submit-btn" @click="onSubmit" class="!w-full" />

                    <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
                        class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer px-4" />
                </div>
            </div>
        </div>
    </AuthorizationFallback>
</template>y