<script setup>
import { computed, nextTick, ref, watch } from "vue";
import Slider from "../../ui/Slider.vue";
import FormInput from "../../ui/FormInput.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import VSelect from "vue-select";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../../components/page/AuthorizationFallback.vue";
import CheckBox from "../../ui/CheckBox.vue";

import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";

import * as yup from "yup";
import usePeriodosStore from "../../../store/Periodo/usePeriodoStore";

const props = defineProps({
  show: {
    type: Boolean,
    default: () => false,
  },
  periodo: {
    type: [Object, null],
    default: () => null,
  },
});
const emit = defineEmits(["hide"]);

const periodosStore = usePeriodosStore();

const { store: createPeriodo, saving, update: updatePeriodo, updating } = useHttpRequest(
  "/periodo"
);
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
  if (!props.role?.id) return ["todo-acceso-roles", "crear-roles"];
  else return ["todo-acceso-roles", "editar-roles"];
});

const title = computed(() =>
  props.periodo
    ? `Editar Periodo "${props.periodo?.nombre_periodo}"`
    : "Agregar nuevo rol"
);

const initialFormData = () => {
  return {
    anio: null,
    nombre_periodo: null,
    descripcion: null,
    status: 0,
  };
};

const formData = ref(initialFormData());
const formErrors = ref({});

const isEditing = computed(() => !!props.periodo?.id);

const onCancelEdit = () => {
  formData.value = initialFormData();
  formErrors.value = {};
  emit("hide"); // oculta el formulario
};

watch(
  () => props.periodo,
  (newPeriodo) => {
    if (props.show && newPeriodo?.id) {
      console.log(formData.value);
      formData.value = Object.entries(initialFormData()).reduce((r, [key, val]) => {
        if (newPeriodo[key]) return { ...r, [key]: newPeriodo[key] };
        return { ...r, [key]: val };
      }, {});
    }
  },
  { immediate: true }
);

const schema = yup.object().shape({
  nombre_periodo: yup
    .string()
    .required("El periodo es obligatorio.")
    .matches(/^\d{4}-I{1,2}$/, "Formato inválido. Usa: 2024-I o 2024-II"),
  anio:   yup.string()
    .required("El año del periodo es obligatorio.")
    .matches(/^\d{4}$/, "Formato inválido. Usa: 2024 o 2024"),
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

  const response = props.periodo?.id
    ? await updatePeriodo(props.periodo?.id, data)
    : await createPeriodo(data);

  if (response?.id) {
    showToast(`Periodo ${props.periodo?.id ? "editado" : "creado"} exitosamente.`);

    periodosStore.loadPeriodos();

    if (!props.periodo?.id) {
      formData.value = initialFormData();
      formErrors.value = {};
    }
    emit("hide");
  }
};

const onPeriodoInput = (e) => {
  let val = e.target.value.toUpperCase();

  // Eliminar todo lo que no sea número o I
  val = val.replace(/[^0-9I]/g, "");

  // Mantener solo los primeros 4 dígitos numéricos
  const year = val.slice(0, 4).replace(/[^0-9]/g, "");

  let rest = val.slice(4).replace(/[^I]/g, "").slice(0, 2);

  let result = year;

  // Si hay 4 números exactos, agregar el guion
  if (year.length === 4) {
    result += "-";
  }

  // Si hay letras I o II, las agregamos
  if (rest.length > 0) {
    result += rest;
  }

  formData.value.nombre_periodo = result;
};
</script>

<template>
  <AuthorizationFallback :permissions="requiredPermissions">
    <div class="mt-2 space-y-1.5 font-inter">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <FormInput
          v-model="formData.anio"
          :focus="show"
          label="Año"
          :error="formErrors?.anio"
          required
          placeholder="2024"
          @input="onPeriodoInput"
        />

        <FormInput
          v-model="formData.nombre_periodo"
          :focus="show"
          label="Periodo"
          :error="formErrors?.nombre_periodo"
          required
          placeholder="2024-I"
          @input="onPeriodoInput"
        />

        <CheckBox
          v-model="formData.status"
          label="Estado"
          class="mt-8 pl-4 flex justify-center items-centers"
        />
      </div>
      <FormInput
        v-model="formData.descripcion"
        :focus="show"
        label="Descripcion"
        :error="formErrors?.descripcion"
      />

      <div class="w-full space-y-3">
        <div class="flex gap-2 mt-1">
          <!-- Botón Guardar: ancho completo -->
          <Button
            :title="periodo?.id ? 'Guardar Cambios' : 'Crear Periodo'"
            :loading-title="periodo?.id ? 'Guardando...' : 'Creando...'"
            :loading="saving || updating"
            key="submit-btn"
            @click="onSubmit"
            class="!w-full"
          />

          <!-- Botón Cancelar: ancho flexible solo si se está editando -->
          <Button
            v-if="isEditing"
            title="Cancelar"
            variant="outline"
            @click="onCancelEdit"
            class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer px-4"
          />
        </div>
      </div>
    </div>
  </AuthorizationFallback>
</template>
