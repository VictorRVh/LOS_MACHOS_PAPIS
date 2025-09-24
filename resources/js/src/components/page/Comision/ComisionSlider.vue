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
  comisionesStore.loadComisionesUserFilter();
  //console.log("sacar: ", userStore?.users);
});

const requiredPermissions = computed(() => {
  return props.comision?.id
    ? ["todo-acceso-roles", "editar-roles"]
    : ["todo-acceso-roles", "crear-roles"];
});

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
        if (newComision[key]) return { ...r, [key]: newComision[key] };
        return { ...r, [key]: val };
      }, {});
    }
  },
  { immediate: true }
);

// Validación Yup
const schema = yup.object().shape({
  titulo: yup.string().required("El nombre de la comisión es obligatorio."),
  descripcion: yup.string().nullable(),
  usuarios: yup.array().min(1, "Debe seleccionar al menos un usuario para la comisión."),
});

// Opciones para el select de usuarios
const selectedUsuario = ref(null);


const usuarioOptions = computed(() => {
  const selectedIds = formData.value.usuarios.map((u) => u.id);
  return comisionesStore.users
    .filter((u) => !selectedIds.includes(u.id))
    .map((u) => ({
      ...u,
      nameCompleto: u.nameCompleto
    }));
});

const onUsuarioSelect = (usuario) => {
  if (!formData.value.usuarios.find((u) => u.id === usuario.id)) {
    formData.value.usuarios = [usuario, ...formData.value.usuarios];
  }
  selectedUsuario.value = null;
};

const onUsuarioRemove = (usuario) => {
  formData.value = {
    ...formData.value,
    usuarios: formData.value.usuarios.filter((fp) => fp.id !== usuario.id),
  };
};


// Envío de formulario
const onSubmit = async () => {
  if (saving.value || updating.value) return;

  const data = {
    ...formData.value,
    usuarios: formData.value.usuarios.map((u) => u.id), // solo IDs al backend
  };

  console.log('data de comosion: ', data)

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
    comisionesStore.loadComisionesUserFilter(); // 🔥 refresca usuarios disponibles
    formData.value = initialFormData();
    formErrors.value = {};

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
      <FormInput v-model="formData.titulo" :focus="show" label="Título de comisión" :error="formErrors?.titulo" />
      <!-- Select de usuarios -->
      <FormLabelError label="Añadir integrantes" :error="formErrors.usuarios">
        <BaseSelect v-model="selectedUsuario" :options="usuarioOptions" label="nameCompleto"
          placeholder="Seleccione un usuario" @update:modelValue="onUsuarioSelect" :loading="comisionesStore.loading" />
      </FormLabelError>


      <div>
        <label class="text-sm font-semibold dark:text-slate-300 mb-1 block">
          Usuarios de la Comisión
        </label>
        <SelectedChips :items="formData.usuarios" labelKey="nameCompleto" @remove="onUsuarioRemove" />
      </div>

      <FormInput v-model="formData.descripcion" :focus="show" label="Descripción" :error="formErrors?.descripcion" />

      <div class="flex gap-2 mt-4">
        <Button :title="comision?.id ? 'Guardar Cambios' : 'Crear Comisión'"
          :loading-title="comision?.id ? 'Guardando...' : 'Creando...'" :loading="saving || updating" @click="onSubmit"
          class="!w-full" />
        <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
          class="bg-red-500 text-white hover:bg-red-600 px-4" />
      </div>
    </div>
  </AuthorizationFallback>
</template>
