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
import useConveniosStore from "../../store/Convenio/useConvenioStore";

const props = defineProps({
  show: {
    type: Boolean,
    default: () => false,
  },
  convenio: {
    type: [Object, null],
    default: () => null,
  },
});
const emit = defineEmits(["hide"]);

const userStore = useUserStore();
const roleStore = useRoleStore();
const conveniosStore = useConveniosStore();

const { store: createConvenio, saving, update: updateConvenio, updating } = useHttpRequest(
  "/convenio"
);
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
  if (!props.role?.id) return ["todo-acceso-modalidades", "crear-modalidades"];
  else return ["todo-acceso-modalidades", "editar-modalidades"];
});

const initialFormData = () => {
  return {
    nombre_institucion: null,
    descripcion: null,
  };
};

const formData = ref(initialFormData());
const formErrors = ref({});

const isEditing = computed(() => !!props.convenio?.id);

const onCancelEdit = () => {
  formData.value = initialFormData();
  formErrors.value = {};
  emit("hide"); // oculta el formulario
};

watch(
  () => props.convenio,
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

const schema = yup.object().shape({
  nombre_institucion: yup.string().nullable().required('El nombre de la modalidad es obligatorio.'),
  descripcion: yup
    .string()
    .nullable()          // permite null
    .notRequired()       // no es obligatorio
    .transform((val) => (val === '' ? null : val)) // opcional: trata '' como null
    .max(255, 'La descripción no puede tener más de 255 caracteres')
});

const onSubmit = async () => {
  if (saving.value || updating.value) return;

  let data = {
    ...formData.value,
  };

  const { validated, errors } = await runYupValidation(schema, data);
  if (!validated) {
    formErrors.value = errors;
    return;
  }
  formErrors.value = {};

  const response = props.convenio?.id
    ? await updateConvenio(props.convenio?.id, data)
    : await createConvenio(data);

  // if (response?.id) {

  //   console.log('entra al response', response)

  //   showToast(`Convenio ${props.convenio?.id ? "editado" : "creado"} exitosamente.`);
  //   roleStore.loadRoles();
  //   userStore.loadUsers();
  //   conveniosStore.loadConvenios();

  //   console.log('antes de if ', props.convenio)

  //   if (props.convenio?.id) {

  //     console.log('entra al prop', props.convenio )

  //     formData.value = initialFormData();
  //     formErrors.value = {};
  //   }
  //   emit("hide");
  // }

  if (response?.id) {
    console.log('entra al response', response);

    showToast(`Modalidad ${isEditing ? "creada" : "editada"} exitosamente.`);
    roleStore.loadRoles();
    userStore.loadUsers();
    conveniosStore.loadConvenios();

    formData.value = initialFormData();
    formErrors.value = {};

    emit("hide");
  }
};
</script>

<template>
  <AuthorizationFallback :permissions="requiredPermissions">
    <div class="mt-2 space-y-1.5 font-inter">
      <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
        {{ isEditing ? "Editar modalidad" : "Agregar nuevo modalidad" }}
      </h3>
      <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

      <FormInput v-model="formData.nombre_institucion" :focus="show" label="Nombre de la modalidad"
        :error="formErrors?.nombre_institucion" required />

      <FormInput v-model="formData.descripcion" :focus="show" label="Descripcion" :error="formErrors?.descripcion"
        required />

      <div class="w-full space-y-3">

        <div class="flex gap-2 mt-1">
          <!-- Botón Guardar: ancho completo -->
          <Button :title="convenio?.id ? 'Guardar Cambios' : 'Crear Modalidad'"
            :loading-title="role?.id ? 'Guardando...' : 'Creando...'" :loading="saving || updating" key="submit-btn"
            @click="onSubmit" class="!w-full" />

          <!-- Botón Cancelar: ancho flexible solo si se está editando -->
          <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
            class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer px-4" />
        </div>
      </div>
    </div>
  </AuthorizationFallback>
</template>
