<script setup>
import { computed, nextTick, ref, watch } from "vue";

import useUserStore from "../../../store/useUserStore";
import useRoleStore from "../../../store/useRoleStore";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";

import * as yup from "yup";
import Slider from "../../ui/Slider.vue";
import FormInput from "../../ui/FormInput.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../AuthorizationFallback.vue";
import useCicloStore from "../../../store/Ciclo/useCicloStore";
import BaseSelect from "../../ui/BaseSelect.vue";
import useEspecialidadStore from "../../../store/Especialidad/useEspecialidadStore";
import BaseSelectCiclo from "../../ui/BaseSelectCiclo.vue";


const props = defineProps({
  show: {
    type: Boolean,
    default: () => false,
  },
  especialidad: {
    type: [Object, null],
    default: () => null,
  },
  ciclo: {
    type: Array,
    default: () => []
  }
});
const emit = defineEmits(["hide"]);

const userStore = useUserStore();
const roleStore = useRoleStore();
const especialidadStore = useEspecialidadStore();


const { store: createEspecialidad, saving, update: updateEspecialidad, updating } = useHttpRequest(
  "/especialidad_madre"
);
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
  if (!props.role?.id) return ["todo-acceso-roles", "crear-roles"];
  else return ["todo-acceso-roles", "editar-roles"];
});

const initialFormData = () => {
  return {
    nombre_especialidad: null,
    id_ciclo: null,
  };
};

const formData = ref(initialFormData());
const formErrors = ref({});

const isEditing = computed(() => !!props.especialidad?.id);

const onCancelEdit = () => {
  formData.value = initialFormData();
  formErrors.value = {};
  emit("hide"); // oculta el formulario
};

watch(
  () => props.especialidad,
  (newRole) => {
    if (props.show && newRole?.id) {
      console.log(formData.value);
      formData.value = Object.entries(initialFormData()).reduce((r, [key, val]) => {
        if (newRole[key]) return { ...r, [key]: newRole[key] };
        return { ...r, [key]: val };
      }, {});
    }
  },
  { immediate: true }
);

const schema = yup.object().shape({
  nombre_especialidad: yup.string().nullable().required("El nombre de la especialidad es obligatorio"),
  id_ciclo: yup.string().nullable().required("Elegir el ciclo."),
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

  const response = props.especialidad?.id
    ? await updateEspecialidad(props.especialidad?.id, data)
    : await createEspecialidad(data);

  if (response?.id) {
    showToast(`especialidad ${props.especialidad?.id ? "editado" : "creado"} exitosamente.`);
    especialidadStore.loadEspecialidad();

    formData.value = initialFormData();
    formErrors.value = {};
    emit("hide");

  }
};
</script>

<template>
  <AuthorizationFallback :permissions="requiredPermissions">
    <div class="mt-2 space-y-1.5 font-inter">


      <FormLabelError label="Ciclo" :error="formErrors?.id_ciclo" required>
        <BaseSelectCiclo v-model="formData.id_ciclo" :options="ciclo" label="nombre_ciclo"
          placeholder="Seleccione un ciclo" />
      </FormLabelError>
      <FormInput v-model="formData.nombre_especialidad" :focus="show" label="Nombre de la especialidad"
        :error="formErrors?.nombre_especialidad" required :uppercase="true"/>

      <div class="w-full space-y-3">

        <div class="flex gap-2 mt-1">
          <!-- Botón Guardar: ancho completo -->
          <Button :title="especialidad?.id ? 'Guardar Cambios' : 'Crear Especialidad'"
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
