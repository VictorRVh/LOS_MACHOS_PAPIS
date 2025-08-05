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

    formData.value = initialFormData();
    formErrors.value = {};

    emit("hide");
  }
};

let lastPeriodoValue = ""; // define esto fuera de la función, en tu <script>

const onPeriodoInput = (e) => {
  let raw = e.target.value.toUpperCase();
  let val = raw.replace(/[^0-9I]/g, "");

  const year = val.slice(0, 4).replace(/[^0-9]/g, "");
  let rest = val.slice(4).replace(/[^I]/g, "").slice(0, 2);

  let result = year;

  const guionEliminado = lastPeriodoValue.endsWith("-") && !raw.endsWith("-");

  if (!guionEliminado && year.length === 4) {
    result += "-";
  }

  if (rest.length > 0) {
    result += rest;
  }

  formData.value.nombre_periodo = result;
  lastPeriodoValue = result;
};

</script>

<template>
  <AuthorizationFallback :permissions="requiredPermissions">
    <div class="mt-4 px-4 space-y-2 font-inter max-w-lg mx-auto">
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
        class="flex items-center"
      />

      <div class="w-full space-y-2 pt-2">
        <div class="flex flex-col md:flex-row gap-2">
          <Button
            :title="periodo?.id ? 'Guardar Cambios' : 'Crear Periodo'"
            :loading-title="periodo?.id ? 'Guardando...' : 'Creando...'"
            :loading="saving || updating"
            key="submit-btn"
            @click="onSubmit"
            class="w-full"
          />

          <Button
            v-if="isEditing"
            title="Cancelar"
            variant="outline"
            @click="onCancelEdit"
            class="bg-red-500 text-white px-4 md:w-auto"
          />
        </div>
      </div>
    </div>
  </AuthorizationFallback>
</template>

