<script setup>
import { computed, nextTick, ref, watch } from 'vue';
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
import SelectedChips from '../ui/selectedChips.vue';
import BaseSelect from '../ui/BaseSelect.vue';


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

const isEditing = computed(() => !!props.role?.id);

const onCancelEdit = () => {
    formData.value = initialFormData();
    formErrors.value = {};
    emit('hide'); // oculta el formulario
};

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

watch(
    () => props.role,
    (newRole) => {
        if (props.show && newRole?.id) {
            formData.value = Object.entries(initialFormData()).reduce(
                (r, [key, val]) => {
                    if (newRole[key]) return { ...r, [key]: newRole[key] };
                    return { ...r, [key]: val };
                },
                {}
            );
        }
    },
    { immediate: true }
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

        if (!props.role?.id) {
            formData.value = initialFormData();
            formErrors.value = {};
        }
        emit('hide');
    }
};
</script>

<template>

    <AuthorizationFallback :permissions="requiredPermissions">
        <div class="mt-4 space-y-6 font-inter">
            <FormInput v-model="formData.name" :focus="show" label="Nombre del rol" :error="formErrors?.name"
                required />
            <FormLabelError label="Añadir permiso">
                <BaseSelect v-model="selectedPermission" :options="permissionOptions" label="name"
                    placeholder="Seleccione un permiso" @update:modelValue="onPermissionSelect" />
            </FormLabelError>
            <div class="w-full space-y-4">
                <div class="flex-between gap-4">
                    <label class="text-sm font-semibold dark:text-slate-300">Permisos del Rol</label>
                    <div v-if="canShowAddAllPermissions"
                        class="cursor-pointer text-xs font-bold text-sky-500 hover:underline dark:text-sky-400"
                        @click="onAddAllPermissions">
                        Añadir todos los permisos
                    </div>
                </div>


                <SelectedChips :items="formData.permissions" @remove="onPermissionRemove" />
                <Button :title="role?.id ? 'Guardar Cambios' : 'Crear Rol'" Add commentMore actions
                    :loading-title="role?.id ? 'Guardando...' : 'Creando...'" class="!mt-6 !w-full"
                    :loading="saving || updating" key="submit-btn" @click="onSubmit" />

                <div class="flex justify-end">
                    <Button v-if="isEditing" title="Cancelar edición" variant="outline" @click="onCancelEdit" />
                </div>

            </div>
        </div>
    </AuthorizationFallback>

</template>