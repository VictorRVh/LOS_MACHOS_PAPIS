<script setup>
import { computed, ref, watch } from 'vue';
import Slider from '../ui/Slider.vue';
import FormInput from '../ui/FormInput.vue';
import FormLabelError from '../ui/FormLabelError.vue';
import VSelect from 'vue-select';
import Button from '../ui/Button.vue';
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue';

import useUserStore from '../../store/useUserStore';
import useRoleStore from '../../store/useRoleStore';
import usePermissionStore from '../../store/usePermissionStore';
import useValidation from '../../composables/useValidation';
import useHttpRequest from '../../composables/useHttpRequest';
import useModalToast from '../../composables/useModalToast';

import * as yup from 'yup';

const props = defineProps({
    show: {
        type: Boolean,
        default: () => false,
    },
    role: {
        type: [Object, null],
        default: () => null,
    },
});
const emit = defineEmits(['hide']);

const userStore = useUserStore();
const roleStore = useRoleStore();
const permissionStore = usePermissionStore();

const {
    store: createRole,
    saving,
    update: updateRole,
    updating,
} = useHttpRequest('/roles');
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
    if (!props.role?.id) return ['todo-acceso-roles', 'crear-roles'];
    else return ['todo-acceso-roles', 'editar-roles'];
});

const title = computed(() =>
    props.role ? `Editar rol "${props.role?.name}"` : 'Agregar nuevo rol',
);

const initialFormData = () => {
    return {
        name: null,
        permissions: [],
    };
};

const formData = ref(initialFormData());
const formErrors = ref({});

watch(
    () => props.show,
    () => {
        if (props.show) {
            if (props.role?.id) {
                formData.value = Object.entries(initialFormData()).reduce(
                    (r, [key, val]) => {
                        if (props.role[key])
                            return { ...r, [key]: props.role[key] };
                        return { ...r, [key]: val };
                    },
                    {},
                );
            } else {
                formData.value = initialFormData();
                formErrors.value = {};
            }
        }
    },
);

const permissionOptions = computed(() => {
    const formDataPermissionIds = formData.value.permissions.map((permission) =>
        permission?.id?.toString(),
    );
    return permissionStore.permissions.filter(
        (permission) =>
            !formDataPermissionIds.includes(permission?.id?.toString()),
    );
});

const selectedPermission = ref(null);
const onPermissionSelect = (permission) => {
    formData.value = {
        ...formData.value,
        permissions: [permission].concat(formData.value.permissions),
    };
    selectedPermission.value = null;
};
const onPermissionRemove = (permission) => {
    const updatedPermissions = formData.value.permissions.filter(
        (fp) => fp?.id?.toString() !== permission?.id?.toString(),
    );

    formData.value = {
        ...formData.value,
        permissions: updatedPermissions,
    };
};

const canShowAddAllPermissions = computed(() =>
    Boolean(
        formData.value.permissions.length !==
            permissionStore.permissions.length,
    ),
);
const onAddAllPermissions = () => {
    formData.value = {
        ...formData.value,
        permissions: permissionStore.permissions,
    };
};

const schema = yup.object().shape({
    name: yup.string().nullable().required(),
});

const onSubmit = async () => {
    if (saving.value || updating.value) return;

    let data = {
        ...formData.value,
        permissions: formData.value.permissions
            ?.map((permission) => permission?.id)
            ?.sort((a, b) => Number(a) - Number(b)),
    };

    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }
    formErrors.value = {};

    const response = props.role?.id
        ? await updateRole(props.role?.id, data)
        : await createRole(data);

    if (response?.id) {
        showToast(
            `Rol ${props.role?.id ? 'editado' : 'creado'} exitosamente.`,
        );
        roleStore.loadRoles();
        userStore.loadUsers();
        emit('hide');
    }
};
</script>

<template>
    <Slider :show="show" :title="title" @hide="emit('hide')">
        <AuthorizationFallback :permissions="requiredPermissions">
            <div class="mt-4 space-y-6 font-inter">
                <FormInput v-model="formData.name" :focus="show" label="Nombre del rol" :error="formErrors?.name" required />
                <FormLabelError label="Añadir permiso">
                    <VSelect v-model="selectedPermission" :options="permissionOptions" label="name" @update:model-value="(permission) => onPermissionSelect(permission)" />
                </FormLabelError>
                <div class="w-full space-y-4">
                    <div class="flex-between gap-4">
                        <label class="text-sm font-semibold dark:text-slate-300">Permisos del Rol</label>
                        <div v-if="canShowAddAllPermissions" class="cursor-pointer text-xs font-bold text-sky-500 hover:underline dark:text-sky-400" @click="onAddAllPermissions">
                            Añadir todos los permisos
                        </div>
                    </div>
                    <TransitionGroup tag="ul" name="edit-list" class="relative space-y-3">
                        <li v-for="permission in formData.permissions" :key="permission.id" class="rounded-md shadow-sm">
                            <div class="flex-between w-full rounded-md border border-slate-200 bg-white p-3 dark:border-slate-600 dark:bg-slate-700">
                                <div class="flex-1 dark:text-slate-200">{{ permission.name }}</div>
                                <span class="cursor-pointer text-sm text-red-500 hover:text-red-700 dark:text-red-500 dark:hover:text-red-400" @click="onPermissionRemove(permission)">
                                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </span>
                            </div>
                        </li>
                        <Button :title="role?.id ? 'Guardar Cambios' : 'Crear Rol'" :loading-title="role?.id ? 'Guardando...' : 'Creando...'" class="!mt-6 !w-full" :loading="saving || updating" key="submit-btn" @click="onSubmit" />
                    </TransitionGroup>
                </div>
            </div>
        </AuthorizationFallback>
    </Slider>
</template>