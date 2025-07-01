<script setup>
import { computed, ref, watch } from 'vue';
import Slider from '../ui/Slider.vue';
import FormInput from '../ui/FormInput.vue';
import FormLabelError from '../ui/FormLabelError.vue';
import VSelect from 'vue-select';
import Button from '../ui/Button.vue';
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue';
import useRoleStore from '../../store/useRoleStore';
import useUserStore from '../../store/useUserStore';
import useValidation from '../../composables/useValidation';
import useHttpRequest from '../../composables/useHttpRequest';
import useUtils from '../../composables/useUtils';
import useModalToast from '../../composables/useModalToast';
import * as yup from 'yup';
import SelectedChips from '../ui/selectedChips.vue';
import CheckBox from '../ui/CheckBox.vue';
import BaseSelect from '../ui/BaseSelect.vue';

const props = defineProps({
    show: { type: Boolean, default: () => false },
    user: { type: [Object, null], default: () => null },
});
const emit = defineEmits(['hide']);

const userStore = useUserStore();
const roleStore = useRoleStore();
const { store: createUser, saving, update: updateUser, updating } = useHttpRequest('/users');
const { runYupValidation } = useValidation();
const { omitPropsFromObject } = useUtils();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
    if (!props.user?.id) return ['todo-acceso-usuarios', 'crear-usuarios'];
    else return ['todo-acceso-usuarios', 'editar-usuarios'];
});

const title = computed(() => (props.user ? `Actualizar Usuario "${props.user?.name}"` : 'Añadir Nuevo Usuario'));


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
    status: null,
    password: null,
    confirm_password: null,
    roles: [],
});

const formData = ref(initialFormData());
const formErrors = ref({});

watch(() => props.show, () => {
    if (props.show) {
        if (props.user?.id) {
            formData.value = Object.entries(initialFormData()).reduce(
                (r, [key, val]) => ({ ...r, [key]: props.user[key] || val }),
                {}
            );
        } else {
            formData.value = initialFormData();
            formErrors.value = {};
        }
    }
});

watch(
    () => formData.value,
    (newVal) => {
        console.log('cambios en status:', newVal.status);
    },
    { deep: true }
);


const roleOptions = computed(() => {
    const formDataRoleIds = formData.value.roles.map((role) => role?.id?.toString());
    return roleStore.roles.filter(
        (role) => !formDataRoleIds.includes(role?.id?.toString()) && role?.name !== 'directora'
    );
});

const selectedRole = ref(null);
const onRoleSelect = (role) => {
    formData.value.roles.unshift(role);
    selectedRole.value = null;
};
const onRoleRemove = (role) => {
    formData.value.roles = formData.value.roles.filter((fRole) => fRole?.id?.toString() !== role?.id?.toString());
};


const schema = yup.object().shape({
    name: yup.string().nullable().required("El nombre es requerido."),
    apellido_paterno: yup.string().nullable().required("El apellido paterno es requerido."),
    apellido_materno: yup.string().nullable().required("El apellido materno es requerido."),
    usuario: yup.string().nullable().required("El usuario es requerido."),
    dni: yup.string().nullable().required("El dni es requerido."),
    email: yup.string().email("Debe ser un email válido.").nullable().required("El email es requerido."),
    fecha_nacimiento: yup.date().nullable().required("La fecha de nacimiento es requerida."),
    telefono: yup.string().nullable().required("El teléfono es requerido."),
    direccion: yup.string().nullable().required("La dirección es requerida."),
    status: yup.bool().required(),
    password: yup.string().nullable().test('password-test', '', (value, { createError }) => {
        if (props.user?.id) return true;
        if (!value) return createError({ message: 'La contraseña es requerida.' });
        if (value.length < 8) return createError({ message: 'La contraseña debe tener al menos 8 caracteres.' });
        if (value !== formData.value.confirm_password) return createError({ message: "Las contraseñas no coinciden." });
        return true;
    }),
});

const onSubmit = async () => {
    if (saving.value || updating.value) return;
    let data = { ...formData.value, roles: formData.value.roles?.map((role) => role?.id).sort((a, b) => Number(a) - Number(b)) };
    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    console.log(formData.value)

    formErrors.value = {};
    const fieldsToBeOmitted = ['confirm_password'];
    if (props.user?.id) fieldsToBeOmitted.push('password');
    data = omitPropsFromObject(data, fieldsToBeOmitted);
    const response = props.user?.id ? await updateUser(props.user?.id, data) : await createUser(data);
    if (response?.id) {
        showToast(`Usuario ${props.user?.id ? 'actualizado' : 'creado'} correctamente.`);
        userStore.loadUsers();
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
                    <FormInput v-model="formData.name" label="Nombres" :error="formErrors?.name" required />
                    <FormInput v-model="formData.apellido_paterno" label="Apellido Paterno"
                        :error="formErrors?.apellido_paterno" required />
                    <FormInput v-model="formData.apellido_materno" label="Apellido Materno"
                        :error="formErrors?.apellido_materno" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.usuario" label="Usuario" :error="formErrors?.usuario"
                        class="md:col-span-1" required />
                    <FormInput v-model="formData.dni" label="DNI" :error="formErrors?.dni" required />
                    <FormInput v-model="formData.telefono" label="Teléfono" :error="formErrors?.telefono" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormInput v-model="formData.email" label="Email" />
                    <FormInput v-model="formData.direccion" label="Dirección" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.fecha_nacimiento" label="Fecha de Nacimiento" type="date"
                        :error="formErrors?.fecha_nacimiento" required />
                    <template v-if="!user?.id">
                        <FormInput v-model="formData.password" label="Contraseña" type="password"
                            :error="formErrors?.password" required />
                        <FormInput v-model="formData.confirm_password" type="password" label="Confirmar Contraseña"
                            required />
                    </template>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormLabelError label="Añadir Rol">
                        <BaseSelect v-model="selectedRole" :options="roleOptions" label="name"
                            placeholder="Seleccione un rol" @update:modelValue="onRoleSelect" />
                    </FormLabelError>
                    <CheckBox v-model="formData.status" label="Estado"
                        class="mt-8 pl-4 flex justify-center items-centers" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-4">
                    <SelectedChips :items="formData.roles" @remove="onRoleRemove" />
                </div>

                <Button :title="user?.id ? 'Guardar Cambios' : 'Crear Usuario'" key="submit-btn"
                    :loading-title="user?.id ? 'Guardando...' : 'Creando...'" class="!mt-6 !w-full"
                    :loading="saving || updating" @click="onSubmit" />
            </div>
        </AuthorizationFallback>
    </Slider>
</template>