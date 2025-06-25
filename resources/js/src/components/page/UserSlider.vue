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
    if (!props.user?.id) return ['users-all', 'users-create'];
    else return ['users-all', 'users-edit'];
});

const title = computed(() => (props.user ? `Actualizar Usuario "${props.user?.name}"` : 'Añadir Nuevo Usuario'));


const initialFormData = () => ({
    name: null,
    apellido_paterno: null,
    apellido_materno: null,
    email: null,
    fecha_nacimiento: null,
    telefono: null,
    direccion: null,
    status: 1, 
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

const roleOptions = computed(() => {
    const formDataRoleIds = formData.value.roles.map((role) => role?.id?.toString());
    return roleStore.roles.filter(
        (role) => !formDataRoleIds.includes(role?.id?.toString()) && role?.name !== 'super-admin'
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
    email: yup.string().email("Debe ser un email válido.").nullable().required("El email es requerido."),
    fecha_nacimiento: yup.date().nullable().required("La fecha de nacimiento es requerida."),
    telefono: yup.string().nullable().required("El teléfono es requerido."),
    direccion: yup.string().nullable().required("La dirección es requerida."),
    status: yup.number().required(),
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
            <div class="mt-4 space-y-6">
              
                <FormInput v-model="formData.name" :focus="show" label="Nombres" :error="formErrors?.name" required />
                <FormInput v-model="formData.apellido_paterno" label="Apellido Paterno" :error="formErrors?.apellido_paterno" required />
                <FormInput v-model="formData.apellido_materno" label="Apellido Materno" :error="formErrors?.apellido_materno" required />
                <FormInput v-model="formData.email" label="Email" type="email" :error="formErrors?.email" required />
                <FormInput v-model="formData.fecha_nacimiento" label="Fecha de Nacimiento" type="date" :error="formErrors?.fecha_nacimiento" required />
                <FormInput v-model="formData.telefono" label="Teléfono" :error="formErrors?.telefono" required />
                <FormInput v-model="formData.direccion" label="Dirección" :error="formErrors?.direccion" required />
                
                <template v-if="!user?.id">
                    <FormInput v-model="formData.password" label="Contraseña" type="password" :error="formErrors?.password" required />
                    <FormInput v-model="formData.confirm_password" type="password" label="Confirmar Contraseña" required />
                </template>

                <FormLabelError label="Añadir Rol">
                    <VSelect v-model="selectedRole" :options="roleOptions" label="name" @update:model-value="(role) => onRoleSelect(role)" />
                </FormLabelError>

                <div v-if="formData.roles?.length" class="w-full space-y-4">
                    <FormLabelError label="Roles del Usuario" />
                    <ul class="relative space-y-3">
                        <li v-for="role in formData.roles" :key="role.id" class="rounded-md shadow-sm">
                            <div class="flex-between w-full rounded-md border border-slate-200 bg-white p-3 dark:border-slate-600 dark:bg-slate-700">
                                <div class="flex-1 dark:text-slate-200">{{ role.name }}</div>
                                <span class="cursor-pointer text-sm text-red-500 hover:text-red-700 dark:text-red-500 dark:hover:text-red-400" @click="onRoleRemove(role)">
                                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>

                <Button
                    :title="user?.id ? 'Guardar Cambios' : 'Crear Usuario'"
                    key="submit-btn"
                    :loading-title="user?.id ? 'Guardando...' : 'Creando...'"
                    class="!mt-6 !w-full"
                    :loading="saving || updating"
                    @click="onSubmit"
                />
            </div>
        </AuthorizationFallback>
    </Slider>
</template>