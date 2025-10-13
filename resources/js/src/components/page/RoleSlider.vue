<script setup>
import { computed, nextTick, ref, watch } from "vue";
import Slider from "../ui/Slider.vue";
import FormInput from "../ui/FormInput.vue";
import FormLabelError from "../ui/FormLabelError.vue";
import VSelect from "vue-select";
import Button from "../ui/Button.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";

import useUserStore from "../../store/useUserStore";
import useRoleStore from "../../store/useRoleStore";
import usePermissionStore from "../../store/usePermissionStore";
import useValidation from "../../composables/useValidation";
import useHttpRequest from "../../composables/useHttpRequest";
import useModalToast from "../../composables/useModalToast";

import * as yup from "yup";
import SelectedChips from "../ui/selectedChips.vue";
import BaseSelect from "../ui/BaseSelect.vue";

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
const emit = defineEmits(["hide"]);

const userStore = useUserStore();
const roleStore = useRoleStore();
const permissionStore = usePermissionStore();

const { store: createRole, saving, update: updateRole, updating } = useHttpRequest(
  "/roles"
);
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
  if (!props.role?.id) return ["todo-acceso-roles", "crear-roles"];
  else return ["todo-acceso-roles", "editar-roles"];
});

const title = computed(() =>
  props.role ? `Editar rol "${props.role?.name}"` : "Agregar nuevo rol"
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
  emit("hide"); // oculta el formulario
};

watch(
  () => props.role,
  (newRole) => {
    if (props.show && newRole?.id) {
      console.log(formData.value);
      formData.value = Object.entries(initialFormData()).reduce((r, [key, val]) => {
        if (newRole[key]) return { ...r, [key]: newRole[key] };
        return { ...r, [key]: val };
      }, {});
      formErrors.value = {};
    }
  },
  { immediate: true }
);

const permissionOptions = computed(() => {
  const formDataPermissionIds = formData.value.permissions.map((permission) =>
    permission?.id?.toString()
  );
  return permissionStore.permissions.filter(
    (permission) => !formDataPermissionIds.includes(permission?.id?.toString())
  );
});

const selectedPermission = ref(null);
const onPermissionSelect = (permission) => {
  console.log("selec: ", permission)
  formData.value = {
    ...formData.value,
    permissions: [permission].concat(formData.value.permissions),
  };
  selectedPermission.value = null;
};
const onPermissionRemove = (permission) => {
  const updatedPermissions = formData.value.permissions.filter(
    (fp) => fp?.id?.toString() !== permission?.id?.toString()
  );

  formData.value = {
    ...formData.value,
    permissions: updatedPermissions,
  };
};

const canShowAddAllPermissions = computed(() =>
  Boolean(formData.value.permissions.length !== permissionStore.permissions.length)
);
const onAddAllPermissions = () => {
  formData.value = {
    ...formData.value,
    permissions: permissionStore.permissions,
  };
};

const schema = yup.object().shape({
  name: yup.string().nullable().required('El nombre del rol es obligatorio.'),
  permissions: yup.array().min(1, "Debe seleccionar al menos un permiso para este rol."),
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
    showToast(`Rol ${props.role?.id ? "editado" : "creado"} exitosamente.`);
    roleStore.loadRoles();
    userStore.loadUsers();

    
    formData.value = initialFormData();
    formErrors.value = {};
    
    emit("hide");
  }
};
</script>

<template>
  <AuthorizationFallback :permissions="requiredPermissions">
    <div class="mt-2 space-y-1.5 font-inter">
      <FormInput v-model="formData.name" :focus="show" label="Nombre del rol" :error="formErrors?.name" required />
      <FormLabelError label="Añadir permiso" :error="formErrors.permissions">
        <BaseSelect v-model="selectedPermission" :options="permissionOptions" label="name"
          placeholder="Seleccione un permiso" @update:modelValue="onPermissionSelect" />
      </FormLabelError>
      <div class="w-full space-y-3">
        <div class="flex-between gap-6">
          <label class="text-sm font-semibold dark:text-slate-300">Permisos del Rol</label>
          <div v-if="canShowAddAllPermissions"
            class="cursor-pointer text-sm font-bold text-sky-500 hover:underline dark:text-sky-400"
            @click="onAddAllPermissions">
            Añadir todos los permisos
          </div>
        </div>

        <SelectedChips :items="formData.permissions" @remove="onPermissionRemove" />

        <div class="flex gap-2 mt-1">
          <!-- Botón Guardar: ancho completo -->
          <Button :title="role?.id ? 'Guardar Cambios' : 'Crear Rol'"
            :loading-title="role?.id ? 'Guardando...' : 'Creando...'"
             :loading="saving || updating" key="submit-btn"
             :disabled="saving || updating"
            @click="onSubmit" class="!w-full" />

          <!-- Botón Cancelar: ancho flexible solo si se está editando -->
          <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
            class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer px-4" />
        </div>
      </div>
    </div>
  </AuthorizationFallback>
</template>
