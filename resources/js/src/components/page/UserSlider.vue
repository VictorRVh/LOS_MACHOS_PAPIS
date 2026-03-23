<script setup>
import { computed, ref, watch } from 'vue';
import Slider from '../ui/Slider.vue';
import FormInput from '../ui/FormInput.vue';
import DatePickerInput from '../ui/DatePickerInput.vue';
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


if (!roleStore.roles?.length) await roleStore.loadRoles();

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
    status: false,
    password: null,
    confirm_password: null,
    roles: [],
});

const formData = ref(initialFormData());
const formErrors = ref({});
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const passwordType = computed(() => showPassword.value ? 'text' : 'password');
const confirmPasswordType = computed(() => showConfirmPassword.value ? 'text' : 'password');

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const toggleConfirmPassword = () => {
    showConfirmPassword.value = !showConfirmPassword.value;
};

watch(() => props.show, () => {
    if (props.show) {
        if (props.user?.id) {
            formData.value = Object.entries(initialFormData()).reduce(
                (r, [key, val]) => ({ ...r, [key]: props.user[key] || val }),
                {}
            );
        } else {
            formData.value = initialFormData();

        }
        formErrors.value = {};
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
        (role) => !formDataRoleIds.includes(role?.id?.toString()) && role?.name !== 'super-directora'
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
    dni: yup.string().nullable().required("El DNI es requerido.").matches(/^[0-9]+$/, "El DNI solo debe contener números.")
        .length(8, "El DNI debe tener exactamente 8 dígitos."),

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

    formErrors.value = {};
    const fieldsToBeOmitted = ['confirm_password'];
    if (props.user?.id) fieldsToBeOmitted.push('password');
    data = omitPropsFromObject(data, fieldsToBeOmitted);
    const response = props.user?.id ? await updateUser(props.user?.id, data) : await createUser(data);
    if (response?.id) {
        showToast(`Usuario ${props.user?.id ? 'actualizado' : 'creado'} correctamente.`);
        formData.value = initialFormData();
        formErrors.value = {};
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
                    <FormInput v-model="formData.email" label="Email" />
                    <FormInput v-model="formData.direccion" label="Dirección" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <DatePickerInput v-model="formData.fecha_nacimiento" label="Fecha de Nacimiento"
                        :error="formErrors?.fecha_nacimiento" required />
                    <template v-if="!user?.id">
                        <div class="relative">
                            <label class="block text-sm font-medium mb-1">Contraseña</label>

                            <div class="relative">
                                <input v-model="formData.password" :type="passwordType" class="w-full rounded-md 
           bg-gray-100 text-gray-900 border-gray-300
           dark:bg-gray-800 dark:text-white dark:border-gray-600
           p-3 pr-10" placeholder="••••••••" />

                                <button type="button" @click="togglePassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3">

                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor"
                                        :class="['h-5 w-5', showPassword ? 'text-cetpro' : 'text-gray-400']">

                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </div>

                            <p v-if="formErrors.password" class="text-xs text-red-500">
                                {{ formErrors.password }}
                            </p>
                        </div>
                        <div class="relative">
                            <label class="block text-sm font-medium mb-1">Confirmar Contraseña</label>

                            <div class="relative">
                                <input v-model="formData.confirm_password" :type="confirmPasswordType"
                                    class="w-full rounded-md 
                                    bg-gray-100 text-gray-900 border-gray-300
                                    dark:bg-gray-800 dark:text-white dark:border-gray-600
                                    p-3 pr-10" placeholder="••••••••" />

                                <button type="button" @click="toggleConfirmPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3">

                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor"
                                        :class="['h-5 w-5', showConfirmPassword ? 'text-cetpro' : 'text-gray-400']">

                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </div>

                            <p v-if="formErrors.confirm_password" class="text-xs text-red-500">
                                {{ formErrors.confirm_password }}
                            </p>
                        </div>
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
                <div class="flex gap-2 mt-1">
                    <Button :title="user?.id ? 'Guardar Cambios' : 'Crear Usuario'" key="submit-btn"
                        :loading-title="user?.id ? 'Guardando...' : 'Creando...'" class="!mt-6 !w-full h-10"
                        :loading="saving || updating" @click="onSubmit" :disabled="saving || updating" />

                    <!-- Botón Cancelar: ancho flexible solo si se está editando -->
                    <Button title="Cancelar" variant="outline" @click="emit('hide');"
                        class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer !mt-6 h-10" />
                </div>

            </div>
        </AuthorizationFallback>
    </Slider>
</template>
