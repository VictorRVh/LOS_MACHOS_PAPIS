<script setup>
import { computed, onMounted, ref, watch } from "vue";
import FormInput from "../../ui/FormInput.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../../components/page/AuthorizationFallback.vue";
import BaseSelect from "../../ui/BaseSelect.vue";
import SelectedChips from "../../ui/SelectedChips.vue";

import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";

import useComisionesStore from "../../../store/Comision/useComisionesStore";

import useUserStore from "../../../store/useUserStore";

import * as yup from "yup";

const props = defineProps({
  show: {
    type: Boolean,
    default: () => false,
  },
  comision: {
    type: [Object, null],
    default: () => null,
  },
});
const emit = defineEmits(["hide"]);

const comisionesStore = useComisionesStore();
const userStore = useUserStore();

const {
  store: createComision,
  saving,
  update: updateComision,
  updating,
} = useHttpRequest("/comisiones");
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

onMounted(() => {
  userStore.loadUsers();
  console.log("sacar: ", userStore?.users);
});

const requiredPermissions = computed(() => {
  return props.comision?.id
    ? ["todo-acceso-roles", "editar-roles"]
    : ["todo-acceso-roles", "crear-roles"];
});

const title = computed(() =>
  props.comision
    ? `Editar comisión "${props.comision?.titulo}"`
    : "Agregar nueva comisión"
);

const initialFormData = () => ({
  titulo: null,
  descripcion: null,
  usuarios: [],
});

const formData = ref(initialFormData());
const formErrors = ref({});
const isEditing = computed(() => !!props.comision?.id);

watch(
  () => props.comision,
  (newComision) => {
    if (props.show && newComision?.id) {
      formData.value = Object.entries(initialFormData()).reduce((r, [key, val]) => {
        return {
          ...r,
          [key]: newComision[key] ?? val,
        };
      }, {});
    }
  },
  { immediate: true }
);

// Validación Yup
const schema = yup.object().shape({
  titulo: yup
    .string()
    .required("El nombre de la comisión es obligatorio.")
    .matches(/^\d{4}-I{1,2}$/, "Formato inválido. Usa: 2024-I o 2024-II"),
  descripcion: yup.string().nullable(),
  usuarios: yup.array().min(1, "Debe seleccionar al menos un usuario para la comisión."),
});

// Opciones para el select de usuarios
const selectedUsuario = ref(null);

const usuarioOptions = computed(() => {
  const selectedIds = formData.value.usuarios.map((u) => u.id);
  return userStore.users.filter((u) => !selectedIds.includes(u.id));
});

const onUsuarioSelect = (usuario) => {
  console.log("SELECCIONADO:", usuario); // <- agrega esto

};

const onUsuarioRemove = (usuario) => {
  const updatedUsers = formData.value.usuarios.filter((fp) => fp?.id?.toString() !== usuario?.id?.toString());
  formData.value = {
    ...formData.value,
    usuarios: updatedUsers,
  };
};

// Envío de formulario
const onSubmit = async () => {
  if (saving.value || updating.value) return;

  const data = {
    ...formData.value,
    usuarios: formData.value.usuarios.map((u) => u.id), // solo IDs al backend
  };

  const { validated, errors } = await runYupValidation(schema, data);
  if (!validated) {
    formErrors.value = errors;
    return;
  }

  formErrors.value = {};

  const response = props.comision?.id
    ? await updateComision(props.comision?.id, data)
    : await createComision(data);

  if (response?.id) {
    showToast(`Comisión ${props.comision?.id ? "editada" : "creada"} exitosamente.`);
    comisionesStore.loadComisiones();

    if (!props.comision?.id) {
      formData.value = initialFormData();
      formErrors.value = {};
    }

    emit("hide");
  }
};

const onCancelEdit = () => {
  formData.value = initialFormData();
  formErrors.value = {};
  emit("hide");
};
</script>

<template>
  <AuthorizationFallback :permissions="requiredPermissions">
    <div class="mt-2 space-y-1.5 font-inter">
      <FormInput
        v-model="formData.titulo"
        :focus="show"
        label="Título de comisión"
        :error="formErrors?.titulo"
      />

      <FormInput
        v-model="formData.descripcion"
        :focus="show"
        label="Descripción"
        :error="formErrors?.descripcion"
      />

      <!-- Select de usuarios -->
      <FormLabelError label="Añadir integrantes">
        <BaseSelect
          v-model="selectedUsuario"
          :options="usuarioOptions"
          label="name"
          placeholder="Seleccione un usuario"
          @update:modelValue="onUsuarioSelect"
        />
      </FormLabelError>

      <div>
        <label class="text-sm font-semibold dark:text-slate-300 mb-1 block">
          Usuarios de la Comisión
        </label>
        <SelectedChips
          :items="formData.usuarios"
          @remove="onUsuarioRemove"
        />
      </div>

      <div class="flex gap-2 mt-4">
        <Button
          :title="comision?.id ? 'Guardar Cambios' : 'Crear Comisión'"
          :loading-title="comision?.id ? 'Guardando...' : 'Creando...'"
          :loading="saving || updating"
          @click="onSubmit"
          class="!w-full"
        />
        <Button
          v-if="isEditing"
          title="Cancelar"
          variant="outline"
          @click="onCancelEdit"
          class="bg-red-500 text-white hover:bg-red-600 px-4"
        />
      </div>
    </div>
  </AuthorizationFallback>
</template>
