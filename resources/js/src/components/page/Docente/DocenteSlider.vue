<script setup>
import { computed, ref, watch } from 'vue';
import Slider from '../../ui/Slider.vue';
import FormInput from '../../ui/FormInput.vue';
import FormLabelError from '../../ui/FormLabelError.vue';
import VSelect from 'vue-select';
import Button from '../../ui/Button.vue';
import AuthorizationFallback from '../AuthorizationFallback.vue';



import useDocenteStore from '../../../store/Docente/useDocenteStore';

import useValidation from '../../../composables/useValidation';
import useHttpRequest from '../../../composables/useHttpRequest';
import useUtils from '../../../composables/useUtils';
import useModalToast from '../../../composables/useModalToast';
import * as yup from 'yup';
import SelectedChips from '../../ui/selectedChips.vue';
import CheckBox from '../../ui/CheckBox.vue';
import BaseSelect from '../../ui/BaseSelect.vue';

const props = defineProps({
    show: { type: Boolean, default: () => false },
    docente: { type: [Object, null], default: () => null },
});
const emit = defineEmits(['hide']);




const docenteStore = useDocenteStore();

const { store: createDocente, saving, update: updateDocente, updating } = useHttpRequest('/docente');
const { runYupValidation } = useValidation();
const { omitPropsFromObject } = useUtils();
const { showToast } = useModalToast()


const requiredPermissions = computed(() => {
    if (!props.docente?.id) return ['todo-acceso-docentes', 'crear-docentes'];
    else return ['todo-acceso-docentes', 'editar-docentes'];
});

const title = computed(() => (props.docente ? `Actualizar Docente "${props.docente?.name}"` : 'Añadir Nuevo  Docente'));

const initialFormData = () => ({
    name: null,
    apellido_paterno: null,
    apellido_materno: null,
    usuario: null,
    dni: null,
    email: null,
    fecha_nacimiento: null,
    telefono: null,
    direccion: null,
    status: 1,
    password: null,
    confirm_password: null,
    roles: ['6'],
    codigo_modular: null,
    especialidad: null,
    condicion: null,
    escala_magisterial: null

});

const formData = ref(initialFormData());
const formErrors = ref({});

watch(() => props.show, () => {
    if (props.show) {
        //  console.log('props de docente', props.docente?.id);
        if (props.docente?.id) {


            formData.value = Object.assign(
                {},
                initialFormData(),
                props.docente,
                props.docente.docente || {}
            );

        } else {
            formData.value = initialFormData();
        }
        formErrors.value = {};
    }
});



const schema = yup.object().shape({
    name: yup.string().nullable().required("El nombre es requerido."),
    apellido_paterno: yup.string().nullable().required("El apellido paterno es requerido."),
    apellido_materno: yup.string().nullable().required("El apellido materno es requerido."),
    usuario: yup.string().nullable().required("El usuario es requerido."),
    dni: yup.string().nullable().required("El DNI es requerido.").matches(/^[0-9]+$/, "El DNI solo debe contener números.")
        .length(8, "El DNI debe tener exactamente 8 dígitos."),
    email: yup.string().email("Debe ser un email válido.").nullable().required("El email es requerido."),
    fecha_nacimiento: yup.date().nullable().required("La fecha de nacimiento es requerida."),
    telefono: yup.string().nullable().required("El teléfono es requerido."),
    direccion: yup.string().nullable().required("La dirección es requerida."),
    status: yup.bool().required(),
    password: yup.string().nullable().test('password-test', '', (value, { createError }) => {
        if (props.docente?.id) return true;
        if (!value) return createError({ message: 'La contraseña es requerida.' });
        if (value.length < 8) return createError({ message: 'La contraseña debe tener al menos 8 caracteres.' });
        if (value !== formData.value.confirm_password) return createError({ message: "Las contraseñas no coinciden." });
        return true;
    }),
    codigo_modular: yup.string().nullable().required("El código modular es requerido."),
    especialidad: yup.string().nullable().required("La especialidad es requerido."),
    condicion: yup.string().nullable(),
    escala_magisterial: yup.string().nullable(),
});

const onSubmit = async () => {


    if (saving.value || updating.value) return;
    let data = { ...formData.value, roles: formData.value.roles[0] };
    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    formErrors.value = {};
    const fieldsToBeOmitted = ['confirm_password'];
    if (props.docente?.id) fieldsToBeOmitted.push('password');
    data = omitPropsFromObject(data, fieldsToBeOmitted);
    const docenteId = props.docente?.id;
    const response = docenteId ? await updateDocente(docenteId, data) : await createDocente(data);
    if (response?.user.id) {
        showToast(`Docente ${props.docente?.id ? 'actualizado' : 'creado'} correctamente.`);

        docenteStore.loadDocentes();
        emit('hide');
    }
};
</script>

<template>
    <Slider :show="show" :title="title" @hide="emit('hide')">
        <AuthorizationFallback :permissions="requiredPermissions">

            <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

            <div class="mt-4 space-y-3">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.name" label="Nombres" :error="formErrors?.name" required
                        :uppercase="true" />
                    <FormInput v-model="formData.apellido_paterno" label="Apellido Paterno"
                        :error="formErrors?.apellido_paterno" required :uppercase="true" />
                    <FormInput v-model="formData.apellido_materno" label="Apellido Materno"
                        :error="formErrors?.apellido_materno" required :uppercase="true" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.usuario" label="Usuario" :error="formErrors?.usuario"
                        class="md:col-span-1" required />
                    <FormInput v-model="formData.dni" label="DNI" :error="formErrors?.dni" required />
                    <FormInput v-model="formData.telefono" label="Teléfono" :error="formErrors?.telefono" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormInput v-model="formData.email" label="Email" :error="formErrors?.email" />
                    <FormInput v-model="formData.direccion" label="Dirección" :error="formErrors?.direccion" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.codigo_modular" label="Código Modular"
                        :error="formErrors?.codigo_modular" required />
                    <FormInput v-model="formData.especialidad" label="Especialidad" :error="formErrors?.especialidad"
                        required />
                    <FormInput v-model="formData.rd_nombramiento" label="Resolución Directorial"
                        :error="formErrors?.rd_nombramiento" required />

                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <FormInput v-model="formData.condicion" label="Condición" :error="formErrors?.condicion" required />
                    <FormInput v-model="formData.escala_magisterial" label="Escala Magisterial"
                        :error="formErrors?.escala_magisterial" autocomplete="off" required />
                    <FormInput v-model="formData.fecha_nacimiento" label="Fecha de Nacimiento" type="date"
                        :error="formErrors?.fecha_nacimiento" required />
                </div>



                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <template v-if="!docente?.id">
                        <FormInput v-model="formData.password" label="Contraseña" type="password"
                            :error="formErrors?.password" required />
                        <FormInput v-model="formData.confirm_password" type="password" label="Confirmar Contraseña"
                            :error="formErrors?.confirm_password" required />
                        <CheckBox v-model="formData.status" label="Estado"
                            class="mt-8 pl-4 flex justify-center items-centers" />
                    </template>
                </div>


                <div class="flex gap-2 mt-1">
                    <Button :title="docente?.id ? 'Guardar Cambios' : 'Crear Docente'" key="submit-btn"
                        :disabled="saving || updating" :loading-title="docente?.id ? 'Guardando...' : 'Creando...'"
                        class="!mt-6 !w-full" :loading="saving || updating" @click="onSubmit" />
                    <Button title="Cancelar" variant="outline" @click="emit('hide');"
                        class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer !mt-6 h-10" />
                </div>
            </div>
        </AuthorizationFallback>
    </Slider>
</template>